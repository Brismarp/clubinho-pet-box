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
            $paymentStatusRaw = strtolower((string) ($payload['status']
                ?? $payload['data']['status']
                ?? ''));
            $email = (string) ($payload['email'] ?? $payload['data']['email'] ?? '');
            $amountCents = $this->extractAmountCents($payload);

            if ($externalId !== '' || $email !== '' || $paymentStatusRaw !== '') {
                $this->upsertPayment($externalId, $email, $paymentStatusRaw, $amountCents, $payload);
            }

            if ($externalId !== '') {
                $subStatus = match (true) {
                    in_array($paymentStatusRaw, ['paid', 'approved', 'active', 'completed'], true) => 'active',
                    in_array($paymentStatusRaw, ['cancelled', 'canceled', 'refunded'], true) => 'cancelled',
                    in_array($paymentStatusRaw, ['paused'], true) => 'paused',
                    default => null,
                };
                if ($subStatus !== null) {
                    $upd = $this->pdo->prepare(
                        'UPDATE subscriptions SET status = ?, external_id = COALESCE(external_id, ?) WHERE external_id = ? OR notes LIKE ?'
                    );
                    $upd->execute([$subStatus, $externalId, $externalId, '%' . $externalId . '%']);
                } elseif ($email !== '') {
                    $byEmail = $this->pdo->prepare(
                        'UPDATE subscriptions SET external_id = ? WHERE email = ? AND (external_id IS NULL OR external_id = \'\') ORDER BY id DESC LIMIT 1'
                    );
                    $byEmail->execute([$externalId, $email]);
                }
            } elseif ($paymentStatusRaw === '' && $email === '') {
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

    /** @param array<string, mixed> $payload */
    private function extractAmountCents(array $payload): int
    {
        $raw = $payload['amount_cents']
            ?? $payload['amount']
            ?? $payload['data']['amount']
            ?? $payload['data']['amount_cents']
            ?? $payload['value']
            ?? 0;
        if (is_string($raw) && str_contains($raw, '.')) {
            return (int) round((float) $raw * 100);
        }
        $n = (int) $raw;
        // Heuristic: values like 10147 might already be cents; values < 1000 often reais
        if ($n > 0 && $n < 1000 && !isset($payload['amount_cents']) && !isset($payload['data']['amount_cents'])) {
            return $n * 100;
        }

        return max(0, $n);
    }

    /** @param array<string, mixed> $payload */
    private function upsertPayment(
        string $externalId,
        string $email,
        string $paymentStatusRaw,
        int $amountCents,
        array $payload,
    ): void {
        $payStatus = match (true) {
            in_array($paymentStatusRaw, ['paid', 'approved', 'active', 'completed'], true) => 'paid',
            in_array($paymentStatusRaw, ['failed', 'declined'], true) => 'failed',
            in_array($paymentStatusRaw, ['refunded'], true) => 'refunded',
            in_array($paymentStatusRaw, ['cancelled', 'canceled'], true) => 'cancelled',
            $paymentStatusRaw === '' => 'pending',
            default => 'pending',
        };

        $subscriptionId = null;
        if ($externalId !== '') {
            $s = $this->pdo->prepare('SELECT id FROM subscriptions WHERE external_id = ? LIMIT 1');
            $s->execute([$externalId]);
            $subscriptionId = $s->fetchColumn() ?: null;
        }
        if ($subscriptionId === null && $email !== '') {
            $s = $this->pdo->prepare('SELECT id FROM subscriptions WHERE email = ? ORDER BY id DESC LIMIT 1');
            $s->execute([$email]);
            $subscriptionId = $s->fetchColumn() ?: null;
        }

        $existingId = null;
        if ($externalId !== '') {
            $e = $this->pdo->prepare('SELECT id FROM payments WHERE provider = \'kirvano\' AND external_id = ? LIMIT 1');
            $e->execute([$externalId]);
            $existingId = $e->fetchColumn() ?: null;
        }

        $paidAt = $payStatus === 'paid' ? date('Y-m-d H:i:s') : null;
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        if ($existingId) {
            $this->pdo->prepare(
                'UPDATE payments SET subscription_id = COALESCE(?, subscription_id), customer_email = COALESCE(NULLIF(?, \'\'), customer_email),
                 amount_cents = CASE WHEN ? > 0 THEN ? ELSE amount_cents END,
                 status = ?, paid_at = COALESCE(?, paid_at), payload_json = ?
                 WHERE id = ?'
            )->execute([
                $subscriptionId !== null ? (int) $subscriptionId : null,
                $email,
                $amountCents,
                $amountCents,
                $payStatus,
                $paidAt,
                $json,
                (int) $existingId,
            ]);
        } else {
            $this->pdo->prepare(
                'INSERT INTO payments (subscription_id, customer_email, provider, external_id, amount_cents, currency, status, paid_at, payload_json)
                 VALUES (?, ?, \'kirvano\', ?, ?, \'BRL\', ?, ?, ?)'
            )->execute([
                $subscriptionId !== null ? (int) $subscriptionId : null,
                $email !== '' ? $email : null,
                $externalId !== '' ? $externalId : null,
                $amountCents,
                $payStatus,
                $paidAt,
                $json,
            ]);
        }
    }
}
