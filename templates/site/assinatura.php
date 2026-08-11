<?php

declare(strict_types=1);

$activeNav = 'assinatura';
$pageTitle = 'Assinatura | Clubinho Pet Box';
$metaDescription = 'Complete seus dados e siga para o checkout Clubinho Pet Box.';
$canonicalPath = '/assinatura';
require BASE_PATH . '/templates/site/header.php';
?>
<section class="page-hero">
    <div class="container reveal">
        <p class="text-uppercase text-gold">Assinatura</p>
        <h1>Complete seus dados</h1>
        <p class="lead">Preencha o formulário e siga para o checkout seguro — ou pague aqui no mock, com carinho de Clubinho.</p>
    </div>
</section>

<section class="section bg-white">
    <div class="container">
        <div id="mock-pay-panel" class="form-panel mock-pay-panel reveal" hidden>
            <p class="eyebrow text-gold">Checkout mock</p>
            <h2>Quase lá — escolha como pagar</h2>
            <p class="lead" style="font-size:var(--text-sm);margin-top:0">
                Assinatura #<strong id="mock-sub-id-label"></strong> · ambiente de desenvolvimento (sem cobrança real).
            </p>

            <div class="mock-pay-methods" role="tablist" aria-label="Método de pagamento">
                <button type="button" class="btn btn-secondary is-active" data-mock-method="pix" role="tab" aria-selected="true">PIX</button>
                <button type="button" class="btn btn-secondary" data-mock-method="card" role="tab" aria-selected="false">Cartão</button>
            </div>

            <div id="mock-pix-pane" class="stack" data-mock-pane="pix">
                <p class="muted">Gere um código PIX fictício e confirme o pagamento na hora.</p>
                <button type="button" class="btn btn-primary" id="mock-pay-pix">Pagar com PIX (mock)</button>
                <pre class="mock-pix-code" id="mock-pix-code" hidden></pre>
            </div>

            <div id="mock-card-pane" class="stack" data-mock-pane="card" hidden>
                <div class="form-group">
                    <label for="mock-card-holder">Nome no cartão</label>
                    <input type="text" id="mock-card-holder" name="card_holder" autocomplete="cc-name" placeholder="Como no cartão">
                </div>
                <div class="form-group">
                    <label for="mock-card-number">Número (só visual)</label>
                    <input type="text" id="mock-card-number" inputmode="numeric" placeholder="4111 1111 1111 1111" maxlength="19">
                </div>
                <p class="muted">Os dados do cartão não vão para o servidor — só o nome (opcional) via JSON.</p>
                <button type="button" class="btn btn-primary" id="mock-pay-card">Confirmar cartão (mock)</button>
            </div>

            <div id="mock-pay-result" class="mock-pay-result" hidden role="status" aria-live="polite"></div>
        </div>

        <div class="form-panel reveal" id="subscription-panel">
            <form id="subscription-form" method="POST" action="/subscribe" class="stack">
                <div class="form-group">
                    <label for="plan">Plano</label>
                    <select id="plan" name="plan" required>
                        <option value="">Escolha um plano</option>
                        <option value="happy">Happy</option>
                        <option value="love">Love</option>
                        <option value="deluxe">Deluxe</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="billing">Ciclo</label>
                    <select id="billing" name="billing" data-billing-select>
                        <option value="monthly">Mensal</option>
                        <option value="annual">Anual (2 meses grátis)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="name">Nome completo</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="phone">Telefone</label>
                    <input type="tel" id="phone" name="phone">
                </div>
                <h3>Dados do pet</h3>
                <div class="form-group">
                    <label for="pet_name">Nome do pet</label>
                    <input type="text" id="pet_name" name="pet_name" required>
                </div>
                <div class="form-group">
                    <label for="pet_type">Tipo</label>
                    <select id="pet_type" name="pet_type" required>
                        <option value="">Escolha</option>
                        <option value="cachorro">Cachorro</option>
                        <option value="gato">Gato</option>
                        <option value="outro">Outro</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="pet_age">Idade (anos)</label>
                    <input type="number" id="pet_age" name="pet_age" min="0">
                </div>
                <div class="form-group">
                    <label for="pet_breed">Raça</label>
                    <input type="text" id="pet_breed" name="pet_breed">
                </div>
                <div class="form-group">
                    <label for="dietary_restrictions">Restrições alimentares</label>
                    <textarea id="dietary_restrictions" name="dietary_restrictions"></textarea>
                </div>
                <h3>Entrega</h3>
                <div class="form-group">
                    <label for="address">Endereço</label>
                    <input type="text" id="address" name="address">
                </div>
                <div class="form-group cluster">
                    <input type="checkbox" id="terms" name="terms" required style="width:auto">
                    <label for="terms" style="margin:0;font-weight:400">Concordo com os termos da assinatura</label>
                </div>
                <div class="cluster">
                    <button type="submit" class="btn btn-primary">Continuar para pagamento</button>
                    <a href="/planos" class="btn btn-secondary">Voltar aos planos</a>
                </div>
            </form>
        </div>
    </div>
</section>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const params = new URLSearchParams(location.search);
  const plan = params.get('plan');
  const billing = params.get('billing');
  const planSelect = document.getElementById('plan');
  const billingSelect = document.getElementById('billing');
  if (plan && planSelect) planSelect.value = plan;
  if (billing && billingSelect) billingSelect.value = billing;

  const subscriptionId = parseInt(params.get('subscription_id') || '', 10);
  const isMockCheckout = params.get('mock_checkout') === '1' && Number.isFinite(subscriptionId) && subscriptionId > 0;
  const panel = document.getElementById('mock-pay-panel');
  const formPanel = document.getElementById('subscription-panel');
  if (!isMockCheckout || !panel) return;

  panel.hidden = false;
  if (formPanel) formPanel.hidden = true;
  const label = document.getElementById('mock-sub-id-label');
  if (label) label.textContent = String(subscriptionId);

  let method = 'pix';
  const resultEl = document.getElementById('mock-pay-result');
  const pixCodeEl = document.getElementById('mock-pix-code');

  const setMethod = (next) => {
    method = next;
    document.querySelectorAll('[data-mock-method]').forEach((btn) => {
      const active = btn.getAttribute('data-mock-method') === next;
      btn.classList.toggle('is-active', active);
      btn.setAttribute('aria-selected', active ? 'true' : 'false');
    });
    document.querySelectorAll('[data-mock-pane]').forEach((pane) => {
      pane.hidden = pane.getAttribute('data-mock-pane') !== next;
    });
  };

  document.querySelectorAll('[data-mock-method]').forEach((btn) => {
    btn.addEventListener('click', () => setMethod(btn.getAttribute('data-mock-method') || 'pix'));
  });

  const showResult = (ok, html) => {
    if (!resultEl) return;
    resultEl.hidden = false;
    resultEl.classList.toggle('is-success', ok);
    resultEl.classList.toggle('is-error', !ok);
    resultEl.innerHTML = html;
  };

  const confirmPay = async (cardHolder) => {
    showResult(true, '<p class="muted" style="margin:0">Confirmando pagamento mock…</p>');
    try {
      const body = {
        subscription_id: subscriptionId,
        method,
      };
      if (method === 'card' && cardHolder) body.card_holder = cardHolder;

      const res = await fetch('/api/v1/payments/mock/confirm', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || window.__CSRF_TOKEN__
            || '',
        },
        body: JSON.stringify(body),
      });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) {
        throw new Error(data.error || data.detail || 'Falha no pagamento mock.');
      }

      if (data.pix_code && pixCodeEl) {
        pixCodeEl.hidden = false;
        pixCodeEl.textContent = data.pix_code;
      }

      const amount = ((data.amount_cents || 0) / 100).toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL',
      });
      showResult(
        true,
        `<p style="margin:0 0 .35rem"><strong>Pagamento confirmado!</strong> ${amount}</p>
         <p class="muted" style="margin:0">Protocolo <code>${data.external_id || ''}</code> · assinatura #${data.subscription_id} ativa.
         Confira notificações em <a href="/admin/outbox">/admin/outbox</a>.</p>`
      );
    } catch (err) {
      showResult(false, `<p style="margin:0">${err instanceof Error ? err.message : 'Erro inesperado.'}</p>`);
    }
  };

  document.getElementById('mock-pay-pix')?.addEventListener('click', () => {
    method = 'pix';
    confirmPay(null);
  });
  document.getElementById('mock-pay-card')?.addEventListener('click', () => {
    method = 'card';
    const holder = document.getElementById('mock-card-holder');
    confirmPay(holder ? holder.value.trim() : '');
  });
});
</script>
<?php require BASE_PATH . '/templates/site/footer.php'; ?>
