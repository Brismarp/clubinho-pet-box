<?php

declare(strict_types=1);

namespace ClubinhoPet\Identity\Domain;

use ClubinhoPet\Shared\Domain\Email;

interface UserRepository
{
    public function findByEmail(Email $email): ?User;

    public function touchLastLogin(int $userId): void;
}
