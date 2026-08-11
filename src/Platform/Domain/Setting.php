<?php

declare(strict_types=1);

namespace ClubinhoPet\Platform\Domain;

final class Setting
{
    public function __construct(
        private ?int $id,
        private string $key,
        private ?string $value,
        private string $type,
        private ?string $description,
        private bool $public,
        private int $version,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromRow(array $row): self
    {
        return new self(
            (int) $row['id'],
            (string) $row['key'],
            isset($row['value']) ? (string) $row['value'] : null,
            (string) ($row['type'] ?? 'string'),
            isset($row['description']) ? (string) $row['description'] : null,
            (bool) ($row['is_public'] ?? false),
            (int) ($row['version'] ?? 1),
        );
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function key(): string
    {
        return $this->key;
    }

    public function value(): ?string
    {
        return $this->value;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function changeValue(string $value): void
    {
        $this->value = $value;
        $this->version++;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'value' => $this->value,
            'type' => $this->type,
            'description' => $this->description,
            'is_public' => $this->public,
            'version' => $this->version,
        ];
    }
}
