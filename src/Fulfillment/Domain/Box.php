<?php

declare(strict_types=1);

namespace ClubinhoPet\Fulfillment\Domain;

final class Box
{
    public function __construct(
        private ?int $id,
        private int $subscriptionId,
        private ?int $boxNumber,
        private ?string $sendDate,
        private ?string $estimatedDeliveryDate,
        private ?string $actualDeliveryDate,
        private ?string $trackingNumber,
        private string $status,
        private ?array $contents,
        private ?string $notes,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromRow(array $row): self
    {
        $contents = $row['contents'] ?? null;
        if (is_string($contents)) {
            $decoded = json_decode($contents, true);
            $contents = is_array($decoded) ? $decoded : null;
        }

        return new self(
            (int) $row['id'],
            (int) $row['subscription_id'],
            isset($row['box_number']) ? (int) $row['box_number'] : null,
            isset($row['send_date']) ? (string) $row['send_date'] : null,
            isset($row['estimated_delivery_date']) ? (string) $row['estimated_delivery_date'] : null,
            isset($row['actual_delivery_date']) ? (string) $row['actual_delivery_date'] : null,
            isset($row['tracking_number']) ? (string) $row['tracking_number'] : null,
            (string) ($row['status'] ?? 'preparing'),
            $contents,
            isset($row['notes']) ? (string) $row['notes'] : null,
        );
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    public function subscriptionId(): int
    {
        return $this->subscriptionId;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function markShipped(?string $tracking = null): void
    {
        $this->status = 'shipped';
        if ($tracking !== null) {
            $this->trackingNumber = $tracking;
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'subscription_id' => $this->subscriptionId,
            'box_number' => $this->boxNumber,
            'send_date' => $this->sendDate,
            'estimated_delivery_date' => $this->estimatedDeliveryDate,
            'actual_delivery_date' => $this->actualDeliveryDate,
            'tracking_number' => $this->trackingNumber,
            'status' => $this->status,
            'contents' => $this->contents,
            'notes' => $this->notes,
        ];
    }

    public static function create(int $subscriptionId, ?int $boxNumber = null, ?string $notes = null): self
    {
        return new self(
            null,
            $subscriptionId,
            $boxNumber,
            date('Y-m-d'),
            null,
            null,
            null,
            'preparing',
            null,
            $notes,
        );
    }

    public function boxNumber(): ?int
    {
        return $this->boxNumber;
    }

    public function sendDate(): ?string
    {
        return $this->sendDate;
    }

    public function trackingNumber(): ?string
    {
        return $this->trackingNumber;
    }

    public function notes(): ?string
    {
        return $this->notes;
    }
}
