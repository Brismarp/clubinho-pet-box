<?php

declare(strict_types=1);

namespace ClubinhoPet\Billing\Application;

use ClubinhoPet\Integrations\Domain\Notifier;
use ClubinhoPet\Platform\Domain\ActivityLogRepository;
use ClubinhoPet\Shared\Domain\DomainException;
use PDO;

/**
 * In-app mock PIX/card charge + strong subscription sync (provider=mock).
 */
final class ConfirmMockPaymentHandler
{
    public function __construct(
        private PDO $pdo,
        private ActivityLogRepository $logs,
        private Notifier $notifier,
    ) {
    }

    /**
     * @return array{
     *   payment_id: int,
     *   subscription_id: int,
     *   status: string,
     *   method: string,
     *   external_id: string,
     *   amount_cents: int,
     *   pix_code?: string,
     *   card_brand?: string
     * }
     */
    public function handle(int $subscriptionId, string $method, ?string $cardHolder = null): array
    {
        $method = strtolower(trim($method));
        if (!in_array($method, ['pix', 'card'], true)) {
            throw new DomainException('Método de pagamento inválido. Use pix ou card.');
        }

        $sub = $this->pdo->prepare(
            'SELECT s.id, s.email, s.status, s.pet_name, s.notes, p.price, p.name AS plan_name, p.slug AS plan_slug
             FROM subscriptions s
             LEFT JOIN plans p ON p.id = s.plan_id
             WHERE s.id = ?
             LIMIT 1'
        );
        $sub->execute([$subscriptionId]);
        $row = $sub->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            throw new DomainException('Assinatura não encontrada.');
        }

        $email = (string) ($row['email'] ?? '');
        $amountCents = (int) round(((float) ($row['price'] ?? 0)) * 100);
        if ($amountCents <= 0) {
            $amountCents = 8990;
        }

        $externalId = 'MOCK-' . strtoupper($method) . '-' . $subscriptionId . '-' . strtoupper(bin2hex(random_bytes(3)));
        $pixCode = null;
        $cardBrand = null;

        if ($method === 'pix') {
            $pixCode = '00020126580014BR.GOV.BCB.PIX0136' . substr(md5($externalId), 0, 32)
                . '520400005303986540' . number_format($amountCents / 100, 2, '.', '')
                . '5802BR5925CLUBINHO PET BOX MOCK6009SAO PAULO62070503***6304ABCD';
        } else {
            $cardBrand = 'visa_mock';
        }

        $payload = [
            'provider' => 'mock',
            'method' => $method,
            'subscription_id' => $subscriptionId,
            'plan_slug' => $row['plan_slug'] ?? null,
            'plan_name' => $row['plan_name'] ?? null,
            'card_holder' => $cardHolder,
            'card_brand' => $cardBrand,
            'pix_code' => $pixCode,
            'simulated' => true,
        ];

        $this->pdo->beginTransaction();
        try {
            $ins = $this->pdo->prepare(
                'INSERT INTO payments (subscription_id, customer_email, provider, external_id, amount_cents, currency, status, paid_at, payload_json)
                 VALUES (?, ?, \'mock\', ?, ?, \'BRL\', \'paid\', NOW(), ?)'
            );
            $ins->execute([
                $subscriptionId,
                $email !== '' ? $email : null,
                $externalId,
                $amountCents,
                json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            ]);
            $paymentId = (int) $this->pdo->lastInsertId();

            $upd = $this->pdo->prepare(
                'UPDATE subscriptions
                 SET status = \'active\',
                     start_date = COALESCE(start_date, CURDATE()),
                     external_id = ?,
                     notes = TRIM(CONCAT(COALESCE(notes, \'\'), \' | mock_paid:\', ?))
                 WHERE id = ?'
            );
            $upd->execute([$externalId, $externalId, $subscriptionId]);

            $this->pdo->commit();
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }

        $this->logs->log(null, 'payment.mock.paid', 'payments', $paymentId, null, [
            'subscription_id' => $subscriptionId,
            'method' => $method,
            'external_id' => $externalId,
            'amount_cents' => $amountCents,
        ]);

        $notifyPayload = [
            'subscription_id' => $subscriptionId,
            'payment_id' => $paymentId,
            'method' => $method,
            'amount_cents' => $amountCents,
            'plan_name' => $row['plan_name'] ?? null,
            'pet_name' => $row['pet_name'] ?? null,
            'external_id' => $externalId,
        ];

        if ($email !== '') {
            $this->notifier->send('email', $email, 'payment.confirmed', $notifyPayload);
            $phone = $this->extractPhone((string) ($row['notes'] ?? ''));
            $this->notifier->send('whatsapp', $phone !== '' ? $phone : $email, 'payment.confirmed', $notifyPayload);
        }

        $out = [
            'payment_id' => $paymentId,
            'subscription_id' => $subscriptionId,
            'status' => 'paid',
            'method' => $method,
            'external_id' => $externalId,
            'amount_cents' => $amountCents,
        ];
        if ($pixCode !== null) {
            $out['pix_code'] = $pixCode;
        }
        if ($cardBrand !== null) {
            $out['card_brand'] = $cardBrand;
        }

        return $out;
    }

    private function extractPhone(string $notes): string
    {
        if (preg_match('/phone:\s*([^|]+)/', $notes, $m) === 1) {
            return trim($m[1]);
        }

        return '';
    }
}
