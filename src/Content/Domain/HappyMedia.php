<?php

declare(strict_types=1);

namespace ClubinhoPet\Content\Domain;

use ClubinhoPet\Shared\Domain\DomainException;

final class HappyMedia
{
    public function __construct(
        private ?int $id,
        private string $type,
        private string $petName,
        private string $status = 'pending',
        private ?string $mediaPath = null,
        private ?string $mediaUrl = null,
        private ?string $tutorHandle = null,
        private string $orientation = 'landscape',
        private int $displayOrder = 0,
    ) {
        if (trim($petName) === '') {
            throw new DomainException('Pet name is required.');
        }
        if (!in_array($type, ['image', 'video'], true)) {
            throw new DomainException('Invalid media type.');
        }
        if (!in_array($status, ['pending', 'approved', 'rejected'], true)) {
            throw new DomainException('Invalid media status.');
        }
        if (($mediaPath === null || $mediaPath === '') && ($mediaUrl === null || $mediaUrl === '')) {
            throw new DomainException('Media path or URL is required.');
        }
    }

    /** @param array<string, mixed> $row */
    public static function fromRow(array $row): self
    {
        return new self(
            (int) $row['id'],
            (string) $row['type'],
            (string) $row['pet_name'],
            (string) ($row['status'] ?? 'pending'),
            isset($row['media_path']) && $row['media_path'] !== '' ? (string) $row['media_path'] : null,
            isset($row['media_url']) && $row['media_url'] !== '' ? (string) $row['media_url'] : null,
            isset($row['tutor_handle']) && $row['tutor_handle'] !== '' ? (string) $row['tutor_handle'] : null,
            (string) ($row['orientation'] ?? 'landscape'),
            (int) ($row['display_order'] ?? 0),
        );
    }

    public function id(): ?int { return $this->id; }
    public function assignId(int $id): void { $this->id = $id; }
    public function type(): string { return $this->type; }
    public function petName(): string { return $this->petName; }
    public function status(): string { return $this->status; }
    public function mediaPath(): ?string { return $this->mediaPath; }
    public function mediaUrl(): ?string { return $this->mediaUrl; }
    public function tutorHandle(): ?string { return $this->tutorHandle; }
    public function orientation(): string { return $this->orientation; }
    public function displayOrder(): int { return $this->displayOrder; }

    public function apply(
        string $type,
        string $petName,
        string $status,
        ?string $mediaPath,
        ?string $mediaUrl,
        ?string $tutorHandle,
        string $orientation,
        int $displayOrder,
    ): void {
        $tmp = new self(null, $type, $petName, $status, $mediaPath, $mediaUrl, $tutorHandle, $orientation, $displayOrder);
        $this->type = $tmp->type();
        $this->petName = $tmp->petName();
        $this->status = $tmp->status();
        $this->mediaPath = $tmp->mediaPath();
        $this->mediaUrl = $tmp->mediaUrl();
        $this->tutorHandle = $tmp->tutorHandle();
        $this->orientation = $tmp->orientation();
        $this->displayOrder = $tmp->displayOrder();
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'media_path' => $this->mediaPath,
            'media_url' => $this->mediaUrl,
            'pet_name' => $this->petName,
            'tutor_handle' => $this->tutorHandle,
            'orientation' => $this->orientation,
            'status' => $this->status,
            'display_order' => $this->displayOrder,
        ];
    }
}
