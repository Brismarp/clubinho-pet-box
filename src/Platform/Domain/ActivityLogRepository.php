<?php

declare(strict_types=1);

namespace ClubinhoPet\Platform\Domain;

interface ActivityLogRepository
{
    public function log(
        ?int $userId,
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $ip = null,
        ?string $ua = null,
    ): void;
}
