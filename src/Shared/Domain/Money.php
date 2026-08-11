<?php

declare(strict_types=1);

namespace ClubinhoPet\Shared\Domain;

final readonly class Money
{
    private function __construct(
        private string $amount,
        private string $currency,
    ) {
    }

    public static function of(float|string $amount, string $currency = 'BRL'): self
    {
        $currency = strtoupper(trim($currency));
        if ($currency === '' || strlen($currency) !== 3) {
            throw new DomainException('Invalid currency code.');
        }

        $normalized = number_format((float) $amount, 2, '.', '');

        return new self($normalized, $currency);
    }

    public function amount(): string
    {
        return $this->amount;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function asFloat(): float
    {
        return (float) $this->amount;
    }
}
