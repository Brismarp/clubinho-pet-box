<?php

declare(strict_types=1);

namespace ClubinhoPet\Analytics\Domain;

interface LeadRepository
{
    public function save(Lead $lead): void;

    /** @return list<Lead> */
    public function findRecent(int $limit = 100): array;

    public function countSince(\DateTimeInterface $since): int;
}
