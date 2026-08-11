<?php

declare(strict_types=1);

namespace ClubinhoPet\Catalog\Infrastructure\Persistence;

use ClubinhoPet\Catalog\Domain\Plan;
use ClubinhoPet\Catalog\Domain\PlanRepository;
use ClubinhoPet\Shared\Domain\PlanSlug;
use PDO;

final class PdoPlanRepository implements PlanRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findActiveOrdered(): array
    {
        $stmt = $this->pdo->query(
            'SELECT * FROM plans WHERE is_active = 1 ORDER BY display_order ASC, id ASC'
        );
        $rows = $stmt->fetchAll() ?: [];

        return array_map(static fn (array $row) => Plan::fromRow($row), $rows);
    }

    public function findBySlug(PlanSlug $slug): ?Plan
    {
        $stmt = $this->pdo->prepare('SELECT * FROM plans WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug->value()]);
        $row = $stmt->fetch();

        return $row ? Plan::fromRow($row) : null;
    }

    public function findById(int $id): ?Plan
    {
        $stmt = $this->pdo->prepare('SELECT * FROM plans WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? Plan::fromRow($row) : null;
    }

    public function save(Plan $plan): void
    {
        if ($plan->id() === null) {
            throw new \RuntimeException('Creating plans via repository is not supported yet.');
        }

        $stmt = $this->pdo->prepare(
            'UPDATE plans SET checkout_url = :url, updated_at = CURRENT_TIMESTAMP WHERE id = :id'
        );
        $stmt->execute([
            'url' => $plan->checkoutUrl(),
            'id' => $plan->id(),
        ]);
    }
}
