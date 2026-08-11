<?php

declare(strict_types=1);

namespace ClubinhoPet\Content\Infrastructure\Persistence;

use ClubinhoPet\Content\Domain\HappyMedia;
use ClubinhoPet\Content\Domain\HappyMediaRepository;
use PDO;

final class PdoHappyMediaRepository implements HappyMediaRepository
{
    public function __construct(private PDO $pdo) {}

    public function findApproved(): array
    {
        $stmt = $this->pdo->query(
            "SELECT * FROM happy_media WHERE status = 'approved' ORDER BY display_order ASC, id DESC"
        );
        return array_map(static fn ($r) => HappyMedia::fromRow($r), $stmt->fetchAll() ?: []);
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM happy_media ORDER BY display_order ASC, id DESC');
        return array_map(static fn ($r) => HappyMedia::fromRow($r), $stmt->fetchAll() ?: []);
    }

    public function findById(int $id): ?HappyMedia
    {
        $stmt = $this->pdo->prepare('SELECT * FROM happy_media WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? HappyMedia::fromRow($row) : null;
    }

    public function save(HappyMedia $item): void
    {
        $data = [
            'type' => $item->type(),
            'media_path' => $item->mediaPath(),
            'media_url' => $item->mediaUrl(),
            'pet_name' => $item->petName(),
            'tutor_handle' => $item->tutorHandle(),
            'orientation' => $item->orientation(),
            'status' => $item->status(),
            'display_order' => $item->displayOrder(),
        ];
        if ($item->id() === null) {
            $stmt = $this->pdo->prepare(
                'INSERT INTO happy_media
                (type, media_path, media_url, pet_name, tutor_handle, orientation, status, display_order)
                VALUES
                (:type, :media_path, :media_url, :pet_name, :tutor_handle, :orientation, :status, :display_order)'
            );
            $stmt->execute($data);
            $item->assignId((int) $this->pdo->lastInsertId());
            return;
        }
        $data['id'] = $item->id();
        $stmt = $this->pdo->prepare(
            'UPDATE happy_media SET type=:type, media_path=:media_path, media_url=:media_url,
             pet_name=:pet_name, tutor_handle=:tutor_handle, orientation=:orientation,
             status=:status, display_order=:display_order, updated_at=CURRENT_TIMESTAMP
             WHERE id=:id'
        );
        $stmt->execute($data);
    }
}
