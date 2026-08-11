<?php

declare(strict_types=1);

namespace ClubinhoPet\Subscription\Application\RegisterInterest;

final readonly class RegisterInterestCommand
{
    public function __construct(
        public string $planSlug,
        public string $name,
        public string $email,
        public string $petName,
        public ?string $petType = null,
        public ?int $petAge = null,
        public ?string $petBreed = null,
        public ?string $dietaryRestrictions = null,
        public ?string $phone = null,
        public ?string $address = null,
    ) {
    }
}
