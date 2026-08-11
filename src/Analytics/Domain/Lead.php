<?php

declare(strict_types=1);

namespace ClubinhoPet\Analytics\Domain;

use ClubinhoPet\Shared\Domain\DomainException;
use ClubinhoPet\Shared\Domain\Email;

final class Lead
{
    public function __construct(
        private ?int $id,
        private string $name,
        private Email $email,
        private string $phone,
        private string $petType,
        private ?string $city = null,
        private ?string $visitorId = null,
        private bool $acceptNewsletter = false,
        private ?string $createdAt = null,
    ) {
        if (trim($name) === '' || trim($phone) === '' || trim($petType) === '') {
            throw new DomainException('Name, phone and pet type are required.');
        }
    }

    public static function capture(
        string $name,
        Email $email,
        string $phone,
        string $petType,
        ?string $city = null,
        ?string $visitorId = null,
        bool $acceptNewsletter = false,
    ): self {
        return new self(null, $name, $email, $phone, $petType, $city, $visitorId, $acceptNewsletter);
    }

    /** @param array<string, mixed> $row */
    public static function fromRow(array $row): self
    {
        return new self(
            (int) $row['id'],
            (string) $row['name'],
            Email::fromString((string) $row['email']),
            (string) $row['phone'],
            (string) $row['pet_type'],
            isset($row['city']) && $row['city'] !== '' ? (string) $row['city'] : null,
            isset($row['visitor_id']) && $row['visitor_id'] !== '' ? (string) $row['visitor_id'] : null,
            (bool) ($row['accept_newsletter'] ?? false),
            isset($row['created_at']) ? (string) $row['created_at'] : null,
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

    public function name(): string
    {
        return $this->name;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function phone(): string
    {
        return $this->phone;
    }

    public function petType(): string
    {
        return $this->petType;
    }

    public function city(): ?string
    {
        return $this->city;
    }

    public function visitorId(): ?string
    {
        return $this->visitorId;
    }

    public function acceptNewsletter(): bool
    {
        return $this->acceptNewsletter;
    }

    public function createdAt(): ?string
    {
        return $this->createdAt;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email->value(),
            'phone' => $this->phone,
            'pet_type' => $this->petType,
            'city' => $this->city,
            'visitor_id' => $this->visitorId,
            'accept_newsletter' => $this->acceptNewsletter,
            'created_at' => $this->createdAt,
        ];
    }
}
