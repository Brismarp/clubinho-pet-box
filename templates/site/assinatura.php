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
        <p class="lead">Preencha o formulário e siga para o checkout seguro.</p>
    </div>
</section>
<section class="section bg-white">
    <div class="container">
        <div class="form-panel reveal">
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
});
</script>
<?php require BASE_PATH . '/templates/site/footer.php'; ?>
