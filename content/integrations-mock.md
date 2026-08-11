# Integrações mock (pagamento, envio, outbox, backup)

Ambiente de desenvolvimento: pagamento in-app (quando `PAYMENT_GATEWAY=mock`), envio MockCarrier com timeline, notificações só no outbox.

## Env

| Variável | Default (`.env.example`) | Uso |
|----------|--------------------------|-----|
| `PAYMENT_GATEWAY` | `mock` | `mock` = checkout local com `mock_checkout=1`; `kirvano` = redirect para URL do plano |
| `INTEGRATIONS_MODE` | `mock` | Reserva para futuros provedores reais |
| `KIRVANO_WEBHOOK_SECRET` | vazio | Se setado, exige header `X-Kirvano-Secret` ou `X-Webhook-Secret` |
| `BACKUP_DIR` | `storage/backups` | Destino do `make backup` |
| `BACKUP_KEEP` | `14` | Quantos dumps gzip preservar |

## Pagamento mock (PIX / cartão)

1. `POST /subscribe` cria assinatura `pending` e redireciona para  
   `/assinatura?plan=…&billing=…&mock_checkout=1&subscription_id=N` (via `MockPaymentGateway`).
2. A página mostra `#mock-pay-panel` (abas PIX / cartão).
3. Confirmação: `POST /api/v1/payments/mock/confirm` com JSON:

```json
{
  "subscription_id": 1,
  "method": "pix",
  "card_holder": "opcional se method=card"
}
```

Resposta `201`: `payment_id`, `status=paid`, `external_id`, `amount_cents`, e `pix_code` (PIX) ou `card_brand` (cartão).

Efeitos: linha em `payments` (`provider=mock`), assinatura → `active`, audit `payment.mock.paid`, outbox `payment.confirmed` (e-mail + WhatsApp).

### Exemplo curl

```bash
curl -sS -X POST http://localhost:8080/api/v1/payments/mock/confirm \
  -H 'Content-Type: application/json' \
  -d '{"subscription_id":1,"method":"pix"}'
```

## Timeline de envio (MockCarrier)

Pipeline em `MockShipmentProvider::PIPELINE`:

`label_created` → `collected` → `in_transit` → `out_for_delivery` → `delivered`

- **Ship** (`POST /admin/boxes/ship`): gera `MOCK-TRACK-{BOXID}-{HEX}` se tracking vazio e semente os 2 primeiros eventos em `shipment_events`.
- **Avançar** (`POST /admin/boxes/advance` ou `POST /api/v1/admin/boxes/advance`): um passo; no fim marca box `delivered` e notifica `shipment.update` / `shipment.delivered`.
- Admin `/admin/boxes` lista a timeline por box e flash `?advanced=1`.

## Outbox (e-mail / WhatsApp mock)

`MockNotifier` grava em `notification_outbox` com `status=sent_mock` e `_rendered` (assunto/corpo) em `payload_json`. Templates: `NotificationTemplates` (`payment.confirmed`, `box.shipped`, `shipment.update`, `shipment.delivered`, quiz/recomendação, etc.).

Painel: `GET /admin/outbox?channel=all|email|whatsapp|log` — permissão `outbox` (Admin, Operacao, Atendimento).

## Webhook Kirvano

`POST /webhooks/kirvano` — aceita JSON, grava em `webhook_events` + `activity_logs`, responde `{ "ok": true, "event_id": N, "status": "..." }`.

Atualização de assinatura é best-effort: se vier `external_id` / `subscription_id` / `data.id` e status mapeável (`paid`/`active` → active, etc.), atualiza `subscriptions` e pode upsert em `payments`.

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

Status do evento: `processed` | `ignored` | `error`.

## Backup

```bash
make backup
# scripts/backup.sh → storage/backups/clubinhopet-*.sql.gz
```

Flags do dump: `--single-transaction --routines --triggers --no-tablespaces`. Requer serviço `db` no ar (`make up`).

## Auditoria

Painel leve: `GET /admin/audit` (últimos logs de `activity_logs`), incluindo `payment.mock.paid` e `shipment.advance`.
