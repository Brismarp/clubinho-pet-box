<?php

declare(strict_types=1);

namespace ClubinhoPet\Identity\Domain;

interface AuthSession
{
    public function login(User $user): void;

    public function logout(): void;

    public function userId(): ?int;

    public function isLoggedIn(): bool;

    public function role(): ?string;

    public function userName(): ?string;
}
