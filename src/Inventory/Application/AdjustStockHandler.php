<?php

declare(strict_types=1);

namespace ClubinhoPet\Inventory\Application;

use ClubinhoPet\Shared\Domain\DomainException;

final class AdjustStockHandler
{
    public function __construct(private StockLedger $ledger)
    {
    }

    public function handle(int $productId, int $delta, string $reason, ?int $userId, ?string $note = null): int
    {
        if (!in_array($reason, ['receive', 'adjust'], true)) {
            throw new DomainException('Only receive/adjust allowed from stock UI.');
        }
        if ($delta === 0) {
            throw new DomainException('Delta cannot be zero.');
        }

        return $this->ledger->move($productId, $delta, $reason, $userId, 'manual', null, $note);
    }
}
