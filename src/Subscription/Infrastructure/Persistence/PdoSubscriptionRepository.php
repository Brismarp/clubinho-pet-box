<?php

declare(strict_types=1);

namespace ClubinhoPet\Subscription\Infrastructure\Persistence;

use ClubinhoPet\Subscription\Domain\Subscription;
use ClubinhoPet\Subscription\Domain\SubscriptionRepository;
use PDO;

final class PdoSubscriptionRepository implements SubscriptionRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function save(Subscription $subscription): void
    {
        $pet = $subscription->pet();
        $stmt = $this->pdo->prepare(
            'INSERT INTO subscriptions (plan_id, email, pet_name, pet_type, pet_age, status, notes, start_date)
             VALUES (:plan_id, :email, :pet_name, :pet_type, :pet_age, :status, :notes, :start_date)'
        );
        $status = $subscription->status()->value;
        $stmt->execute([
            'plan_id' => $subscription->planId(),
            'email' => $subscription->email()->value(),
            'pet_name' => $pet?->name(),
            'pet_type' => $pet?->type(),
            'pet_age' => $pet?->age(),
            'status' => $status,
            'notes' => $subscription->notes(),
            'start_date' => $status === 'active' ? date('Y-m-d') : null,
        ]);

        $id = (int) $this->pdo->lastInsertId();
        $subscription->assignId($id);

        if ($pet !== null) {
            $petStmt = $this->pdo->prepare(
                'INSERT INTO pets (subscription_id, name, type, age, breed, dietary_restrictions)
                 VALUES (:subscription_id, :name, :type, :age, :breed, :dietary)'
            );
            $petStmt->execute([
                'subscription_id' => $id,
                'name' => $pet->name(),
                'type' => $pet->type(),
                'age' => $pet->age(),
                'breed' => $pet->breed(),
                'dietary' => $pet->dietaryRestrictions(),
            ]);
        }
    }

    public function findRecent(int $limit = 50): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM subscriptions ORDER BY created_at DESC LIMIT :limit'
        );
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll() ?: [];

        return array_map(static fn (array $row) => Subscription::fromRow($row), $rows);
    }

    public function findRecentDetailed(?string $status = null, int $limit = 100): array
    {
        $sql = 'SELECT s.*, p.name AS plan_name, p.slug AS plan_slug
                FROM subscriptions s
                LEFT JOIN plans p ON p.id = s.plan_id';
        $params = [];
        if ($status !== null && $status !== '') {
            $sql .= ' WHERE s.status = :status';
            $params['status'] = $status;
        }
        $sql .= ' ORDER BY s.created_at DESC LIMIT :limit';
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $out = [];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $out[] = [
                'id' => (int) $row['id'],
                'plan_id' => (int) $row['plan_id'],
                'plan_name' => (string) ($row['plan_name'] ?? ''),
                'plan_slug' => (string) ($row['plan_slug'] ?? ''),
                'email' => (string) $row['email'],
                'status' => (string) $row['status'],
                'pet_name' => $row['pet_name'] ?? null,
                'pet_type' => $row['pet_type'] ?? null,
                'notes' => $row['notes'] ?? null,
                'created_at' => (string) ($row['created_at'] ?? ''),
            ];
        }

        return $out;
    }
}
