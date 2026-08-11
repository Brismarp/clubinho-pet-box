<?php

declare(strict_types=1);

namespace ClubinhoPet\Identity\Domain;

use ClubinhoPet\Shared\Domain\Email;

interface UserRepository
{
    public function findByEmail(Email $email): ?User;

    public function findById(int $id): ?User;

    /** @return list<User> */
    public function listAll(): array;

    public function save(User $user): void;

    public function countActiveAdmins(): int;

    public function touchLastLogin(int $userId): void;
}
