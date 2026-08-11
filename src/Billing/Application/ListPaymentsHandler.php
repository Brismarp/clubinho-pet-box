<?php

declare(strict_types=1);

namespace ClubinhoPet\Billing\Application;

use PDO;

final class ListPaymentsHandler
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function handle(?string $status = null, int $limit = 100): array
    {
        $limit = max(1, min(300, $limit));
        if ($status !== null && $status !== '') {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM payments WHERE status = ? ORDER BY id DESC LIMIT {$limit}"
            );
            $stmt->execute([$status]);
        } else {
            $stmt = $this->pdo->query("SELECT * FROM payments ORDER BY id DESC LIMIT {$limit}");
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
