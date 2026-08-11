<?php

declare(strict_types=1);

namespace ClubinhoPet\Subscription\Application\RegisterInterest;

use ClubinhoPet\Catalog\Domain\PlanRepository;
use ClubinhoPet\Shared\Domain\DomainException;
use ClubinhoPet\Shared\Domain\Email;
use ClubinhoPet\Shared\Domain\PlanSlug;
use ClubinhoPet\Subscription\Domain\PaymentGateway;
use ClubinhoPet\Subscription\Domain\Pet;
use ClubinhoPet\Subscription\Domain\Subscription;
use ClubinhoPet\Subscription\Domain\SubscriptionRepository;

final class RegisterInterestHandler
{
    public function __construct(
        private PlanRepository $plans,
        private SubscriptionRepository $subscriptions,
        private PaymentGateway $gateway,
    ) {
    }

    /** @return array{subscription_id:int, checkout_url:string, plan_slug:string} */
    public function handle(RegisterInterestCommand $command): array
    {
        $slug = PlanSlug::fromString($command->planSlug);
        $plan = $this->plans->findBySlug($slug);
        if ($plan === null || !$plan->isActive() || $plan->id() === null) {
            throw new DomainException('Plan not found.');
        }

        $notes = array_filter([
            $command->phone ? 'phone: ' . $command->phone : null,
            $command->address ? 'address: ' . $command->address : null,
        ]);

        $subscription = Subscription::registerInterest(
            $plan->id(),
            Email::fromString($command->email),
            trim($command->name),
            new Pet(
                $command->petName,
                $command->petType,
                $command->petAge,
                $command->petBreed,
                $command->dietaryRestrictions,
            ),
            $notes !== [] ? implode(' | ', $notes) : null,
        );

        $this->subscriptions->save($subscription);
        $url = $this->gateway->checkoutUrlFor($slug);

        return [
            'subscription_id' => (int) $subscription->id(),
            'checkout_url' => $url,
            'plan_slug' => $slug->value(),
        ];
    }
}
