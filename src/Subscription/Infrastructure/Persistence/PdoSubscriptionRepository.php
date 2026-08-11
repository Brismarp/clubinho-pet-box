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
             VALUES (:plan_id, :email, :pet_name, :pet_type, :pet_age, :status, :notes, CURDATE())'
        );
        $stmt->execute([
            'plan_id' => $subscription->planId(),
            'email' => $subscription->email()->value(),
            'pet_name' => $pet?->name(),
            'pet_type' => $pet?->type(),
            'pet_age' => $pet?->age(),
            'status' => $subscription->status()->value,
            'notes' => $subscription->notes(),
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
}
