<?php

declare(strict_types=1);

namespace ClubinhoPet\Content\Domain;

interface HappyMediaRepository
{
    /** @return list<HappyMedia> */
    public function findApproved(): array;

    /** @return list<HappyMedia> */
    public function findAll(): array;

    /** @return list<HappyMedia> */
    public function findByStatus(string $status): array;

    public function findById(int $id): ?HappyMedia;

    public function save(HappyMedia $item): void;
}
