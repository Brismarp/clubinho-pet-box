<?php

declare(strict_types=1);

namespace ClubinhoPet\Subscription\Domain;

use ClubinhoPet\Shared\Domain\DomainException;
use ClubinhoPet\Shared\Domain\Email;

final class Subscription
{
    public function __construct(
        private ?int $id,
        private int $planId,
        private Email $email,
        private ?string $customerName,
        private ?Pet $pet,
        private SubscriptionStatus $status,
        private ?string $externalId = null,
        private ?string $notes = null,
    ) {
    }

    public static function registerInterest(
        int $planId,
        Email $email,
        string $customerName,
        Pet $pet,
        ?string $notes = null,
    ): self {
        return new self(
            null,
            $planId,
            $email,
            $customerName,
            $pet,
            SubscriptionStatus::Pending,
            null,
            $notes,
        );
    }

    /** @param array<string, mixed> $row */
    public static function fromRow(array $row): self
    {
        $pet = null;
        if (!empty($row['pet_name'])) {
            $pet = new Pet(
                (string) $row['pet_name'],
                isset($row['pet_type']) ? (string) $row['pet_type'] : null,
                isset($row['pet_age']) ? (int) $row['pet_age'] : null,
            );
        }

        return new self(
            (int) $row['id'],
            (int) $row['plan_id'],
            Email::fromString((string) $row['email']),
            null,
            $pet,
            SubscriptionStatus::from((string) $row['status']),
            isset($row['external_id']) ? (string) $row['external_id'] : null,
            isset($row['notes']) ? (string) $row['notes'] : null,
        );
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    public function planId(): int
    {
        return $this->planId;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function customerName(): ?string
    {
        return $this->customerName;
    }

    public function pet(): ?Pet
    {
        return $this->pet;
    }

    public function status(): SubscriptionStatus
    {
        return $this->status;
    }

    public function notes(): ?string
    {
        return $this->notes;
    }

    public function pause(): void
    {
        if ($this->status !== SubscriptionStatus::Active) {
            throw new DomainException('Only active subscriptions can be paused.');
        }
        $this->status = SubscriptionStatus::Paused;
    }

    public function cancel(): void
    {
        if ($this->status === SubscriptionStatus::Cancelled) {
            throw new DomainException('Subscription already cancelled.');
        }
        $this->status = SubscriptionStatus::Cancelled;
    }
}
