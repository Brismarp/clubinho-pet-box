<?php

declare(strict_types=1);

namespace ClubinhoPet\Crm\Application;

use ClubinhoPet\Platform\Domain\ActivityLogRepository;
use ClubinhoPet\Shared\Domain\DomainException;
use PDO;

final class EraseCustomerDataHandler
{
    public function __construct(
        private PDO $pdo,
        private ?ActivityLogRepository $logs = null,
    ) {
    }

    /** @return array{ok: bool, customer_id: int} */
    public function handle(int $customerId, string $confirmPhrase, ?int $actorUserId): array
    {
        if ($confirmPhrase !== 'EXCLUIR') {
            throw new DomainException('Confirmação inválida. Digite EXCLUIR para confirmar.');
        }

        $stmt = $this->pdo->prepare('SELECT * FROM customers WHERE id = ?');
        $stmt->execute([$customerId]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($customer === false) {
            throw new DomainException('Customer not found.');
        }

        $email = (string) $customer['email'];
        $phone = trim((string) ($customer['phone'] ?? ''));
        $redactedCustomerEmail = 'deleted-' . $customerId . '@redacted.local';

        $this->pdo->beginTransaction();
        try {
            $sessionIdsStmt = $this->pdo->prepare('SELECT id FROM quiz_sessions WHERE customer_id = ?');
            $sessionIdsStmt->execute([$customerId]);
            $sessionIds = array_map('intval', $sessionIdsStmt->fetchAll(PDO::FETCH_COLUMN) ?: []);

            if ($sessionIds !== []) {
                $placeholders = implode(',', array_fill(0, count($sessionIds), '?'));
                $this->pdo->prepare("DELETE FROM quiz_answers WHERE session_id IN ($placeholders)")
                    ->execute($sessionIds);
                $this->pdo->prepare("DELETE FROM quiz_attributes WHERE session_id IN ($placeholders)")
                    ->execute($sessionIds);
                $this->pdo->prepare("DELETE FROM quiz_sessions WHERE id IN ($placeholders)")
                    ->execute($sessionIds);
            }

            $this->pdo->prepare(
                'UPDATE pets
                 SET name = \'Redacted\',
                     dietary_restrictions = NULL,
                     health_notes = NULL,
                     photo_url = NULL,
                     customer_id = NULL
                 WHERE customer_id = ?'
            )->execute([$customerId]);

            $subs = $this->pdo->prepare('SELECT id FROM subscriptions WHERE email = ?');
            $subs->execute([$email]);
            foreach ($subs->fetchAll(PDO::FETCH_ASSOC) ?: [] as $sub) {
                $sid = (int) $sub['id'];
                $this->pdo->prepare(
                    'UPDATE subscriptions
                     SET email = ?,
                         pet_name = NULL,
                         pet_type = NULL,
                         pet_age = NULL,
                         notes = NULL,
                         status = \'cancelled\'
                     WHERE id = ?'
                )->execute(['deleted+' . $sid . '@redacted.local', $sid]);
            }

            $this->pdo->prepare(
                'UPDATE payments SET customer_email = ? WHERE customer_email = ?'
            )->execute([$redactedCustomerEmail, $email]);

            $this->pdo->prepare('DELETE FROM leads WHERE email = ?')->execute([$email]);
            $this->pdo->prepare('DELETE FROM contact_messages WHERE email = ?')->execute([$email]);

            $this->pdo->prepare(
                'UPDATE notification_outbox SET recipient = ? WHERE recipient = ?'
            )->execute([$redactedCustomerEmail, $email]);
            if ($phone !== '') {
                $this->pdo->prepare(
                    'UPDATE notification_outbox SET recipient = ? WHERE recipient = ?'
                )->execute([$redactedCustomerEmail, $phone]);
            }

            if ($this->hasAnalyticsEmailColumn()) {
                $this->pdo->prepare('DELETE FROM analytics_events WHERE email = ?')->execute([$email]);
            }

            $this->pdo->prepare(
                'UPDATE customers
                 SET name = \'Redacted\',
                     email = ?,
                     phone = NULL,
                     address_line = NULL,
                     city = NULL,
                     state = NULL,
                     notes = NULL,
                     consent_marketing = 0,
                     consent_lgpd_at = NULL
                 WHERE id = ?'
            )->execute([$redactedCustomerEmail, $customerId]);

            $this->logs?->log(
                $actorUserId,
                'dsar.erase',
                'customers',
                $customerId,
                ['email' => $email],
                ['ok' => true, 'customer_id' => $customerId],
            );

            $this->pdo->commit();
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }

        return ['ok' => true, 'customer_id' => $customerId];
    }

    private function hasAnalyticsEmailColumn(): bool
    {
        try {
            $stmt = $this->pdo->query("SHOW COLUMNS FROM analytics_events LIKE 'email'");

            return $stmt !== false && $stmt->fetch(PDO::FETCH_ASSOC) !== false;
        } catch (\Throwable) {
            return false;
        }
    }
}
