<?php

declare(strict_types=1);

namespace ClubinhoPet\Subscription\Domain;

enum SubscriptionStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
    case Cancelled = 'cancelled';
    case Expired = 'expired';
}
