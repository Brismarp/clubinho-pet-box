<?php

declare(strict_types=1);

namespace ClubinhoPet\Catalog\Infrastructure\Persistence;

use ClubinhoPet\Catalog\Domain\Product;
use ClubinhoPet\Catalog\Domain\ProductRepository;
use PDO;

final class PdoProductRepository implements ProductRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findAvailable(): array
    {
        $stmt = $this->pdo->query(
            'SELECT * FROM products WHERE is_available = 1 ORDER BY name ASC'
        );
        $rows = $stmt->fetchAll() ?: [];

        return array_map(static fn (array $row) => Product::fromRow($row), $rows);
    }
}
