<?php

declare(strict_types=1);

namespace ClubinhoPet\Integrations\Infrastructure;

use ClubinhoPet\Integrations\Domain\ShipmentProvider;
use PDO;

/** Mock carrier with persisted shipment_events timeline. */
final class MockShipmentProvider implements ShipmentProvider
{
    /** @var list<array{code:string,status:string,description:string}> */
    public const PIPELINE = [
        ['code' => 'label_created', 'status' => 'label_created', 'description' => 'Etiqueta gerada na MockCarrier'],
        ['code' => 'collected', 'status' => 'collected', 'description' => 'Objeto coletado no centro de distribuição'],
        ['code' => 'in_transit', 'status' => 'in_transit', 'description' => 'Objeto em trânsito'],
        ['code' => 'out_for_delivery', 'status' => 'out_for_delivery', 'description' => 'Saiu para entrega'],
        ['code' => 'delivered', 'status' => 'delivered', 'description' => 'Entregue ao destinatário'],
    ];

    public function __construct(private PDO $pdo)
    {
    }

    public function createShipment(array $context): array
    {
        $boxId = isset($context['box_id']) ? (int) $context['box_id'] : 0;
        $hex = strtoupper(bin2hex(random_bytes(4)));
        $tracking = $boxId > 0
            ? sprintf('MOCK-TRACK-%d-%s', $boxId, $hex)
            : 'MOCK-TRACK-' . $hex;

        $this->seedTimeline($boxId > 0 ? $boxId : null, $tracking, 2);

        return [
            'tracking_number' => $tracking,
            'carrier' => 'MockCarrier',
            'label_url' => null,
        ];
    }

    public function getTracking(string $trackingNumber): array
    {
        $events = $this->listEventsByTracking($trackingNumber);
        $status = $events !== [] ? (string) $events[array_key_last($events)]['status'] : 'unknown';

        return [
            'tracking_number' => $trackingNumber,
            'status' => $status,
            'events' => array_map(static fn (array $e): array => [
                'at' => (string) $e['occurred_at'],
                'code' => (string) $e['event_code'],
                'status' => (string) $e['status'],
                'description' => (string) $e['description'],
            ], $events),
        ];
    }

    /** Seed the first N steps of the mock pipeline (default: label + collected). */
    public function seedTimeline(?int $boxId, string $trackingNumber, int $steps = 2): void
    {
        $existing = $this->pdo->prepare(
            'SELECT COUNT(*) FROM shipment_events WHERE tracking_number = ?'
        );
        $existing->execute([$trackingNumber]);
        if ((int) $existing->fetchColumn() > 0) {
            return;
        }

        $steps = max(1, min($steps, count(self::PIPELINE)));
        $base = time();
        for ($i = 0; $i < $steps; $i++) {
            $step = self::PIPELINE[$i];
            $this->insertEvent(
                $boxId,
                $trackingNumber,
                $step['status'],
                $step['code'],
                $step['description'],
                date('Y-m-d H:i:s', $base - (($steps - $i) * 3600)),
            );
        }
    }

    /**
     * Advance tracking by one pipeline step. Returns null if already delivered.
     *
     * @return array{event: array<string, mixed>, status: string, delivered: bool}|null
     */
    public function advance(string $trackingNumber, ?int $boxId = null): ?array
    {
        $codes = $this->pdo->prepare(
            'SELECT event_code FROM shipment_events WHERE tracking_number = ? ORDER BY occurred_at ASC, id ASC'
        );
        $codes->execute([$trackingNumber]);
        $done = $codes->fetchAll(PDO::FETCH_COLUMN) ?: [];

        $next = null;
        foreach (self::PIPELINE as $step) {
            if (!in_array($step['code'], $done, true)) {
                $next = $step;
                break;
            }
        }
        if ($next === null) {
            return null;
        }

        if ($boxId === null) {
            $q = $this->pdo->prepare(
                'SELECT box_id FROM shipment_events WHERE tracking_number = ? AND box_id IS NOT NULL ORDER BY id DESC LIMIT 1'
            );
            $q->execute([$trackingNumber]);
            $found = $q->fetchColumn();
            $boxId = $found !== false ? (int) $found : null;
        }

        $id = $this->insertEvent(
            $boxId,
            $trackingNumber,
            $next['status'],
            $next['code'],
            $next['description'],
            date('Y-m-d H:i:s'),
        );

        $delivered = $next['code'] === 'delivered';
        if ($delivered && $boxId !== null) {
            $this->pdo->prepare(
                'UPDATE boxes SET status = \'delivered\', actual_delivery_date = CURDATE() WHERE id = ?'
            )->execute([$boxId]);
        }

        return [
            'event' => [
                'id' => $id,
                'code' => $next['code'],
                'status' => $next['status'],
                'description' => $next['description'],
            ],
            'status' => $next['status'],
            'delivered' => $delivered,
        ];
    }

    /** @return list<array<string, mixed>> */
    public function listEventsByBox(int $boxId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM shipment_events WHERE box_id = ? ORDER BY occurred_at ASC, id ASC'
        );
        $stmt->execute([$boxId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** @return list<array<string, mixed>> */
    public function listEventsByTracking(string $trackingNumber): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM shipment_events WHERE tracking_number = ? ORDER BY occurred_at ASC, id ASC'
        );
        $stmt->execute([$trackingNumber]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function insertEvent(
        ?int $boxId,
        string $tracking,
        string $status,
        string $code,
        string $description,
        string $occurredAt,
    ): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO shipment_events (box_id, tracking_number, status, event_code, description, occurred_at)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$boxId, $tracking, $status, $code, $description, $occurredAt]);

        return (int) $this->pdo->lastInsertId();
    }
}
