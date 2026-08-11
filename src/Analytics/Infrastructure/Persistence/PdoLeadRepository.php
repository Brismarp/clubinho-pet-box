<?php

declare(strict_types=1);

namespace ClubinhoPet\Analytics\Infrastructure\Persistence;

use ClubinhoPet\Analytics\Domain\Lead;
use ClubinhoPet\Analytics\Domain\LeadRepository;
use PDO;

final class PdoLeadRepository implements LeadRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function save(Lead $lead): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO leads (name, email, phone, pet_type, city, visitor_id, accept_newsletter)
             VALUES (:name, :email, :phone, :pet_type, :city, :visitor_id, :accept_newsletter)'
        );
        $stmt->execute([
            'name' => $lead->name(),
            'email' => $lead->email()->value(),
            'phone' => $lead->phone(),
            'pet_type' => $lead->petType(),
            'city' => $lead->city(),
            'visitor_id' => $lead->visitorId(),
            'accept_newsletter' => $lead->acceptNewsletter() ? 1 : 0,
        ]);
        $lead->assignId((int) $this->pdo->lastInsertId());
    }

    public function findRecent(int $limit = 100): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM leads ORDER BY created_at DESC LIMIT :limit');
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll() ?: [];

        return array_map(static fn (array $row) => Lead::fromRow($row), $rows);
    }

    public function countSince(\DateTimeInterface $since): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM leads WHERE created_at >= :since');
        $stmt->execute(['since' => $since->format('Y-m-d H:i:s')]);

        return (int) $stmt->fetchColumn();
    }
}
