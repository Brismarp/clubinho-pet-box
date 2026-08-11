<?php

declare(strict_types=1);

namespace ClubinhoPet\Subscription\Application\StartCheckout;

use ClubinhoPet\Catalog\Domain\PlanRepository;
use ClubinhoPet\Shared\Domain\DomainException;
use ClubinhoPet\Shared\Domain\PlanSlug;
use ClubinhoPet\Subscription\Domain\PaymentGateway;

final class StartCheckoutHandler
{
    public function __construct(
        private PlanRepository $plans,
        private PaymentGateway $gateway,
    ) {
    }

    public function handle(StartCheckoutCommand $command): StartCheckoutResult
    {
        $slug = PlanSlug::fromString($command->planSlug);
        $plan = $this->plans->findBySlug($slug);
        if ($plan === null || !$plan->isActive()) {
            throw new DomainException('Plan not found.');
        }

        $url = $this->gateway->checkoutUrlFor($slug);

        return new StartCheckoutResult($url, $slug->value(), $plan->name());
    }
}
