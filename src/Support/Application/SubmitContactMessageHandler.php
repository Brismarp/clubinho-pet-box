<?php

declare(strict_types=1);

namespace ClubinhoPet\Support\Application;

use ClubinhoPet\Shared\Domain\Email;
use ClubinhoPet\Support\Domain\ContactMessage;
use ClubinhoPet\Support\Domain\ContactMessageRepository;

final class SubmitContactMessageHandler
{
    public function __construct(private ContactMessageRepository $messages)
    {
    }

    /** @return array<string, mixed> */
    public function handle(string $name, string $email, string $subject, string $message): array
    {
        $entity = ContactMessage::submit($name, Email::fromString($email), $subject, $message);
        $this->messages->save($entity);

        return $entity->toArray();
    }
}
