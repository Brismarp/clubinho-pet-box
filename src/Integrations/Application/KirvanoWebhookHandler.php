<?php

declare(strict_types=1);

namespace ClubinhoPet\Integrations\Application;

use ClubinhoPet\Platform\Domain\ActivityLogRepository;
use PDO;

final class KirvanoWebhookHandler
{
    public function __construct(
        private PDO $pdo,
        private ActivityLogRepository $logs,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, string> $headers
     * @return array{ok: bool, event_id: int, status: string}
     */
    public function handle(array $payload, array $headers = []): array
    {
        $eventType = (string) ($payload['event'] ?? $payload['type'] ?? $payload['event_type'] ?? 'unknown');
        $ins = $this->pdo->prepare(
            'INSERT INTO webhook_events (provider, event_type, payload_json, headers_json, status, processed_at)
             VALUES (\'kirvano\', ?, ?, ?, \'received\', NULL)'
        );
        $ins->execute([
            $eventType,
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            $headers === [] ? null : json_encode($headers, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        ]);
        $eventId = (int) $this->pdo->lastInsertId();

        $status = 'processed';
        try {
            $externalId = (string) ($payload['external_id']
                ?? $payload['subscription_id']
                ?? $payload['data']['id']
                ?? '');
            $paymentStatus = strtolower((string) ($payload['status']
                ?? $payload['data']['status']
                ?? ''));

            if ($externalId !== '') {
                $subStatus = match (true) {
                    in_array($paymentStatus, ['paid', 'approved', 'active', 'completed'], true) => 'active',
                    in_array($paymentStatus, ['cancelled', 'canceled', 'refunded'], true) => 'cancelled',
                    in_array($paymentStatus, ['paused'], true) => 'paused',
                    default => null,
                };
                if ($subStatus !== null) {
                    $upd = $this->pdo->prepare(
                        'UPDATE subscriptions SET status = ?, external_id = COALESCE(external_id, ?) WHERE external_id = ? OR notes LIKE ?'
                    );
                    $upd->execute([$subStatus, $externalId, $externalId, '%' . $externalId . '%']);
                } else {
                    // Ensure external_id is stored if we find by email
                    $email = (string) ($payload['email'] ?? $payload['data']['email'] ?? '');
                    if ($email !== '') {
                        $byEmail = $this->pdo->prepare(
                            'UPDATE subscriptions SET external_id = ? WHERE email = ? AND (external_id IS NULL OR external_id = \'\') ORDER BY id DESC LIMIT 1'
                        );
                        $byEmail->execute([$externalId, $email]);
                    }
                }
            } else {
                $status = 'ignored';
            }

            $this->pdo->prepare(
                'UPDATE webhook_events SET status = ?, processed_at = NOW() WHERE id = ?'
            )->execute([$status, $eventId]);
        } catch (\Throwable $e) {
            $status = 'error';
            $this->pdo->prepare(
                'UPDATE webhook_events SET status = \'error\', processed_at = NOW() WHERE id = ?'
            )->execute([$eventId]);
            $this->logs->log(null, 'webhook.kirvano.error', 'webhook_events', $eventId, null, [
                'message' => $e->getMessage(),
            ]);
        }

        $this->logs->log(null, 'webhook.kirvano.' . $status, 'webhook_events', $eventId, null, [
            'event_type' => $eventType,
            'status' => $status,
        ]);

        return ['ok' => true, 'event_id' => $eventId, 'status' => $status];
    }
}
