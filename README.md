# Clubinho Pet Box

PHP puro com DDD (bounded contexts) + Docker Compose.

## Subir

```bash
cp .env.example .env   # se ainda não tiver .env
make up
```

Site: http://localhost:8080  
Admin: http://localhost:8080/admin/login  

Defina `APP_URL` no `.env` (ex.: `https://seudominio.com`) — usado para URLs absolutas em `/sitemap.xml` e deve coincidir com a linha `Sitemap:` em `public/robots.txt`.

Credenciais locais (seed):

- Email: `admin@clubinhopetbox.com.br` (ou usuário `admin` / `AsmoD`)
- Senha: `Carpe1936!`

## Visual

Design system **premium warm stone** (off-white + charcoal + accent coral muted): Cormorant Garamond + DM Sans. Tokens compartilhados em `public/assets/css/tokens.css` (site `style.css`, admin `admin.css`).

## Comandos

- `make up` / `make down` / `make logs` / `make shell`
- `make db-import` — reimporta schema (cuidado: dados)
- `make serve` — PHP built-in sem MySQL (`-t public`)
- `make backup` — dump gzip em `storage/backups/` via `scripts/backup.sh`
- `make test` — Pest (HTTP smoke + unit; stack Docker precisa estar no ar)
- `make test-e2e` — Playwright (Chromium) nos fluxos de UI críticos

## Estrutura

- `public/` — document root (HTML + front controller)
- `src/<Context>/{Domain,Application,Infrastructure}`
- `templates/admin/` — painel
- `database/docker/01_schema.sql` — schema + seed

Deploy Hostinger: aponte o document root para `public/` (ou publique o conteúdo de `public/` em `public_html` e mantenha `src/`, `vendor/`, `.env` um nível acima).

## Planos mensal / anual

O toggle do site calcula anual como **10× o mensal** (2 meses grátis) e chama `/checkout?plan={slug}&billing=annual|monthly`.

Hoje o checkout Kirvano usa a URL mensal do plano por slug. URLs anuais dedicadas podem ser adicionadas depois como plans no banco (`billing_cycle=annual`) sem mudar o front.

## Endurecimento (CSRF / LGPD / Quiz / Regras)

- **CSRF:** token de sessão em forms HTML (`_csrf`) e header `X-CSRF-Token` nas APIs same-origin; `GET /api/v1/csrf`.
- **DSAR:** em `/admin/customers/{id}` — Exportar JSON e Apagar (`confirm=EXCLUIR`).
- **Quiz v3:** campos ambiente, materiais e tamanho de brinquedo; motor `recommendation_rule_sets` (`rules-v1`).
- **Upload:** `/enviar-foto` → `happy_media` pending → moderação no admin.
- Migration: `database/migrations/011_hardening_rules_quiz_media.sql`

## Integrações (mock vs Kirvano)

- **Pagamento:** `PAYMENT_GATEWAY=mock` (padrão no `.env.example`) devolve `/assinatura?...&mock_checkout=1&subscription_id=…` com UI PIX/cartão; `POST /api/v1/payments/mock/confirm` grava `payments` (`provider=mock`), ativa a assinatura e dispara outbox. `PAYMENT_GATEWAY=kirvano` redireciona para a URL de checkout do plano.
- **Envio:** MockCarrier — tracking `MOCK-TRACK-…`, timeline em `shipment_events`. No admin, **Avançar rastreio** (`POST /admin/boxes/advance`) avança um passo até `delivered`.
- **Notificação:** `MockNotifier` grava em `notification_outbox` (e-mail / WhatsApp / log). Painel: `/admin/outbox` (perm `outbox`).
- **Webhook:** `POST /webhooks/kirvano` — ingest + audit (`webhook_events` / `activity_logs`). Segredo opcional: `KIRVANO_WEBHOOK_SECRET` (headers `X-Kirvano-Secret` ou `X-Webhook-Secret`).
- Detalhes e payloads de exemplo: [`content/integrations-mock.md`](content/integrations-mock.md).

## Core operacional (admin)

Fluxo: **quiz → recomendação → aprovar → Gerar box** (reserva estoque em `stock_movements`) → **ship** (converte reserva) → **avançar rastreio** (timeline) → entregue.

- CRM: `/admin/customers`, `/admin/pets`
- Usuários: `/admin/users` (CRUD de roles; só `admin`)
- Assinaturas: pause / cancel (confirm `CANCELAR`) / reativar em `/admin/subscriptions`
- Estoque: `/admin/stock` (confirm no ajuste + alertas `stock_min`)
- Pagamentos: resumo por período, busca e paginação em `/admin/payments`
- Outbox: `/admin/outbox`
- Pets Felizes: upload público foto **ou** vídeo em `/enviar-foto` → moderação
- Migrations: `009`–`012` (inclui role `financeiro`)

### Roles (senha local igual ao admin: `Carpe1936!`)

| Role | E-mail demo |
|------|-------------|
| admin | `admin@clubinhopetbox.com.br` |
| operacao | `operacao@clubinhopetbox.com.br` |
| montagem | `montagem@clubinhopetbox.com.br` |
| atendimento | `atendimento@clubinhopetbox.com.br` |
| estoque | `estoque@clubinhopetbox.com.br` |
| financeiro | `financeiro@clubinhopetbox.com.br` |

Financeiro acessa dashboard, pagamentos, assinaturas e auditoria. Settings e CRUD de usuários são restritos a **admin**. Outbox: admin, operação e atendimento.

## LGPD / termos

- `/privacidade` e `/termos` — páginas públicas; links no footer e no consentimento do quiz.

## Backup (mysqldump)

Com o stack Docker no ar:

```bash
make backup
# ou: bash scripts/backup.sh
```

O script usa `mysqldump --single-transaction --routines --triggers --no-tablespaces`, compacta em `storage/backups/clubinhopet-YYYYMMDD-HHMMSS.sql.gz` e mantém os `BACKUP_KEEP` (default 14) dumps mais novos. Override: `BACKUP_DIR`, `BACKUP_KEEP`.

Manual (sem rotação):

```bash
docker compose exec -T db mysqldump -uclubinho -pclubinho --no-tablespaces clubinhopet > backup-$(date +%Y%m%d).sql
```

Os dados do MySQL ficam no volume Docker do serviço `db` (não no working tree). Um `make down` sem `-v` preserva o volume; `docker compose down -v` apaga dados.
