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

    public function findAll(int $limit = 100): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT :limit'
        );
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
}
