<?php

declare(strict_types=1);

namespace ClubinhoPet\Catalog\Application;

use ClubinhoPet\Catalog\Domain\PlanRepository;
use ClubinhoPet\Shared\Domain\DomainException;
use ClubinhoPet\Shared\Domain\PlanSlug;

final class GetPlanBySlugHandler
{
    public function __construct(private PlanRepository $plans)
    {
    }

    /** @return array<string, mixed> */
    public function handle(string $slug): array
    {
        $plan = $this->plans->findBySlug(PlanSlug::fromString($slug));
        if ($plan === null || !$plan->isActive()) {
            throw new DomainException('Plan not found.');
        }

        return $plan->toArray();
    }
}
