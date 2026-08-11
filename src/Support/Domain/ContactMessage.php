<?php

declare(strict_types=1);

namespace ClubinhoPet\Support\Domain;

use ClubinhoPet\Shared\Domain\DomainException;
use ClubinhoPet\Shared\Domain\Email;

final class ContactMessage
{
    public function __construct(
        private ?int $id,
        private string $name,
        private Email $email,
        private string $subject,
        private string $message,
        private string $status = 'new',
    ) {
        if (trim($name) === '' || trim($subject) === '' || trim($message) === '') {
            throw new DomainException('Name, subject and message are required.');
        }
    }

    public static function submit(string $name, Email $email, string $subject, string $message): self
    {
        return new self(null, $name, $email, $subject, $message, 'new');
    }

    /** @param array<string, mixed> $row */
    public static function fromRow(array $row): self
    {
        $msg = new self(
            (int) $row['id'],
            (string) $row['name'],
            Email::fromString((string) $row['email']),
            (string) $row['subject'],
            (string) $row['message'],
            (string) ($row['status'] ?? 'new'),
        );

        return $msg;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function subject(): string
    {
        return $this->subject;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function markRead(): void
    {
        if ($this->status === 'new') {
            $this->status = 'read';
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email->value(),
            'subject' => $this->subject,
            'message' => $this->message,
            'status' => $this->status,
        ];
    }
}
