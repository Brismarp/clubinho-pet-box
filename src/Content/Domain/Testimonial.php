<?php

declare(strict_types=1);

namespace ClubinhoPet\Content\Domain;

use ClubinhoPet\Shared\Domain\DomainException;

final class Testimonial
{
    public function __construct(
        private ?int $id,
        private string $name,
        private string $quote,
        private string $status = 'pending',
        private ?string $petName = null,
        private ?string $photoPath = null,
        private int $displayOrder = 0,
    ) {
        if (trim($name) === '' || trim($quote) === '') {
            throw new DomainException('Name and quote are required.');
        }
        if (!in_array($status, ['pending', 'published', 'hidden'], true)) {
            throw new DomainException('Invalid testimonial status.');
        }
    }

    /** @param array<string, mixed> $row */
    public static function fromRow(array $row): self
    {
        return new self(
            (int) $row['id'],
            (string) $row['name'],
            (string) $row['quote'],
            (string) ($row['status'] ?? 'pending'),
            isset($row['pet_name']) && $row['pet_name'] !== '' ? (string) $row['pet_name'] : null,
            isset($row['photo_path']) && $row['photo_path'] !== '' ? (string) $row['photo_path'] : null,
            (int) ($row['display_order'] ?? 0),
        );
    }

    public function id(): ?int { return $this->id; }
    public function assignId(int $id): void { $this->id = $id; }
    public function name(): string { return $this->name; }
    public function quote(): string { return $this->quote; }
    public function status(): string { return $this->status; }
    public function petName(): ?string { return $this->petName; }
    public function photoPath(): ?string { return $this->photoPath; }
    public function displayOrder(): int { return $this->displayOrder; }

    public function apply(
        string $name,
        string $quote,
        string $status,
        ?string $petName,
        ?string $photoPath,
        int $displayOrder,
    ): void {
        if (trim($name) === '' || trim($quote) === '') {
            throw new DomainException('Name and quote are required.');
        }
        if (!in_array($status, ['pending', 'published', 'hidden'], true)) {
            throw new DomainException('Invalid testimonial status.');
        }
        $this->name = $name;
        $this->quote = $quote;
        $this->status = $status;
        $this->petName = $petName;
        $this->photoPath = $photoPath;
        $this->displayOrder = $displayOrder;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'pet_name' => $this->petName,
            'photo_path' => $this->photoPath,
            'quote' => $this->quote,
            'status' => $this->status,
            'display_order' => $this->displayOrder,
        ];
    }
}
