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

## Comandos

- `make up` / `make down` / `make logs` / `make shell`
- `make db-import` — reimporta schema (cuidado: dados)
- `make serve` — PHP built-in sem MySQL (`-t public`)

## Estrutura

- `public/` — document root (HTML + front controller)
- `src/<Context>/{Domain,Application,Infrastructure}`
- `templates/admin/` — painel
- `database/docker/01_schema.sql` — schema + seed

Deploy Hostinger: aponte o document root para `public/` (ou publique o conteúdo de `public/` em `public_html` e mantenha `src/`, `vendor/`, `.env` um nível acima).

## Planos mensal / anual

O toggle do site calcula anual como **10× o mensal** (2 meses grátis) e chama `/checkout?plan={slug}&billing=annual|monthly`.

Hoje o checkout Kirvano usa a URL mensal do plano por slug. URLs anuais dedicadas podem ser adicionadas depois como plans no banco (`billing_cycle=annual`) sem mudar o front.

## Integrações (mock vs Kirvano)

- **Pagamento:** `PAYMENT_GATEWAY=kirvano` (default) redireciona para a URL de checkout do plano; `PAYMENT_GATEWAY=mock` devolve `/assinatura?...&mock_checkout=1` sem sair do app.
- **Envio / notificação:** sempre Mock neste build — tracking `MOCK-TRACK-…` e mensagens em `notification_outbox`.
- **Webhook:** `POST /webhooks/kirvano` — ingest + audit (`webhook_events` / `activity_logs`). Segredo opcional: `KIRVANO_WEBHOOK_SECRET` (headers `X-Kirvano-Secret` ou `X-Webhook-Secret`).
- Detalhes e payloads de exemplo: [`content/integrations-mock.md`](content/integrations-mock.md).

## LGPD / termos

- `/privacidade` e `/termos` — páginas públicas; links no footer e no consentimento do quiz.

## Backup (mysqldump)

Com o stack Docker no ar:

```bash
docker compose exec -T db mysqldump -uclubinho -pclubinho clubinhopet > backup-$(date +%Y%m%d).sql
```

Os dados do MySQL ficam no volume Docker do serviço `db` (não no working tree). Um `make down` sem `-v` preserva o volume; `docker compose down -v` apaga dados.

