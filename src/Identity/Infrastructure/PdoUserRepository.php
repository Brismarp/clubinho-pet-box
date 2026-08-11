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

    public function findById(int $id): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? User::fromRow($row) : null;
    }

    public function listAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM users ORDER BY id ASC');
        $out = [];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $out[] = User::fromRow($row);
        }

        return $out;
    }

    public function save(User $user): void
    {
        if ($user->id() === null) {
            $stmt = $this->pdo->prepare(
                'INSERT INTO users (name, email, password, role, is_active)
                 VALUES (:name, :email, :password, :role, :is_active)'
            );
            $stmt->execute([
                'name' => $user->name(),
                'email' => $user->email()->value(),
                'password' => $user->passwordHash(),
                'role' => $user->role()->value,
                'is_active' => $user->isActive() ? 1 : 0,
            ]);
            $user->assignId((int) $this->pdo->lastInsertId());

            return;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE users
             SET name = :name, email = :email, password = :password, role = :role, is_active = :is_active
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $user->id(),
            'name' => $user->name(),
            'email' => $user->email()->value(),
            'password' => $user->passwordHash(),
            'role' => $user->role()->value,
            'is_active' => $user->isActive() ? 1 : 0,
        ]);
    }

    public function countActiveAdmins(): int
    {
        $stmt = $this->pdo->query(
            "SELECT COUNT(*) FROM users WHERE role = 'admin' AND is_active = 1"
        );

        return (int) $stmt->fetchColumn();
    }

    public function touchLastLogin(int $userId): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = :id');
        $stmt->execute(['id' => $userId]);
    }
}
