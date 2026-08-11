<?php

declare(strict_types=1);

namespace ClubinhoPet\Shared\Domain;

final readonly class PlanSlug
{
    private const ALLOWED = ['happy', 'love', 'deluxe'];

    private function __construct(private string $value)
    {
    }

    public static function fromString(string $slug): self
    {
        $slug = strtolower(trim($slug));
        if ($slug === '' || !preg_match('/^[a-z0-9\-]+$/', $slug)) {
            throw new DomainException('Invalid plan slug.');
        }

        return new self($slug);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
