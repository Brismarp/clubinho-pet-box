<?php

declare(strict_types=1);

namespace ClubinhoPet\Inventory\Application;

use ClubinhoPet\Shared\Domain\DomainException;
use PDO;

final class StockLedger
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Apply a signed stock movement and return the new balance.
     *
     * @throws DomainException
     */
    public function move(
        int $productId,
        int $delta,
        string $reason,
        ?int $userId = null,
        ?string $refType = null,
        ?int $refId = null,
        ?string $note = null,
    ): int {
        $allowed = ['receive', 'adjust', 'reserve', 'ship', 'release', 'sale'];
        if (!in_array($reason, $allowed, true)) {
            throw new DomainException('Invalid stock reason.');
        }

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare('SELECT stock_qty FROM products WHERE id = ? FOR UPDATE');
            $stmt->execute([$productId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row === false) {
                throw new DomainException('Product not found.');
            }
            $current = (int) $row['stock_qty'];
            $next = $current + $delta;
            if ($next < 0) {
                throw new DomainException('Insufficient stock for product #' . $productId . '.');
            }
            // ship with delta 0 = mark conversion from prior reserve (no second decrement)
            if ($delta !== 0 || $reason === 'ship') {
                $this->pdo->prepare('UPDATE products SET stock_qty = ? WHERE id = ?')->execute([$next, $productId]);
            }
            $this->pdo->prepare(
                'INSERT INTO stock_movements (product_id, delta, balance_after, reason, ref_type, ref_id, user_id, note)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            )->execute([$productId, $delta, $next, $reason, $refType, $refId, $userId, $note]);
            $this->pdo->commit();

            return $next;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e instanceof DomainException ? $e : new DomainException($e->getMessage());
        }
    }

    /** @return list<array<string, mixed>> */
    public function recent(int $limit = 80): array
    {
        $limit = max(1, min(200, $limit));
        $stmt = $this->pdo->query(
            "SELECT m.*, p.name AS product_name, p.sku
             FROM stock_movements m
             INNER JOIN products p ON p.id = m.product_id
             ORDER BY m.id DESC
             LIMIT {$limit}"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** @return list<array<string, mixed>> */
    public function productStockList(): array
    {
        $stmt = $this->pdo->query(
            'SELECT id, name, sku, stock_qty, stock_min, is_available,
                    (stock_qty <= stock_min) AS is_low
             FROM products
             ORDER BY (stock_qty <= stock_min) DESC, name ASC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function lowStockCount(): int
    {
        return (int) $this->pdo->query(
            'SELECT COUNT(*) FROM products WHERE stock_qty <= stock_min'
        )->fetchColumn();
    }
}
