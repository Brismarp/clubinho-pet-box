<?php

declare(strict_types=1);

namespace ClubinhoPet\Content\Domain;

interface FaqItemRepository
{
    /** @return list<FaqItem> */
    public function findPublished(): array;

    /** @return list<FaqItem> */
    public function findAll(): array;

    public function findById(int $id): ?FaqItem;

    public function save(FaqItem $item): void;
}
