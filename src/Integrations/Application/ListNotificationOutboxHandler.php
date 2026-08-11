<?php

declare(strict_types=1);

namespace ClubinhoPet\Integrations\Application;

use PDO;

final class ListNotificationOutboxHandler
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return list<array<string, mixed>> */
    public function handle(int $limit = 100, ?string $channel = null): array
    {
        $limit = max(1, min(500, $limit));
        if ($channel !== null && $channel !== '' && $channel !== 'all') {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM notification_outbox WHERE channel = ? ORDER BY id DESC LIMIT ' . $limit
            );
            $stmt->execute([$channel]);
        } else {
            $stmt = $this->pdo->query(
                'SELECT * FROM notification_outbox ORDER BY id DESC LIMIT ' . $limit
            );
        }

        return $stmt ? ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
    }
}
