<?php

declare(strict_types=1);

namespace ClubinhoPet\Subscription\Infrastructure\Payment;

use ClubinhoPet\Catalog\Domain\PlanRepository;
use ClubinhoPet\Shared\Domain\DomainException;
use ClubinhoPet\Shared\Domain\PlanSlug;
use ClubinhoPet\Subscription\Domain\PaymentGateway;

/**
 * Anti-corruption: checkout URL lives on Plan (single source of truth).
 * Adapter name kept historical (Kirvano / external gateway).
 */
final class PlanCheckoutGateway implements PaymentGateway
{
    public function __construct(private PlanRepository $plans)
    {
    }

    public function checkoutUrlFor(PlanSlug $slug): string
    {
        $plan = $this->plans->findBySlug($slug);
        if ($plan === null || !$plan->isActive()) {
            throw new DomainException('Plan not available for checkout.');
        }

        $url = $plan->checkoutUrl();
        if ($url === null || $url === '') {
            throw new DomainException('Checkout URL is not configured for this plan.');
        }

        return $url;
    }
}
