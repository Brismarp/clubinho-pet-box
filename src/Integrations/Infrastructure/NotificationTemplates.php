<?php

declare(strict_types=1);

namespace ClubinhoPet\Integrations\Infrastructure;

/** Subject/body drafts for mock transactional email & WhatsApp. */
final class NotificationTemplates
{
    /**
     * @param array<string, mixed> $payload
     * @return array{subject: string, body: string}
     */
    public static function render(string $templateKey, array $payload): array
    {
        return match ($templateKey) {
            'payment.confirmed' => [
                'subject' => 'Pagamento confirmado — Clubinho Pet Box',
                'body' => sprintf(
                    "Olá! Recebemos seu pagamento (%s) de R$ %s.\nAssinatura #%s está ativa%s.\nProtocolo: %s",
                    (string) ($payload['method'] ?? 'mock'),
                    number_format(((int) ($payload['amount_cents'] ?? 0)) / 100, 2, ',', '.'),
                    (string) ($payload['subscription_id'] ?? ''),
                    isset($payload['pet_name']) && $payload['pet_name'] !== ''
                        ? ' para ' . $payload['pet_name']
                        : '',
                    (string) ($payload['external_id'] ?? ''),
                ),
            ],
            'box.shipped' => [
                'subject' => 'Sua box saiu para entrega',
                'body' => sprintf(
                    "Box #%s enviada.\nTracking MockCarrier: %s\nAcompanhe a timeline no admin ou peça o status pelo WhatsApp.",
                    (string) ($payload['box_id'] ?? ''),
                    (string) ($payload['tracking_number'] ?? ''),
                ),
            ],
            'shipment.update' => [
                'subject' => 'Atualização de entrega — Clubinho',
                'body' => sprintf(
                    "Tracking %s: %s\n%s",
                    (string) ($payload['tracking_number'] ?? ''),
                    (string) (($payload['event']['description'] ?? $payload['status'] ?? '')),
                    isset($payload['pet_name']) ? 'Pet: ' . $payload['pet_name'] : '',
                ),
            ],
            'shipment.delivered' => [
                'subject' => 'Box entregue!',
                'body' => sprintf(
                    "Boa notícia: a box #%s foi marcada como entregue (tracking %s). Esperamos que %s adore!",
                    (string) ($payload['box_id'] ?? ''),
                    (string) ($payload['tracking_number'] ?? ''),
                    (string) ($payload['pet_name'] ?? 'seu pet'),
                ),
            ],
            'quiz.completed' => [
                'subject' => 'Quiz Clubinho concluído',
                'body' => 'Recebemos suas respostas. Em breve montamos a recomendação.',
            ],
            'recommendation.approved' => [
                'subject' => 'Recomendação aprovada',
                'body' => sprintf('A recomendação #%s foi aprovada pela curadoria.', (string) ($payload['recommendation_id'] ?? '')),
            ],
            default => [
                'subject' => 'Clubinho Pet Box — ' . $templateKey,
                'body' => json_encode($payload, JSON_UNESCAPED_UNICODE) ?: '',
            ],
        };
    }
}
