<?php

declare(strict_types=1);

namespace ClubinhoPet\Subscription\Domain;

use ClubinhoPet\Shared\Domain\DomainException;
use ClubinhoPet\Shared\Domain\Email;
use ClubinhoPet\Shared\Domain\PlanSlug;

final class Pet
{
    public function __construct(
        private string $name,
        private ?string $type = null,
        private ?int $age = null,
        private ?string $breed = null,
        private ?string $dietaryRestrictions = null,
    ) {
        if (trim($name) === '') {
            throw new DomainException('Pet name is required.');
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): ?string
    {
        return $this->type;
    }

    public function age(): ?int
    {
        return $this->age;
    }

    public function breed(): ?string
    {
        return $this->breed;
    }

    public function dietaryRestrictions(): ?string
    {
        return $this->dietaryRestrictions;
    }
}
