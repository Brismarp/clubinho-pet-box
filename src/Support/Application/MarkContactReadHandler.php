<?php

declare(strict_types=1);

namespace ClubinhoPet\Support\Application;

use ClubinhoPet\Shared\Domain\DomainException;
use ClubinhoPet\Support\Domain\ContactMessageRepository;

final class MarkContactReadHandler
{
    public function __construct(private ContactMessageRepository $messages)
    {
    }

    /** @return array<string, mixed> */
    public function handle(int $id): array
    {
        $message = $this->messages->findById($id);
        if ($message === null) {
            throw new DomainException('Contact message not found.');
        }
        $message->markRead();
        $this->messages->save($message);

        return $message->toArray();
    }
}
