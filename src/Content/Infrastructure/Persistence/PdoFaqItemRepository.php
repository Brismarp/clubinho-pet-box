<?php

declare(strict_types=1);

namespace ClubinhoPet\Content\Infrastructure\Persistence;

use ClubinhoPet\Content\Domain\FaqItem;
use ClubinhoPet\Content\Domain\FaqItemRepository;
use PDO;

final class PdoFaqItemRepository implements FaqItemRepository
{
    public function __construct(private PDO $pdo) {}

    public function findPublished(): array
    {
        $stmt = $this->pdo->query(
            'SELECT * FROM faq_items WHERE is_published = 1 ORDER BY display_order ASC, id ASC'
        );
        return array_map(static fn ($r) => FaqItem::fromRow($r), $stmt->fetchAll() ?: []);
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM faq_items ORDER BY display_order ASC, id ASC');
        return array_map(static fn ($r) => FaqItem::fromRow($r), $stmt->fetchAll() ?: []);
    }

    public function findById(int $id): ?FaqItem
    {
        $stmt = $this->pdo->prepare('SELECT * FROM faq_items WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? FaqItem::fromRow($row) : null;
    }

    public function save(FaqItem $item): void
    {
        $data = [
            'question' => $item->question(),
            'answer' => $item->answer(),
            'display_order' => $item->displayOrder(),
            'is_published' => $item->isPublished() ? 1 : 0,
        ];
        if ($item->id() === null) {
            $stmt = $this->pdo->prepare(
                'INSERT INTO faq_items (question, answer, display_order, is_published)
                 VALUES (:question, :answer, :display_order, :is_published)'
            );
            $stmt->execute($data);
            $item->assignId((int) $this->pdo->lastInsertId());
            return;
        }
        $data['id'] = $item->id();
        $stmt = $this->pdo->prepare(
            'UPDATE faq_items SET question=:question, answer=:answer, display_order=:display_order,
             is_published=:is_published, updated_at=CURRENT_TIMESTAMP WHERE id=:id'
        );
        $stmt->execute($data);
    }
}
