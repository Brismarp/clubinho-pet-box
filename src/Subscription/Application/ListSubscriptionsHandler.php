<?php

declare(strict_types=1);

namespace ClubinhoPet\Subscription\Application;

use ClubinhoPet\Subscription\Domain\SubscriptionRepository;

final class ListSubscriptionsHandler
{
    public function __construct(private SubscriptionRepository $subscriptions)
    {
    }

    /** @return list<array<string, mixed>> */
    public function handle(int $limit = 50, ?string $status = null): array
    {
        return $this->subscriptions->findRecentDetailed($status, $limit);
    }
}
