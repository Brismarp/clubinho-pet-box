<?php

declare(strict_types=1);

namespace ClubinhoPet\Crm\Application;

use ClubinhoPet\Shared\Domain\DomainException;
use PDO;

final class ExportCustomerDataHandler
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return array<string, mixed> */
    public function handle(int $customerId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM customers WHERE id = ?');
        $stmt->execute([$customerId]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($customer === false) {
            throw new DomainException('Customer not found.');
        }

        $email = (string) $customer['email'];
        $phone = trim((string) ($customer['phone'] ?? ''));

        $pets = $this->pdo->prepare('SELECT * FROM pets WHERE customer_id = ? ORDER BY id ASC');
        $pets->execute([$customerId]);

        $subs = $this->pdo->prepare('SELECT * FROM subscriptions WHERE email = ? ORDER BY id ASC');
        $subs->execute([$email]);

        $payments = $this->pdo->prepare(
            'SELECT id, status, amount_cents, provider, external_id, paid_at
             FROM payments WHERE customer_email = ? ORDER BY id ASC'
        );
        $payments->execute([$email]);

        $sessionsStmt = $this->pdo->prepare(
            'SELECT * FROM quiz_sessions WHERE customer_id = ? ORDER BY id ASC'
        );
        $sessionsStmt->execute([$customerId]);
        $sessions = $sessionsStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $quizSessions = [];
        foreach ($sessions as $session) {
            $sid = (int) $session['id'];
            $answers = $this->pdo->prepare(
                'SELECT id, step_key, question_key, value_json, created_at, updated_at
                 FROM quiz_answers WHERE session_id = ? ORDER BY id ASC'
            );
            $answers->execute([$sid]);
            $attrs = $this->pdo->prepare(
                'SELECT id, attr_key, attr_value, created_at
                 FROM quiz_attributes WHERE session_id = ? ORDER BY id ASC'
            );
            $attrs->execute([$sid]);
            $quizSessions[] = [
                ...$session,
                'answers' => $answers->fetchAll(PDO::FETCH_ASSOC) ?: [],
                'attributes' => $attrs->fetchAll(PDO::FETCH_ASSOC) ?: [],
            ];
        }

        $boxes = $this->pdo->prepare(
            'SELECT b.* FROM boxes b
             INNER JOIN subscriptions s ON s.id = b.subscription_id
             WHERE s.email = ?
             ORDER BY b.id ASC'
        );
        $boxes->execute([$email]);

        $leads = $this->pdo->prepare('SELECT * FROM leads WHERE email = ? ORDER BY id ASC');
        $leads->execute([$email]);

        $contacts = $this->pdo->prepare(
            'SELECT * FROM contact_messages WHERE email = ? ORDER BY id ASC'
        );
        $contacts->execute([$email]);

        $outboxSql = 'SELECT id, channel, recipient, template_key, status, created_at, sent_at
                      FROM notification_outbox WHERE recipient = ?';
        $outboxParams = [$email];
        if ($phone !== '') {
            $outboxSql .= ' OR recipient = ?';
            $outboxParams[] = $phone;
            $digits = preg_replace('/\D+/', '', $phone) ?? '';
            if (strlen($digits) >= 8) {
                $outboxSql .= ' OR REPLACE(REPLACE(REPLACE(REPLACE(recipient, \' \', \'\'), \'-\', \'\'), \'(\', \'\'), \')\', \'\') LIKE ?';
                $outboxParams[] = '%' . $digits . '%';
            }
        }
        $outboxSql .= ' ORDER BY id ASC';
        $outbox = $this->pdo->prepare($outboxSql);
        $outbox->execute($outboxParams);

        return [
            'exported_at' => (new \DateTimeImmutable('now'))->format(\DateTimeInterface::ATOM),
            'customer' => $customer,
            'pets' => $pets->fetchAll(PDO::FETCH_ASSOC) ?: [],
            'subscriptions' => $subs->fetchAll(PDO::FETCH_ASSOC) ?: [],
            'payments' => $payments->fetchAll(PDO::FETCH_ASSOC) ?: [],
            'quiz_sessions' => $quizSessions,
            'boxes' => $boxes->fetchAll(PDO::FETCH_ASSOC) ?: [],
            'leads' => $leads->fetchAll(PDO::FETCH_ASSOC) ?: [],
            'contact_messages' => $contacts->fetchAll(PDO::FETCH_ASSOC) ?: [],
            'notification_outbox' => $outbox->fetchAll(PDO::FETCH_ASSOC) ?: [],
        ];
    }
}
