<?php

declare(strict_types=1);

namespace ClubinhoPet\Platform\Infrastructure\Persistence;

use ClubinhoPet\Platform\Domain\ActivityLogRepository;
use PDO;

final class PdoActivityLogRepository implements ActivityLogRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function log(
        ?int $userId,
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $ip = null,
        ?string $ua = null,
    ): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO activity_logs (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
             VALUES (:user_id, :action, :entity_type, :entity_id, :old_values, :new_values, :ip, :ua)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues !== null ? json_encode($oldValues, JSON_THROW_ON_ERROR) : null,
            'new_values' => $newValues !== null ? json_encode($newValues, JSON_THROW_ON_ERROR) : null,
            'ip' => $ip,
            'ua' => $ua,
        ]);
    }

    public function recent(int $limit = 100): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM activity_logs ORDER BY id DESC LIMIT :limit'
        );
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }
}
