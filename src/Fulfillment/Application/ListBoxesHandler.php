<?php

declare(strict_types=1);

namespace ClubinhoPet\Fulfillment\Application;

use ClubinhoPet\Fulfillment\Domain\BoxRepository;

final class ListBoxesHandler
{
    public function __construct(private BoxRepository $boxes)
    {
    }

    /** @return list<array<string, mixed>> */
    public function handle(int $limit = 100): array
    {
        return array_map(static fn ($b) => $b->toArray(), $this->boxes->findAll($limit));
    }
}
