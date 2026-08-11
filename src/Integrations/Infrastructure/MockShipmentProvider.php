<?php

declare(strict_types=1);

namespace ClubinhoPet\Integrations\Infrastructure;

use ClubinhoPet\Integrations\Domain\ShipmentProvider;

final class MockShipmentProvider implements ShipmentProvider
{
    public function createShipment(array $context): array
    {
        $tracking = 'MOCK-TRACK-' . strtoupper(bin2hex(random_bytes(4)));

        return [
            'tracking_number' => $tracking,
            'carrier' => 'MockCarrier',
            'label_url' => null,
        ];
    }

    public function getTracking(string $trackingNumber): array
    {
        return [
            'tracking_number' => $trackingNumber,
            'status' => 'in_transit',
            'events' => [
                [
                    'at' => date('c'),
                    'description' => 'Objeto em trânsito (mock)',
                ],
            ],
        ];
    }
}
