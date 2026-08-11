<?php

declare(strict_types=1);

namespace ClubinhoPet\Identity\Domain;

use ClubinhoPet\Shared\Domain\Email;

final class User
{
    public function __construct(
        private ?int $id,
        private string $name,
        private Email $email,
        private string $passwordHash,
        private UserRole $role,
        private bool $active,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromRow(array $row): self
    {
        return new self(
            (int) $row['id'],
            (string) $row['name'],
            Email::fromString((string) $row['email']),
            (string) $row['password'],
            UserRole::from((string) $row['role']),
            (bool) $row['is_active'],
        );
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function role(): UserRole
    {
        return $this->role;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function verifyPassword(string $plain): bool
    {
        return password_verify($plain, $this->passwordHash);
    }

    public function canAccessAdmin(): bool
    {
        return $this->active && in_array($this->role, UserRole::adminAccessRoles(), true);
    }

    public function can(string $permission): bool
    {
        return $this->canAccessAdmin() && $this->role->can($permission);
    }
}
