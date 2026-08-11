<?php

declare(strict_types=1);

namespace ClubinhoPet\Subscription\Application\StartCheckout;

final readonly class StartCheckoutResult
{
    public function __construct(
        public string $checkoutUrl,
        public string $planSlug,
        public string $planName,
    ) {
    }
}
