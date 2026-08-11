<?php

declare(strict_types=1);

namespace ClubinhoPet\Catalog\Domain;

use ClubinhoPet\Shared\Domain\PlanSlug;

interface PlanRepository
{
    /** @return list<Plan> */
    public function findActiveOrdered(): array;

    public function findBySlug(PlanSlug $slug): ?Plan;

    public function findById(int $id): ?Plan;

    public function save(Plan $plan): void;
}
