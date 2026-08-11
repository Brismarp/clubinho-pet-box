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
    public function handle(int $limit = 50): array
    {
        $out = [];
        foreach ($this->subscriptions->findRecent($limit) as $sub) {
            $pet = $sub->pet();
            $out[] = [
                'id' => $sub->id(),
                'plan_id' => $sub->planId(),
                'email' => $sub->email()->value(),
                'status' => $sub->status()->value,
                'pet_name' => $pet?->name(),
                'pet_type' => $pet?->type(),
                'notes' => $sub->notes(),
            ];
        }

        return $out;
    }
}
