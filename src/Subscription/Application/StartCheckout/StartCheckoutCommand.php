<?php

declare(strict_types=1);

namespace ClubinhoPet\Subscription\Application\StartCheckout;

final readonly class StartCheckoutCommand
{
    public function __construct(public string $planSlug)
    {
    }
}
