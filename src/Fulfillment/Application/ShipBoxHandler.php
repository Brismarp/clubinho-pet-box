<?php

declare(strict_types=1);

namespace ClubinhoPet\Fulfillment\Application;

use ClubinhoPet\Fulfillment\Domain\BoxRepository;
use ClubinhoPet\Integrations\Domain\Notifier;
use ClubinhoPet\Integrations\Domain\ShipmentProvider;
use ClubinhoPet\Platform\Domain\ActivityLogRepository;
use ClubinhoPet\Shared\Domain\DomainException;

final class ShipBoxHandler
{
    public function __construct(
        private BoxRepository $boxes,
        private ?ShipmentProvider $shipments = null,
        private ?ActivityLogRepository $logs = null,
        private ?Notifier $notifier = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function handle(int $boxId, ?string $tracking = null, ?int $actorUserId = null): array
    {
        $box = $this->boxes->findById($boxId);
        if ($box === null) {
            throw new DomainException('Box not found.');
        }

        if (($tracking === null || $tracking === '') && $this->shipments !== null) {
            $created = $this->shipments->createShipment([
                'box_id' => $boxId,
                'subscription_id' => $box->subscriptionId(),
            ]);
            $tracking = (string) ($created['tracking_number'] ?? '');
        }

        $box->markShipped($tracking !== '' ? $tracking : null);
        $this->boxes->save($box);
        $data = $box->toArray();

        $this->logs?->log($actorUserId, 'box.ship', 'boxes', $boxId, null, [
            'tracking_number' => $data['tracking_number'] ?? null,
            'status' => $data['status'] ?? null,
        ]);

        $this->notifier?->send('log', 'ops@clubinhopetbox.local', 'box.shipped', [
            'box_id' => $boxId,
            'tracking_number' => $data['tracking_number'] ?? null,
        ]);

        return $data;
    }
}
