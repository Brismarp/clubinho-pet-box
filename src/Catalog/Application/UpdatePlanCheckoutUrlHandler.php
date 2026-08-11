<?php

declare(strict_types=1);

namespace ClubinhoPet\Catalog\Application;

use ClubinhoPet\Catalog\Domain\PlanRepository;
use ClubinhoPet\Shared\Domain\DomainException;

final class UpdatePlanCheckoutUrlHandler
{
    public function __construct(private PlanRepository $plans)
    {
    }

    public function handle(int $planId, string $url): void
    {
        $plan = $this->plans->findById($planId);
        if ($plan === null) {
            throw new DomainException('Plan not found.');
        }
        $plan->changeCheckoutUrl($url);
        $this->plans->save($plan);
    }
}
