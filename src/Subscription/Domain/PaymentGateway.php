<?php

declare(strict_types=1);

namespace ClubinhoPet\Subscription\Domain;

use ClubinhoPet\Shared\Domain\PlanSlug;

interface PaymentGateway
{
    public function checkoutUrlFor(PlanSlug $slug): string;
}
