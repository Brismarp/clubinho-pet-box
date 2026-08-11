<?php

declare(strict_types=1);

namespace ClubinhoPet\Fulfillment\Application;

use ClubinhoPet\Integrations\Domain\Notifier;
use ClubinhoPet\Integrations\Infrastructure\MockShipmentProvider;
use ClubinhoPet\Platform\Domain\ActivityLogRepository;
use ClubinhoPet\Shared\Domain\DomainException;
use PDO;

final class AdvanceShipmentHandler
{
    public function __construct(
        private MockShipmentProvider $shipments,
        private PDO $pdo,
        private ActivityLogRepository $logs,
        private Notifier $notifier,
    ) {
    }

    /** @return array<string, mixed> */
    public function handle(int $boxId, ?int $actorUserId = null): array
    {
        $box = $this->pdo->prepare(
            'SELECT b.id, b.tracking_number, b.status, b.subscription_id, s.email, s.notes, s.pet_name
             FROM boxes b
             LEFT JOIN subscriptions s ON s.id = b.subscription_id
             WHERE b.id = ?
             LIMIT 1'
        );
        $box->execute([$boxId]);
        $row = $box->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            throw new DomainException('Box not found.');
        }

        $tracking = trim((string) ($row['tracking_number'] ?? ''));
        if ($tracking === '') {
            throw new DomainException('Box sem tracking — envie a box primeiro.');
        }

        $advanced = $this->shipments->advance($tracking, $boxId);
        if ($advanced === null) {
            return [
                'box_id' => $boxId,
                'tracking_number' => $tracking,
                'status' => 'delivered',
                'done' => true,
                'events' => $this->shipments->listEventsByBox($boxId),
            ];
        }

        $this->logs->log($actorUserId, 'shipment.advance', 'boxes', $boxId, null, [
            'tracking_number' => $tracking,
            'status' => $advanced['status'],
            'event_code' => $advanced['event']['code'] ?? null,
        ]);

        $email = (string) ($row['email'] ?? '');
        $payload = [
            'box_id' => $boxId,
            'tracking_number' => $tracking,
            'status' => $advanced['status'],
            'event' => $advanced['event'],
            'pet_name' => $row['pet_name'] ?? null,
            'delivered' => $advanced['delivered'],
        ];
        $template = $advanced['delivered'] ? 'shipment.delivered' : 'shipment.update';

        if ($email !== '') {
            $this->notifier->send('email', $email, $template, $payload);
            $phone = '';
            if (preg_match('/phone:\s*([^|]+)/', (string) ($row['notes'] ?? ''), $m) === 1) {
                $phone = trim($m[1]);
            }
            $this->notifier->send('whatsapp', $phone !== '' ? $phone : $email, $template, $payload);
        }

        return [
            'box_id' => $boxId,
            'tracking_number' => $tracking,
            'status' => $advanced['status'],
            'done' => $advanced['delivered'],
            'event' => $advanced['event'],
            'events' => $this->shipments->listEventsByBox($boxId),
        ];
    }
}
