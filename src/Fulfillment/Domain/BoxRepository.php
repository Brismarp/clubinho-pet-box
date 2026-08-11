<?php

declare(strict_types=1);

namespace ClubinhoPet\Fulfillment\Domain;

interface BoxRepository
{
    /** @return list<Box> */
    public function findAll(int $limit = 100): array;

    public function save(Box $box): void;

    public function findById(int $id): ?Box;

    /** @return array<string, int> */
    public function countByStatus(): array;
}
