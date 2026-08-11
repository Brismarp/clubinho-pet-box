<?php

declare(strict_types=1);

namespace ClubinhoPet\Analytics\Application;

use ClubinhoPet\Analytics\Domain\AnalyticsEventRepository;
use ClubinhoPet\Analytics\Domain\LeadRepository;
use PDO;

final class GetDashboardMetricsHandler
{
    public function __construct(
        private AnalyticsEventRepository $events,
        private LeadRepository $leads,
        private PDO $pdo,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function handle(int $rangeDays = 30): array
    {
        $rangeDays = in_array($rangeDays, [7, 30, 90], true) ? $rangeDays : 30;
        $since = new \DateTimeImmutable('-' . $rangeDays . ' days');
        $sinceSql = $since->format('Y-m-d H:i:s');

        $funnel = $this->events->funnelSince($since);

        $subsPeriod = (int) $this->scalar(
            'SELECT COUNT(*) FROM subscriptions WHERE created_at >= :since',
            ['since' => $sinceSql]
        );
        $subsTotal = (int) $this->scalar('SELECT COUNT(*) FROM subscriptions');
        $subsByStatus = $this->pairs(
            'SELECT status, COUNT(*) AS c FROM subscriptions GROUP BY status'
        );
        $subsByPlan = $this->pairs(
            'SELECT COALESCE(p.slug, CONCAT(\'#\', s.plan_id)) AS k, COUNT(*) AS c
             FROM subscriptions s
             LEFT JOIN plans p ON p.id = s.plan_id
             WHERE s.created_at >= :since
             GROUP BY k
             ORDER BY c DESC',
            ['since' => $sinceSql]
        );

        $contactsNew = (int) $this->scalar(
            "SELECT COUNT(*) FROM contact_messages WHERE status = 'new'"
        );
        $contactsPeriod = (int) $this->scalar(
            'SELECT COUNT(*) FROM contact_messages WHERE created_at >= :since',
            ['since' => $sinceSql]
        );

        $boxesByStatus = $this->pairs(
            'SELECT status, COUNT(*) AS c FROM boxes GROUP BY status'
        );

        return [
            'range_days' => $rangeDays,
            'since' => $sinceSql,
            'funnel' => $funnel,
            'top_paths' => $this->events->topPathsSince($since),
            'checkout_by_plan' => $this->events->checkoutByPlanSince($since),
            'quiz_complete' => $this->events->countEventSince('quiz_complete', $since),
            'operational' => [
                'subscriptions_period' => $subsPeriod,
                'subscriptions_total' => $subsTotal,
                'subscriptions_by_status' => $subsByStatus,
                'subscriptions_by_plan' => $subsByPlan,
                'contacts_new' => $contactsNew,
                'contacts_period' => $contactsPeriod,
                'boxes_by_status' => $boxesByStatus,
                'boxes_preparing' => (int) ($boxesByStatus['preparing'] ?? 0),
                'boxes_shipped' => (int) ($boxesByStatus['shipped'] ?? 0),
                'leads_period' => $this->leads->countSince($since),
                'plans_active' => (int) $this->scalar('SELECT COUNT(*) FROM plans WHERE is_active = 1'),
            ],
            'revenue_note' => 'Receita / pagos confirmados: aguardando webhook Kirvano',
        ];
    }

    /** @param array<string, mixed> $params */
    private function scalar(string $sql, array $params = []): mixed
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchColumn();
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, int>
     */
    private function pairs(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $out = [];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $key = (string) ($row['k'] ?? $row['status'] ?? array_values($row)[0] ?? '');
            $out[$key] = (int) ($row['c'] ?? 0);
        }

        return $out;
    }
}
