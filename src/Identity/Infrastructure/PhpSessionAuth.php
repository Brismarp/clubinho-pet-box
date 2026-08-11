<?php

declare(strict_types=1);

namespace ClubinhoPet\Identity\Infrastructure;

use ClubinhoPet\Identity\Domain\AuthSession;
use ClubinhoPet\Identity\Domain\User;

final class PhpSessionAuth implements AuthSession
{
    public function login(User $user): void
    {
        $_SESSION['user_id'] = $user->id();
        $_SESSION['user_name'] = $user->name();
        $_SESSION['user_email'] = $user->email()->value();
        $_SESSION['user_role'] = $user->role()->value;
        $_SESSION['admin_logged_in'] = true;
    }

    public function logout(): void
    {
        unset(
            $_SESSION['user_id'],
            $_SESSION['user_name'],
            $_SESSION['user_email'],
            $_SESSION['user_role'],
            $_SESSION['admin_logged_in'],
        );
    }

    public function userId(): ?int
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public function isLoggedIn(): bool
    {
        return !empty($_SESSION['admin_logged_in']) && $this->userId() !== null;
    }

    public function role(): ?string
    {
        return isset($_SESSION['user_role']) ? (string) $_SESSION['user_role'] : null;
    }

    public function userName(): ?string
    {
        return isset($_SESSION['user_name']) ? (string) $_SESSION['user_name'] : null;
    }
}
