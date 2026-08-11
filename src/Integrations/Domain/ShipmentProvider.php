<?php

declare(strict_types=1);

namespace ClubinhoPet\Integrations\Domain;

interface ShipmentProvider
{
    /**
     * @param array<string, mixed> $context
     * @return array{tracking_number: string, carrier: string, label_url: ?string}
     */
    public function createShipment(array $context): array;

    /** @return array{tracking_number: string, status: string, events: list<array<string, mixed>>} */
    public function getTracking(string $trackingNumber): array;
}
