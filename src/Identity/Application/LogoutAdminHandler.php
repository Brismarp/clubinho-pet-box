<?php

declare(strict_types=1);

namespace ClubinhoPet\Identity\Application;

use ClubinhoPet\Identity\Domain\AuthSession;

final class LogoutAdminHandler
{
    public function __construct(private AuthSession $auth)
    {
    }

    public function handle(): void
    {
        $this->auth->logout();
    }
}
