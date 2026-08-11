<?php

declare(strict_types=1);

namespace ClubinhoPet\Platform\Application;

use ClubinhoPet\Platform\Domain\ActivityLogRepository;
use ClubinhoPet\Platform\Domain\SettingRepository;
use ClubinhoPet\Shared\Domain\DomainException;

final class UpdateSettingHandler
{
    public function __construct(
        private SettingRepository $settings,
        private ActivityLogRepository $logs,
    ) {
    }

    public function handle(
        string $key,
        string $value,
        ?int $userId = null,
        ?string $ip = null,
        ?string $ua = null,
    ): void {
        $setting = $this->settings->findByKey($key);
        if ($setting === null) {
            throw new DomainException('Setting not found.');
        }

        $old = $setting->value();
        $setting->changeValue($value);
        $this->settings->save($setting, $userId, $ip, $ua);
        $this->logs->log(
            $userId,
            'setting.update',
            'setting',
            $setting->id(),
            ['value' => $old],
            ['value' => $value],
            $ip,
            $ua,
        );
    }
}
