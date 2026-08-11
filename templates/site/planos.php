<?php

declare(strict_types=1);

$activeNav = 'planos';
$pageTitle = 'Planos | Clubinho Pet Box';
$metaDescription = 'Planos Happy, Love e Deluxe do Clubinho Pet Box — mensal ou anual.';
$canonicalPath = '/planos';
require BASE_PATH . '/templates/site/header.php';
?>
<section class="page-hero">
    <div class="container reveal">
        <p class="text-uppercase text-gold">Assinatura premium</p>
        <h1>Escolha seu plano</h1>
        <p class="lead">Três boxes curadas — mensal ou anual com 2 meses grátis.</p>
    </div>
</section>
<section class="section bg-white" id="planos">
    <div class="container">
        <div class="billing-toggle-wrap reveal">
            <div class="billing-toggle" data-billing-toggle role="group" aria-label="Ciclo de cobrança">
                <button type="button" class="billing-toggle__btn is-active" data-billing-value="monthly" aria-pressed="true">Mensal</button>
                <button type="button" class="billing-toggle__btn" data-billing-value="annual" aria-pressed="false">Anual</button>
            </div>
            <p class="billing-toggle__badge">Anual: 2 meses grátis</p>
        </div>
        <div class="plans-grid reveal-stagger">
            <article class="plan-card reveal reveal--up" data-slug="happy" data-price-monthly="101.47" data-price-annual="1014.70" data-billing="monthly">
                <img src="/assets/images/boxes/box-happy.jpg" alt="Box Happy" class="card-image" loading="lazy" width="640" height="480">
                <div class="card-content">
                    <h3 class="card-title">Clubinho Happy</h3>
                    <p class="card-price"><span data-plan-price>R$ 101,47</span> <span class="card-price-period" data-plan-period>/mês</span></p>
                    <p class="card-price-equiv" data-plan-equiv hidden></p>
                    <p class="lead" style="font-size: var(--text-sm); margin: 0;">Box essencial com produtos selecionados.</p>
                    <ul class="card-features">
                        <li>4 a 6 produtos essenciais</li>
                        <li>Brinquedos divertidos</li>
                        <li>Petiscos saborosos</li>
                        <li>Produtos de higiene</li>
                    </ul>
                    <a href="/checkout?plan=happy&billing=monthly" class="btn btn-secondary" data-plan-cta>Assinar Happy</a>
                </div>
            </article>
            <article class="plan-card featured reveal reveal--up" data-slug="love" data-price-monthly="164.97" data-price-annual="1649.70" data-billing="monthly">
                <img src="/assets/images/boxes/box-love.jpg" alt="Box Love" class="card-image" loading="lazy" width="640" height="480">
                <div class="card-content">
                    <h3 class="card-title">Clubinho Love</h3>
                    <p class="card-price"><span data-plan-price>R$ 164,97</span> <span class="card-price-period" data-plan-period>/mês</span></p>
                    <p class="card-price-equiv" data-plan-equiv hidden></p>
                    <p class="lead" style="font-size: var(--text-sm); margin: 0;">Box premium com marcas renomadas.</p>
                    <ul class="card-features">
                        <li>6 a 8 produtos exclusivos</li>
                        <li>Brinquedos interativos</li>
                        <li>Petiscos premium</li>
                        <li>Marcas renomadas</li>
                    </ul>
                    <a href="/checkout?plan=love&billing=monthly" class="btn btn-gold" data-plan-cta>Assinar Love</a>
                </div>
            </article>
            <article class="plan-card reveal reveal--up" data-slug="deluxe" data-price-monthly="253.87" data-price-annual="2538.70" data-billing="monthly">
                <img src="/assets/images/boxes/box-deluxe.jpg" alt="Box Deluxe" class="card-image" loading="lazy" width="640" height="480">
                <div class="card-content">
                    <h3 class="card-title">Clubinho Deluxe</h3>
                    <p class="card-price"><span data-plan-price>R$ 253,87</span> <span class="card-price-period" data-plan-period>/mês</span></p>
                    <p class="card-price-equiv" data-plan-equiv hidden></p>
                    <p class="lead" style="font-size: var(--text-sm); margin: 0;">O que há de melhor em cada entrega.</p>
                    <ul class="card-features">
                        <li>8 a 10 produtos de luxo</li>
                        <li>Brinquedos importados</li>
                        <li>Petiscos gourmet</li>
                        <li>Produtos naturais</li>
                    </ul>
                    <a href="/checkout?plan=deluxe&billing=monthly" class="btn btn-secondary" data-plan-cta>Assinar Deluxe</a>
                </div>
            </article>
        </div>
    </div>
</section>
<section class="cta-band">
    <div class="container-narrow reveal">
        <p class="text-uppercase">Não sabe por onde começar?</p>
        <h2>Faça o quiz personalizado</h2>
        <p class="lead">Em poucos minutos indicamos a box ideal para o seu pet.</p>
        <a href="/quiz" class="btn btn-gold">Começar quiz</a>
    </div>
</section>
<script src="/assets/js/billing-toggle.js" defer></script>
<?php require BASE_PATH . '/templates/site/footer.php'; ?>
