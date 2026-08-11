<?php

declare(strict_types=1);

namespace ClubinhoPet\Support\Application;

use ClubinhoPet\Support\Domain\ContactMessageRepository;

final class ListContactMessagesHandler
{
    public function __construct(private ContactMessageRepository $messages)
    {
    }

    /** @return list<array<string, mixed>> */
    public function handle(int $limit = 100, ?string $status = null): array
    {
        return array_map(static fn ($m) => $m->toArray(), $this->messages->findAll($limit, $status));
    }

    public function countNew(): int
    {
        return $this->messages->countByStatus('new');
    }
}
