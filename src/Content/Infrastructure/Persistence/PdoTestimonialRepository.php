<?php

declare(strict_types=1);

namespace ClubinhoPet\Content\Infrastructure\Persistence;

use ClubinhoPet\Content\Domain\Testimonial;
use ClubinhoPet\Content\Domain\TestimonialRepository;
use PDO;

final class PdoTestimonialRepository implements TestimonialRepository
{
    public function __construct(private PDO $pdo) {}

    public function findPublished(): array
    {
        $stmt = $this->pdo->query(
            "SELECT * FROM testimonials WHERE status = 'published' ORDER BY display_order ASC, id DESC"
        );
        return array_map(static fn ($r) => Testimonial::fromRow($r), $stmt->fetchAll() ?: []);
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM testimonials ORDER BY display_order ASC, id DESC');
        return array_map(static fn ($r) => Testimonial::fromRow($r), $stmt->fetchAll() ?: []);
    }

    public function findById(int $id): ?Testimonial
    {
        $stmt = $this->pdo->prepare('SELECT * FROM testimonials WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? Testimonial::fromRow($row) : null;
    }

    public function save(Testimonial $item): void
    {
        $data = [
            'name' => $item->name(),
            'pet_name' => $item->petName(),
            'photo_path' => $item->photoPath(),
            'quote' => $item->quote(),
            'status' => $item->status(),
            'display_order' => $item->displayOrder(),
        ];
        if ($item->id() === null) {
            $stmt = $this->pdo->prepare(
                'INSERT INTO testimonials (name, pet_name, photo_path, quote, status, display_order)
                 VALUES (:name, :pet_name, :photo_path, :quote, :status, :display_order)'
            );
            $stmt->execute($data);
            $item->assignId((int) $this->pdo->lastInsertId());
            return;
        }
        $data['id'] = $item->id();
        $stmt = $this->pdo->prepare(
            'UPDATE testimonials SET name=:name, pet_name=:pet_name, photo_path=:photo_path,
             quote=:quote, status=:status, display_order=:display_order, updated_at=CURRENT_TIMESTAMP
             WHERE id=:id'
        );
        $stmt->execute($data);
    }
}
