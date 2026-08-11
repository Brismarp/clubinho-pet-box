<?php

declare(strict_types=1);

namespace ClubinhoPet\Integrations\Infrastructure;

use ClubinhoPet\Integrations\Domain\Notifier;
use PDO;

final class MockNotifier implements Notifier
{
    public function __construct(private PDO $pdo)
    {
    }

    public function send(string $channel, string $to, string $templateKey, array $payload = []): void
    {
        $allowed = ['email', 'whatsapp', 'log'];
        if (!in_array($channel, $allowed, true)) {
            $channel = 'log';
        }

        $rendered = NotificationTemplates::render($templateKey, $payload);
        $enriched = $payload;
        $enriched['_rendered'] = $rendered;
        $enriched['_channel'] = $channel;

        $stmt = $this->pdo->prepare(
            'INSERT INTO notification_outbox (channel, recipient, template_key, payload_json, status, sent_at)
             VALUES (?, ?, ?, ?, \'sent_mock\', NOW())'
        );
        $stmt->execute([
            $channel,
            $to,
            $templateKey,
            json_encode($enriched, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        ]);
    }
}
