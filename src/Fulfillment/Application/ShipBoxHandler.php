<?php

declare(strict_types=1);

namespace ClubinhoPet\Fulfillment\Application;

use ClubinhoPet\Fulfillment\Domain\BoxRepository;
use ClubinhoPet\Integrations\Domain\Notifier;
use ClubinhoPet\Integrations\Domain\ShipmentProvider;
use ClubinhoPet\Integrations\Infrastructure\MockShipmentProvider;
use ClubinhoPet\Inventory\Application\StockLedger;
use ClubinhoPet\Platform\Domain\ActivityLogRepository;
use ClubinhoPet\Shared\Domain\DomainException;
use PDO;

final class ShipBoxHandler
{
    public function __construct(
        private BoxRepository $boxes,
        private ?ShipmentProvider $shipments = null,
        private ?ActivityLogRepository $logs = null,
        private ?Notifier $notifier = null,
        private ?StockLedger $stock = null,
        private ?PDO $pdo = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function handle(int $boxId, ?string $tracking = null, ?int $actorUserId = null): array
    {
        $box = $this->boxes->findById($boxId);
        if ($box === null) {
            throw new DomainException('Box not found.');
        }
        if (!in_array($box->status(), ['preparing'], true)) {
            throw new DomainException('Box já foi enviada ou não está preparando.');
        }

        $createdByMock = false;
        if (($tracking === null || $tracking === '') && $this->shipments !== null) {
            $created = $this->shipments->createShipment([
                'box_id' => $boxId,
                'subscription_id' => $box->subscriptionId(),
            ]);
            $tracking = (string) ($created['tracking_number'] ?? '');
            $createdByMock = true;
        }

        $box->markShipped($tracking !== '' ? $tracking : null);
        $this->boxes->save($box);
        $data = $box->toArray();

        if (!$createdByMock && $tracking !== null && $tracking !== '' && $this->shipments instanceof MockShipmentProvider) {
            $this->shipments->seedTimeline($boxId, $tracking, 2);
        }

        if ($this->stock !== null && $this->pdo !== null) {
            $items = $this->pdo->prepare('SELECT product_id, qty, source FROM box_items WHERE box_id = ?');
            $items->execute([$boxId]);
            foreach ($items->fetchAll(PDO::FETCH_ASSOC) ?: [] as $item) {
                $qty = max(1, (int) $item['qty']);
                $source = (string) ($item['source'] ?? 'manual');
                if ($source === 'recommendation') {
                    $this->stock->move(
                        (int) $item['product_id'],
                        0,
                        'ship',
                        $actorUserId,
                        'boxes',
                        $boxId,
                        'ship after reserve',
                    );
                } else {
                    $this->stock->move(
                        (int) $item['product_id'],
                        -$qty,
                        'ship',
                        $actorUserId,
                        'boxes',
                        $boxId,
                        'ship manual box',
                    );
                }
            }
        }

        $this->logs?->log($actorUserId, 'box.ship', 'boxes', $boxId, null, [
            'tracking_number' => $data['tracking_number'] ?? null,
            'status' => $data['status'] ?? null,
        ]);

        $notifyPayload = [
            'box_id' => $boxId,
            'tracking_number' => $data['tracking_number'] ?? null,
            'subscription_id' => $box->subscriptionId(),
        ];
        $this->notifier?->send('log', 'ops@clubinhopetbox.local', 'box.shipped', $notifyPayload);

        if ($this->pdo !== null && $this->notifier !== null) {
            $sub = $this->pdo->prepare('SELECT email, notes, pet_name FROM subscriptions WHERE id = ? LIMIT 1');
            $sub->execute([$box->subscriptionId()]);
            $srow = $sub->fetch(PDO::FETCH_ASSOC) ?: [];
            $email = (string) ($srow['email'] ?? '');
            if ($email !== '') {
                $notifyPayload['pet_name'] = $srow['pet_name'] ?? null;
                $this->notifier->send('email', $email, 'box.shipped', $notifyPayload);
                $phone = '';
                if (preg_match('/phone:\s*([^|]+)/', (string) ($srow['notes'] ?? ''), $m) === 1) {
                    $phone = trim($m[1]);
                }
                $this->notifier->send('whatsapp', $phone !== '' ? $phone : $email, 'box.shipped', $notifyPayload);
            }
        }

        if ($this->shipments instanceof MockShipmentProvider) {
            $data['shipment_events'] = $this->shipments->listEventsByBox($boxId);
        }

        return $data;
    }
}
