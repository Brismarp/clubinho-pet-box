<?php

declare(strict_types=1);

namespace ClubinhoPet\Platform\Domain;

interface SettingRepository
{
    /** @return list<Setting> */
    public function findAll(): array;

    /** @return list<Setting> */
    public function findPublic(): array;

    public function findByKey(string $key): ?Setting;

    public function save(Setting $setting, ?int $changedBy = null, ?string $ip = null, ?string $ua = null): void;
}
