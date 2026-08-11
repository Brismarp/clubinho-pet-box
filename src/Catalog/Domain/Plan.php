<?php

declare(strict_types=1);

namespace ClubinhoPet\Catalog\Domain;

use ClubinhoPet\Shared\Domain\DomainException;
use ClubinhoPet\Shared\Domain\Money;
use ClubinhoPet\Shared\Domain\PlanSlug;

final class Plan
{
    /**
     * @param list<string> $features
     */
    public function __construct(
        private ?int $id,
        private string $name,
        private PlanSlug $slug,
        private string $description,
        private Money $price,
        private string $billingCycle,
        private ?string $checkoutUrl,
        private array $features,
        private bool $active,
        private bool $popular,
        private int $displayOrder,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        $features = $row['features'] ?? [];
        if (is_string($features)) {
            $decoded = json_decode($features, true);
            $features = is_array($decoded) ? $decoded : [];
        }

        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            (string) $row['name'],
            PlanSlug::fromString((string) $row['slug']),
            (string) ($row['description'] ?? ''),
            Money::of($row['price'] ?? 0, (string) ($row['currency'] ?? 'BRL')),
            (string) ($row['billing_cycle'] ?? 'monthly'),
            isset($row['checkout_url']) && $row['checkout_url'] !== '' ? (string) $row['checkout_url'] : null,
            array_values(array_map('strval', $features)),
            (bool) ($row['is_active'] ?? true),
            (bool) ($row['is_popular'] ?? false),
            (int) ($row['display_order'] ?? 0),
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

    public function slug(): PlanSlug
    {
        return $this->slug;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function price(): Money
    {
        return $this->price;
    }

    public function billingCycle(): string
    {
        return $this->billingCycle;
    }

    public function checkoutUrl(): ?string
    {
        return $this->checkoutUrl;
    }

    /** @return list<string> */
    public function features(): array
    {
        return $this->features;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function isPopular(): bool
    {
        return $this->popular;
    }

    public function displayOrder(): int
    {
        return $this->displayOrder;
    }

    public function changeCheckoutUrl(string $url): void
    {
        $url = trim($url);
        if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new DomainException('Invalid checkout URL.');
        }
        $this->checkoutUrl = $url;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug->value(),
            'description' => $this->description,
            'price' => $this->price->asFloat(),
            'currency' => $this->price->currency(),
            'billing_cycle' => $this->billingCycle,
            'checkout_url' => $this->checkoutUrl,
            'features' => $this->features,
            'is_active' => $this->active,
            'is_popular' => $this->popular,
            'display_order' => $this->displayOrder,
        ];
    }
}
