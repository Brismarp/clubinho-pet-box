<?php

declare(strict_types=1);

namespace ClubinhoPet\Identity\Infrastructure;

use ClubinhoPet\Identity\Domain\User;
use ClubinhoPet\Identity\Domain\UserRepository;
use ClubinhoPet\Shared\Domain\Email;
use PDO;

final class PdoUserRepository implements UserRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findByEmail(Email $email): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email->value()]);
        $row = $stmt->fetch();

        return $row ? User::fromRow($row) : null;
    }

    public function touchLastLogin(int $userId): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = :id');
        $stmt->execute(['id' => $userId]);
    }
}
