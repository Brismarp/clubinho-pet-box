<?php

declare(strict_types=1);

namespace ClubinhoPet\Integrations\Domain;

interface Notifier
{
    /**
     * @param array<string, mixed> $payload
     */
    public function send(string $channel, string $to, string $templateKey, array $payload = []): void;
}
