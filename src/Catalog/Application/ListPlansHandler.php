<?php

declare(strict_types=1);

namespace ClubinhoPet\Catalog\Application;

use ClubinhoPet\Catalog\Domain\PlanRepository;

final class ListPlansHandler
{
    public function __construct(private PlanRepository $plans)
    {
    }

    /** @return list<array<string, mixed>> */
    public function handle(bool $activeOnly = true): array
    {
        $items = $activeOnly ? $this->plans->findActiveOrdered() : $this->plans->findActiveOrdered();

        return array_map(static fn ($plan) => $plan->toArray(), $items);
    }
}
