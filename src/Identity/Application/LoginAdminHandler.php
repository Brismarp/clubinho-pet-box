<?php

declare(strict_types=1);

namespace ClubinhoPet\Identity\Application;

use ClubinhoPet\Identity\Domain\AuthSession;
use ClubinhoPet\Identity\Domain\UserRepository;
use ClubinhoPet\Shared\Domain\DomainException;
use ClubinhoPet\Shared\Domain\Email;

final class LoginAdminHandler
{
    public function __construct(
        private UserRepository $users,
        private AuthSession $auth,
    ) {
    }

    public function handle(string $emailOrUsername, string $password): void
    {
        // Accept email; legacy "username" field maps to admin email when no @ present
        $emailRaw = str_contains($emailOrUsername, '@')
            ? $emailOrUsername
            : 'admin@clubinhopetbox.com.br';

        try {
            $email = Email::fromString($emailRaw);
        } catch (DomainException) {
            throw new DomainException('Invalid credentials.');
        }

        $user = $this->users->findByEmail($email);
        if ($user === null || !$user->canAccessAdmin() || !$user->verifyPassword($password)) {
            throw new DomainException('Invalid credentials.');
        }

        // Legacy username login: if they typed username, still require matching the seeded password
        if (!str_contains($emailOrUsername, '@') && $emailOrUsername !== 'AsmoD' && strtolower($emailOrUsername) !== 'admin') {
            throw new DomainException('Invalid credentials.');
        }

        $this->auth->login($user);
        if ($user->id() !== null) {
            $this->users->touchLastLogin($user->id());
        }
    }
}
