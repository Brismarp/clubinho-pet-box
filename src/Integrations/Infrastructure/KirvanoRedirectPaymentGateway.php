<?php

declare(strict_types=1);

namespace ClubinhoPet\Integrations\Infrastructure;

use ClubinhoPet\Shared\Domain\PlanSlug;
use ClubinhoPet\Subscription\Domain\PaymentGateway;
use ClubinhoPet\Subscription\Infrastructure\Payment\PlanCheckoutGateway;

/**
 * Keeps real Kirvano redirect checkout while exposing a mock-friendly facade.
 */
final class KirvanoRedirectPaymentGateway implements PaymentGateway
{
    public function __construct(private PlanCheckoutGateway $inner)
    {
    }

    public function checkoutUrlFor(PlanSlug $slug): string
    {
        return $this->inner->checkoutUrlFor($slug);
    }
}
