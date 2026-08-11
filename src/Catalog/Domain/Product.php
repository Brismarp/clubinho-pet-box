<?php

declare(strict_types=1);

namespace ClubinhoPet\Catalog\Domain;

use ClubinhoPet\Shared\Domain\Money;

final class Product
{
    public function __construct(
        private ?int $id,
        private string $name,
        private string $description,
        private string $category,
        private ?Money $price,
        private bool $available,
        private ?string $imageUrl,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromRow(array $row): self
    {
        $price = isset($row['price']) && $row['price'] !== null
            ? Money::of($row['price'])
            : null;

        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            (string) $row['name'],
            (string) ($row['description'] ?? ''),
            (string) ($row['category'] ?? ''),
            $price,
            (bool) ($row['is_available'] ?? true),
            isset($row['image_url']) ? (string) $row['image_url'] : null,
        );
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'price' => $this->price?->asFloat(),
            'is_available' => $this->available,
            'image_url' => $this->imageUrl,
        ];
    }
}
