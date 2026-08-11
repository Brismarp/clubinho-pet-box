<?php

declare(strict_types=1);

namespace ClubinhoPet\Analytics\Application;

use ClubinhoPet\Analytics\Domain\LeadRepository;

final class ListLeadsHandler
{
    public function __construct(private LeadRepository $leads)
    {
    }

    /** @return list<array<string, mixed>> */
    public function handle(int $limit = 100): array
    {
        return array_map(static fn ($lead) => $lead->toArray(), $this->leads->findRecent($limit));
    }
}
