<?php

declare(strict_types=1);

namespace ClubinhoPet\Support\Infrastructure\Persistence;

use ClubinhoPet\Support\Domain\ContactMessage;
use ClubinhoPet\Support\Domain\ContactMessageRepository;
use PDO;

final class PdoContactMessageRepository implements ContactMessageRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function save(ContactMessage $message): void
    {
        if ($message->id() === null) {
            $stmt = $this->pdo->prepare(
                'INSERT INTO contact_messages (name, email, subject, message, status)
                 VALUES (:name, :email, :subject, :message, :status)'
            );
            $stmt->execute([
                'name' => $message->name(),
                'email' => $message->email()->value(),
                'subject' => $message->subject(),
                'message' => $message->message(),
                'status' => $message->status(),
            ]);
            $message->assignId((int) $this->pdo->lastInsertId());

            return;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE contact_messages SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id'
        );
        $stmt->execute([
            'status' => $message->status(),
            'id' => $message->id(),
        ]);
    }

    public function findAll(int $limit = 100, ?string $status = null): array
    {
        $sql = 'SELECT * FROM contact_messages';
        if ($status !== null && $status !== '') {
            $sql .= ' WHERE status = :status';
        }
        $sql .= ' ORDER BY created_at DESC LIMIT :limit';
        $stmt = $this->pdo->prepare($sql);
        if ($status !== null && $status !== '') {
            $stmt->bindValue('status', $status);
        }
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll() ?: [];

        return array_map(static fn (array $row) => ContactMessage::fromRow($row), $rows);
    }

    public function findById(int $id): ?ContactMessage
    {
        $stmt = $this->pdo->prepare('SELECT * FROM contact_messages WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? ContactMessage::fromRow($row) : null;
    }

    public function countByStatus(string $status): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM contact_messages WHERE status = :status');
        $stmt->execute(['status' => $status]);

        return (int) $stmt->fetchColumn();
    }
}
