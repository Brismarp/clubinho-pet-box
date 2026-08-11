<?php

declare(strict_types=1);

namespace ClubinhoPet\Shared\Domain;

final readonly class Email
{
    private function __construct(private string $value)
    {
    }

    public static function fromString(string $email): self
    {
        $email = trim(strtolower($email));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new DomainException('Invalid email address.');
        }

        return new self($email);
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
