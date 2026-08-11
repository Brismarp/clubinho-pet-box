<?php

declare(strict_types=1);

namespace ClubinhoPet\Integrations\Infrastructure;

use ClubinhoPet\Shared\Domain\PlanSlug;
use ClubinhoPet\Subscription\Domain\PaymentGateway;

/** Dev/mock checkout that never leaves the app. */
final class MockPaymentGateway implements PaymentGateway
{
    public function __construct(private string $appUrl)
    {
    }

    public function checkoutUrlFor(PlanSlug $slug): string
    {
        $base = rtrim($this->appUrl, '/');

        return $base . '/assinatura?plan=' . rawurlencode($slug->value()) . '&billing=monthly&mock_checkout=1';
    }
}
