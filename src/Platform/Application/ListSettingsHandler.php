<?php

declare(strict_types=1);

namespace ClubinhoPet\Platform\Application;

use ClubinhoPet\Platform\Domain\SettingRepository;

final class ListSettingsHandler
{
    public function __construct(private SettingRepository $settings)
    {
    }

    /** @return list<array<string, mixed>> */
    public function handle(): array
    {
        return array_map(static fn ($s) => $s->toArray(), $this->settings->findAll());
    }
}
