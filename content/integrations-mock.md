# Integrações mock (Onda C+D)

Ambiente de desenvolvimento usa mocks para envio e notificação. Pagamento público
continua redirecionando para Kirvano unless `PAYMENT_GATEWAY=mock`.

## Env

| Variável | Default | Uso |
|----------|---------|-----|
| `PAYMENT_GATEWAY` | `kirvano` | `kirvano` = redirect checkout do plano; `mock` = URL local com `mock_checkout=1` |
| `INTEGRATIONS_MODE` | `mock` | Reserva para futuros provedores reais |
| `KIRVANO_WEBHOOK_SECRET` | vazio | Se setado, exige header `X-Kirvano-Secret` ou `X-Webhook-Secret` |

## Webhook Kirvano

`POST /webhooks/kirvano` — aceita JSON, grava em `webhook_events` + `activity_logs`, responde `{ "ok": true, "event_id": N, "status": "..." }`.

Atualização de assinatura é best-effort: se vier `external_id` / `subscription_id` / `data.id` e status mapeável (`paid`/`active` → active, etc.), atualiza `subscriptions`.

### Exemplo — pagamento aprovado

```bash
curl -sS -X POST http://localhost:8080/webhooks/kirvano \
  -H 'Content-Type: application/json' \
  -H 'X-Kirvano-Secret: SEU_SECRET' \
  -d '{
    "event": "payment.approved",
    "external_id": "sub_demo_001",
    "status": "paid",
    "email": "cliente@example.com"
  }'
```

### Exemplo — evento ignorado (sem id)

```json
{
  "event": "ping",
  "message": "healthcheck"
}
```

Status do evento: `processed` | `ignored` | `error`.

## Notifier mock

`MockNotifier` grava em `notification_outbox` com `status=sent_mock` (sem e-mail/WhatsApp real). Disparado em quiz complete, ship box e aprovação de recomendação.

## Shipment mock

`MockShipmentProvider` gera `MOCK-TRACK-{BOXID}-{HEX}` quando o admin envia a box sem tracking.

## Auditoria

Painel leve: `GET /admin/audit` (últimos logs de `activity_logs`).
