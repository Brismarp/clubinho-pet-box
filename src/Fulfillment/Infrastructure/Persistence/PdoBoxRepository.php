<?php

declare(strict_types=1);

namespace ClubinhoPet\Fulfillment\Infrastructure\Persistence;

use ClubinhoPet\Fulfillment\Domain\Box;
use ClubinhoPet\Fulfillment\Domain\BoxRepository;
use PDO;

final class PdoBoxRepository implements BoxRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findAll(int $limit = 100): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM boxes ORDER BY created_at DESC LIMIT :limit');
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll() ?: [];

        return array_map(static fn (array $row) => Box::fromRow($row), $rows);
    }

    public function findById(int $id): ?Box
    {
        $stmt = $this->pdo->prepare('SELECT * FROM boxes WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? Box::fromRow($row) : null;
    }

    public function save(Box $box): void
    {
        if ($box->id() === null) {
            $stmt = $this->pdo->prepare(
                'INSERT INTO boxes (subscription_id, box_number, send_date, tracking_number, status, notes)
                 VALUES (:subscription_id, :box_number, :send_date, :tracking, :status, :notes)'
            );
            $stmt->execute([
                'subscription_id' => $box->subscriptionId(),
                'box_number' => $box->boxNumber(),
                'send_date' => $box->sendDate(),
                'tracking' => $box->trackingNumber(),
                'status' => $box->status(),
                'notes' => $box->notes(),
            ]);
            $box->assignId((int) $this->pdo->lastInsertId());

            return;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE boxes SET status = :status, tracking_number = :tracking, updated_at = CURRENT_TIMESTAMP WHERE id = :id'
        );
        $stmt->execute([
            'status' => $box->status(),
            'tracking' => $box->trackingNumber(),
            'id' => $box->id(),
        ]);
    }

    public function countByStatus(): array
    {
        $stmt = $this->pdo->query('SELECT status, COUNT(*) AS c FROM boxes GROUP BY status');
        $out = [];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $out[(string) $row['status']] = (int) $row['c'];
        }

        return $out;
    }
}
