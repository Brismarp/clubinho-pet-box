<?php

declare(strict_types=1);

namespace ClubinhoPet\Platform\Infrastructure\Persistence;

use ClubinhoPet\Platform\Domain\Setting;
use ClubinhoPet\Platform\Domain\SettingRepository;
use PDO;

final class PdoSettingRepository implements SettingRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM settings ORDER BY `key` ASC');
        $rows = $stmt->fetchAll() ?: [];

        return array_map(static fn (array $row) => Setting::fromRow($row), $rows);
    }

    public function findPublic(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM settings WHERE is_public = 1 ORDER BY `key` ASC');
        $rows = $stmt->fetchAll() ?: [];

        return array_map(static fn (array $row) => Setting::fromRow($row), $rows);
    }

    public function findByKey(string $key): ?Setting
    {
        $stmt = $this->pdo->prepare('SELECT * FROM settings WHERE `key` = :key LIMIT 1');
        $stmt->execute(['key' => $key]);
        $row = $stmt->fetch();

        return $row ? Setting::fromRow($row) : null;
    }

    public function save(Setting $setting, ?int $changedBy = null, ?string $ip = null, ?string $ua = null): void
    {
        if ($setting->id() === null) {
            throw new \RuntimeException('Cannot save setting without id.');
        }

        $old = $this->pdo->prepare('SELECT value FROM settings WHERE id = :id');
        $old->execute(['id' => $setting->id()]);
        $oldValue = $old->fetchColumn();

        $stmt = $this->pdo->prepare(
            'UPDATE settings SET value = :value, version = :version, updated_at = CURRENT_TIMESTAMP WHERE id = :id'
        );
        $stmt->execute([
            'value' => $setting->value(),
            'version' => $setting->toArray()['version'],
            'id' => $setting->id(),
        ]);

        $hist = $this->pdo->prepare(
            'INSERT INTO settings_history (setting_id, old_value, new_value, changed_by, ip_address, user_agent)
             VALUES (:setting_id, :old_value, :new_value, :changed_by, :ip, :ua)'
        );
        $hist->execute([
            'setting_id' => $setting->id(),
            'old_value' => $oldValue !== false ? (string) $oldValue : null,
            'new_value' => $setting->value(),
            'changed_by' => $changedBy,
            'ip' => $ip,
            'ua' => $ua,
        ]);
    }
}
