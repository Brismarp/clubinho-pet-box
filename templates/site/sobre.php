<?php

declare(strict_types=1);

$activeNav = 'sobre';
$pageTitle = 'Sobre | Clubinho Pet Box';
$metaDescription = 'História, missão e valores do Clubinho Pet Box.';
$canonicalPath = '/sobre';
require BASE_PATH . '/templates/site/header.php';
?>
<section class="page-hero">
    <div class="container reveal">
        <p class="text-uppercase text-gold">Nossa história</p>
        <h1>Amor, cuidado e qualidade para o seu pet</h1>
        <p class="lead">Nascemos da paixão por pets e do desejo de experiências memoráveis.</p>
    </div>
</section>
<section class="section bg-white">
    <div class="container">
        <div class="about-content">
            <div class="about-text reveal">
                <p class="text-uppercase text-gold">Quem somos</p>
                <h2>A história do Clubinho Pet Box</h2>
                <p class="lead">Nasceu do amor pelos pets e da vontade de transformar o cuidado do dia a dia.</p>
                <p>Vimos tutores buscando produtos confiáveis e adequados. Criamos uma curadoria premium que entrega, todo mês, itens escolhidos com critério.</p>
                <p>Cada box é pensada do produto à embalagem — marcas confiáveis, itens testados, foco em segurança, diversão e bem-estar.</p>
            </div>
            <div class="reveal">
                <div class="media-frame media-frame--portrait">
                    <img src="/assets/images/boxes/box-love.jpg" alt="Clubinho Pet Box" class="media-cover" loading="lazy" width="720" height="900">
                </div>
            </div>
        </div>
    </div>
</section>
<section class="section bg-cream">
    <div class="container">
        <header class="section-head reveal">
            <p class="text-uppercase text-gold">Princípios</p>
            <h2>O que nos move</h2>
        </header>
        <div class="grid grid-3 reveal-stagger">
            <article class="step reveal reveal--scale"><h3 class="text-gold">Missão</h3><p>Experiências premium para pets e tutores, com curadoria e entrega cuidadas.</p></article>
            <article class="step reveal reveal--scale"><h3 class="text-gold">Visão</h3><p>Ser referência em assinatura premium para pets no Brasil.</p></article>
            <article class="step reveal reveal--scale"><h3 class="text-gold">Valores</h3><ul class="card-features"><li>Respeito pelos animais</li><li>Qualidade sem concessões</li><li>Transparência</li><li>Sustentabilidade</li></ul></article>
        </div>
    </div>
</section>
<section class="cta-band">
    <div class="container-narrow reveal">
        <p class="text-uppercase">Venha conosco</p>
        <h2>Conheça os planos</h2>
        <p class="lead">Encontre a box certa para o perfil do seu pet.</p>
        <a href="/planos" class="btn btn-gold">Ver planos</a>
    </div>
</section>
<?php require BASE_PATH . '/templates/site/footer.php'; ?>
