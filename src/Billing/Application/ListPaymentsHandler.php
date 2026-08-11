<?php

declare(strict_types=1);

namespace ClubinhoPet\Billing\Application;

use ClubinhoPet\Shared\Infrastructure\AdminPager;
use PDO;

final class ListPaymentsHandler
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @return array{
     *   items: list<array<string,mixed>>,
     *   summary: array<string,mixed>,
     *   pager: array<string,mixed>,
     *   filters: array{status:?string,q:string,range:int}
     * }
     */
    public function handle(
        ?string $status = null,
        string $q = '',
        int $rangeDays = 30,
        ?int $page = null,
        ?int $perPage = null,
    ): array {
        $rangeDays = in_array($rangeDays, [7, 30, 90], true) ? $rangeDays : 30;
        $pagerReq = AdminPager::fromRequest(25);
        $page = $page ?? $pagerReq['page'];
        $perPage = $perPage ?? $pagerReq['per_page'];
        $offset = ($page - 1) * $perPage;

        $where = ['COALESCE(p.paid_at, p.created_at) >= DATE_SUB(NOW(), INTERVAL :days DAY)'];
        $params = ['days' => $rangeDays];

        if ($status !== null && $status !== '' && $status !== 'all') {
            $where[] = 'p.status = :status';
            $params['status'] = $status;
        }
        if (trim($q) !== '') {
            $where[] = 'IFNULL(p.customer_email, \'\') LIKE :q';
            $params['q'] = '%' . trim($q) . '%';
        }
        $whereSql = implode(' AND ', $where);

        $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM payments p WHERE {$whereSql}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $sql = "SELECT p.* FROM payments p WHERE {$whereSql} ORDER BY p.id DESC LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $sumStmt = $this->pdo->prepare(
            "SELECT p.status, COUNT(*) AS c, COALESCE(SUM(p.amount_cents),0) AS cents
             FROM payments p
             WHERE COALESCE(p.paid_at, p.created_at) >= DATE_SUB(NOW(), INTERVAL :days DAY)
             GROUP BY p.status"
        );
        $sumStmt->execute(['days' => $rangeDays]);
        $byStatus = [];
        foreach ($sumStmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $byStatus[(string) $row['status']] = [
                'count' => (int) $row['c'],
                'cents' => (int) $row['cents'],
            ];
        }

        $delinquency = (int) $this->pdo->query(
            "SELECT COUNT(*) FROM subscriptions s
             WHERE s.status = 'active'
             AND NOT EXISTS (
                SELECT 1 FROM payments p
                WHERE p.subscription_id = s.id
                  AND p.status = 'paid'
                  AND COALESCE(p.paid_at, p.created_at) >= DATE_SUB(NOW(), INTERVAL 45 DAY)
             )"
        )->fetchColumn();

        $failed = (int) ($byStatus['failed']['count'] ?? 0);

        return [
            'items' => $items,
            'summary' => [
                'by_status' => $byStatus,
                'paid_cents' => (int) ($byStatus['paid']['cents'] ?? 0),
                'pending_count' => (int) ($byStatus['pending']['count'] ?? 0),
                'failed_count' => $failed,
                'refunded_count' => (int) ($byStatus['refunded']['count'] ?? 0),
                'delinquent_active_subs' => $delinquency,
                'range_days' => $rangeDays,
            ],
            'pager' => AdminPager::meta($total, $page, $perPage),
            'filters' => [
                'status' => $status,
                'q' => $q,
                'range' => $rangeDays,
            ],
        ];
    }
}
