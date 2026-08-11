<?php

declare(strict_types=1);

namespace ClubinhoPet\Platform\Application;

use ClubinhoPet\Platform\Domain\SettingRepository;

final class GetPublicSettingsHandler
{
    public function __construct(private SettingRepository $settings)
    {
    }

    /** @return array<string, mixed> */
    public function handle(): array
    {
        $out = [];
        foreach ($this->settings->findPublic() as $setting) {
            $out[$setting->key()] = $setting->value();
        }

        return $out;
    }
}
