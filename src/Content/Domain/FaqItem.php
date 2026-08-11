<?php

declare(strict_types=1);

namespace ClubinhoPet\Content\Domain;

use ClubinhoPet\Shared\Domain\DomainException;

final class FaqItem
{
    public function __construct(
        private ?int $id,
        private string $question,
        private string $answer,
        private int $displayOrder = 0,
        private bool $isPublished = true,
    ) {
        if (trim($question) === '' || trim($answer) === '') {
            throw new DomainException('Question and answer are required.');
        }
    }

    /** @param array<string, mixed> $row */
    public static function fromRow(array $row): self
    {
        return new self(
            (int) $row['id'],
            (string) $row['question'],
            (string) $row['answer'],
            (int) ($row['display_order'] ?? 0),
            (bool) ($row['is_published'] ?? true),
        );
    }

    public function id(): ?int { return $this->id; }
    public function assignId(int $id): void { $this->id = $id; }
    public function question(): string { return $this->question; }
    public function answer(): string { return $this->answer; }
    public function displayOrder(): int { return $this->displayOrder; }
    public function isPublished(): bool { return $this->isPublished; }

    public function apply(string $question, string $answer, int $displayOrder, bool $isPublished): void
    {
        if (trim($question) === '' || trim($answer) === '') {
            throw new DomainException('Question and answer are required.');
        }
        $this->question = $question;
        $this->answer = $answer;
        $this->displayOrder = $displayOrder;
        $this->isPublished = $isPublished;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'question' => $this->question,
            'answer' => $this->answer,
            'display_order' => $this->displayOrder,
            'is_published' => $this->isPublished,
        ];
    }
}
