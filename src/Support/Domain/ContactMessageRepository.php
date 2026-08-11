<?php

declare(strict_types=1);

namespace ClubinhoPet\Support\Domain;

interface ContactMessageRepository
{
    public function save(ContactMessage $message): void;

    /** @return list<ContactMessage> */
    public function findAll(int $limit = 100, ?string $status = null): array;

    public function findById(int $id): ?ContactMessage;

    public function countByStatus(string $status): int;
}
