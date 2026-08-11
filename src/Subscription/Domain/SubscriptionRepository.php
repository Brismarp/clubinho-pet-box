<?php

declare(strict_types=1);

namespace ClubinhoPet\Subscription\Domain;

interface SubscriptionRepository
{
    public function save(Subscription $subscription): void;

    public function findById(int $id): ?Subscription;

    public function updateStatus(Subscription $subscription): void;

    /** @return list<Subscription> */
    public function findRecent(int $limit = 50): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function findRecentDetailed(?string $status = null, int $limit = 100): array;
}
