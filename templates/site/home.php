<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $testimonials */
/** @var list<array<string, mixed>> $happyMedia */
/** @var list<array<string, mixed>> $faqs */

$activeNav = 'home';
$pageTitle = 'Clubinho Pet Box | Assinatura Premium para Pets';
$metaDescription = 'Box escolhida de acordo com o perfil do seu pet. Quiz inteligente, planos Happy, Love e Deluxe.';
$canonicalPath = '/';
require BASE_PATH . '/templates/site/header.php';
?>

<section class="hero">
    <div class="hero-content">
        <div class="hero-text">
            <p class="hero-brand">Clubinho Pet <span>Box</span></p>
            <h1 class="hero-title">A box certa para o perfil do seu pet</h1>
            <p class="hero-description">
                Responda o quiz, a gente analisa e monta uma seleção pensada para a rotina, o porte e as preferências do seu dog — todo mês.
            </p>
            <div class="hero-actions">
                <a href="/quiz" class="btn btn-primary">Descobrir a box do meu pet</a>
                <a href="/planos" class="btn btn-secondary">Conhecer os planos</a>
            </div>
        </div>
        <div class="hero-media">
            <div class="media-frame">
                <img src="/assets/images/mood/q09.jpg" alt="Tutor com cão feliz" class="media-cover" fetchpriority="high" width="900" height="1125">
            </div>
        </div>
    </div>
</section>

<section class="section stats-band" id="numeros" aria-label="Números Clubinho">
    <div class="container">
        <div class="stats-grid reveal-stagger">
            <div class="stat-block reveal">
                <p class="stat-block__value"><span data-count-up data-target="12480" data-duration="1800">0</span></p>
                <p class="stat-block__label">Pets felizes na comunidade</p>
            </div>
            <div class="stat-block reveal">
                <p class="stat-block__value"><span data-count-up data-target="48320" data-duration="2000">0</span></p>
                <p class="stat-block__label">Boxes enviadas</p>
            </div>
            <div class="stat-block reveal">
                <p class="stat-block__value"><span data-count-up data-target="98" data-suffix="%" data-duration="1600">0</span></p>
                <p class="stat-block__label">Tutores satisfeitos</p>
            </div>
            <div class="stat-block reveal">
                <p class="stat-block__value"><span data-count-up data-target="4.8" data-decimals="1" data-suffix="/5" data-duration="1400">0</span></p>
                <p class="stat-block__label">Nota média dos depoimentos</p>
            </div>
        </div>
    </div>
</section>

<section class="section bg-white" id="como-funciona">
    <div class="container">
        <header class="section-head reveal">
            <p class="text-uppercase text-gold">Como funciona</p>
            <h2>Do quiz à caixa na porta</h2>
            <p class="lead">Quatro passos claros para uma box realmente adequada.</p>
        </header>
        <div class="steps reveal-stagger">
            <article class="step reveal reveal--scale">
                <div class="step-num">01</div>
                <h3>Responder o quiz</h3>
                <p>Contamos com perfil, rotina e preferências do pet e do tutor.</p>
            </article>
            <article class="step reveal reveal--scale">
                <div class="step-num">02</div>
                <h3>Perfil analisado</h3>
                <p>As respostas viram critérios para filtrar o que faz sentido — e o que evitar.</p>
            </article>
            <article class="step reveal reveal--scale">
                <div class="step-num">03</div>
                <h3>Montagem da box</h3>
                <p>A equipe (e o sistema) monta a seleção com produtos compatíveis.</p>
            </article>
            <article class="step reveal reveal--scale">
                <div class="step-num">04</div>
                <h3>Box enviada</h3>
                <p>Você recebe em casa e acompanha o ritual mês a mês.</p>
            </article>
        </div>
    </div>
</section>

<section class="section bg-cream" id="personalizado">
    <div class="container">
        <div class="about-content">
            <div class="about-text reveal">
                <p class="text-uppercase text-gold">Por que é personalizado</p>
                <h2>Menos chute, mais acerto</h2>
                <p class="lead">O quiz considera características, rotina, comportamento, alimentação e preferências — para reduzir escolhas inadequadas ao porte ou ao temperamento do pet.</p>
                <ul class="card-features">
                    <li>Bloqueio de combinações ruins (porte × brinquedo, restrições × petisco)</li>
                    <li>Curadoria alinhada ao nível de energia e ao que o tutor não quer receber</li>
                    <li>Espaço para revisão humana antes da expedição</li>
                </ul>
                <a href="/quiz" class="btn btn-primary mt-xl">Começar pelo quiz</a>
                <a href="#modal-lead" class="btn btn-ghost mt-xl" data-open-modal="modal-lead">Cadastrar perfil</a>
            </div>
            <div class="reveal">
                <div class="media-frame media-frame--portrait">
                    <img src="/assets/images/mood/q04.jpg" alt="Cão brincalhão" class="media-cover" loading="lazy" width="720" height="900">
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section bg-white" id="planos">
    <div class="container">
        <header class="section-head reveal">
            <p class="text-uppercase text-gold">Nossos planos</p>
            <h2>Escolha a box perfeita</h2>
            <p class="lead">Três níveis de curadoria, mensal ou anual.</p>
        </header>

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

<section class="section bg-cream" id="pets-felizes">
    <div class="container">
        <header class="section-head reveal">
            <p class="text-uppercase text-gold">Pets Felizes</p>
            <h2>Quem já recebeu a box</h2>
            <p class="lead">Momentos reais de tutores e pets com a curadoria Clubinho.</p>
        </header>
        <?php if ($happyMedia === []): ?>
            <p class="muted" style="text-align:center">Em breve novas fotos. Enquanto isso, faça o <a href="/quiz">quiz</a>.</p>
        <?php else: ?>
            <div class="happy-gallery reveal-stagger">
                <?php foreach ($happyMedia as $media):
                    $src = (string) ($media['media_path'] ?: $media['media_url'] ?: '');
                    if ($src !== '' && !str_starts_with($src, 'http') && !str_starts_with($src, '/')) {
                        $src = '/' . ltrim($src, '/');
                    }
                    $orient = (string) ($media['orientation'] ?? 'landscape');
                    ?>
                    <figure class="happy-item happy-item--<?= htmlspecialchars($orient, ENT_QUOTES, 'UTF-8') ?> reveal reveal--scale">
                        <?php if (($media['type'] ?? '') === 'video' && !empty($media['media_url'])): ?>
                            <div class="happy-item__media">
                                <iframe src="<?= htmlspecialchars((string) $media['media_url'], ENT_QUOTES, 'UTF-8') ?>" title="<?= htmlspecialchars((string) $media['pet_name'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy" allowfullscreen></iframe>
                            </div>
                        <?php else: ?>
                            <div class="happy-item__media">
                                <img src="<?= htmlspecialchars($src, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string) $media['pet_name'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy" width="640" height="800">
                            </div>
                        <?php endif; ?>
                        <figcaption>
                            <strong><?= htmlspecialchars((string) $media['pet_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <?php if (!empty($media['tutor_handle'])): ?>
                                <span><?= htmlspecialchars((string) $media['tutor_handle'], ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endif; ?>
                        </figcaption>
                    </figure>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section bg-white" id="depoimentos">
    <div class="container">
        <header class="section-head reveal">
            <p class="text-uppercase text-gold">Depoimentos</p>
            <h2>O que dizem os tutores</h2>
        </header>
        <?php if ($testimonials === []): ?>
            <p class="muted" style="text-align:center">Depoimentos em breve.</p>
        <?php else: ?>
            <?php
            $renderTestimonialCard = static function (array $t): void {
                $photo = (string) ($t['photo_path'] ?? '');
                if ($photo !== '' && !str_starts_with($photo, '/')) {
                    $photo = '/' . ltrim($photo, '/');
                }
                $name = (string) ($t['name'] ?? '');
                $initial = mb_strtoupper(mb_substr($name !== '' ? $name : '?', 0, 1));
                $pet = (string) ($t['pet_name'] ?? '');
                $handle = $pet !== '' ? '@' . preg_replace('/\s+/', '', mb_strtolower($pet)) : '';
                ?>
                <article class="testimonial-card">
                    <div class="testimonial-card__head">
                        <div class="testimonial-card__avatar" aria-hidden="true">
                            <?php if ($photo !== ''): ?>
                                <img src="<?= htmlspecialchars($photo, ENT_QUOTES, 'UTF-8') ?>" alt="" width="40" height="40" loading="lazy">
                            <?php else: ?>
                                <?= htmlspecialchars($initial, ENT_QUOTES, 'UTF-8') ?>
                            <?php endif; ?>
                        </div>
                        <div class="testimonial-card__meta">
                            <span class="testimonial-card__name"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></span>
                            <?php if ($handle !== ''): ?>
                                <span class="testimonial-card__handle"><?= htmlspecialchars($handle, ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <blockquote class="testimonial-card__quote"><?= htmlspecialchars((string) $t['quote'], ENT_QUOTES, 'UTF-8') ?></blockquote>
                </article>
                <?php
            };
            ?>
            <div class="testimonials-wall reveal" aria-label="Depoimentos de tutores">
                <div class="testimonials-wall__stage">
                    <div class="testimonials-wall__tilt">
                        <?php for ($col = 0; $col < 4; $col++): ?>
                            <div
                                class="marquee marquee--vertical"
                                data-pause-hover
                                <?= $col % 2 === 1 ? 'data-reverse' : '' ?>
                                role="marquee"
                                aria-label="Coluna de depoimentos <?= $col + 1 ?>"
                            >
                                <div class="marquee__track" data-marquee-track>
                                    <?php foreach ($testimonials as $t) {
                                        $renderTestimonialCard($t);
                                    } ?>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                    <div class="testimonials-wall__fade" aria-hidden="true"></div>
                </div>
                <div class="testimonials-list testimonials-list-fallback" hidden>
                    <?php foreach ($testimonials as $t):
                        $photo = (string) ($t['photo_path'] ?? '');
                        if ($photo !== '' && !str_starts_with($photo, '/')) {
                            $photo = '/' . ltrim($photo, '/');
                        }
                        ?>
                        <blockquote class="testimonial">
                            <?php if ($photo !== ''): ?>
                                <div class="testimonial__photo"><img src="<?= htmlspecialchars($photo, ENT_QUOTES, 'UTF-8') ?>" alt="" width="72" height="72" loading="lazy"></div>
                            <?php endif; ?>
                            <p class="testimonial__quote"><?= htmlspecialchars((string) $t['quote'], ENT_QUOTES, 'UTF-8') ?></p>
                            <footer>
                                <cite><?= htmlspecialchars((string) $t['name'], ENT_QUOTES, 'UTF-8') ?></cite>
                                <?php if (!empty($t['pet_name'])): ?>
                                    <span class="muted"> · <?= htmlspecialchars((string) $t['pet_name'], ENT_QUOTES, 'UTF-8') ?></span>
                                <?php endif; ?>
                            </footer>
                        </blockquote>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section bg-cream" id="faq">
    <div class="container-narrow">
        <header class="section-head reveal">
            <p class="text-uppercase text-gold">FAQ</p>
            <h2>Perguntas frequentes</h2>
        </header>
        <div class="faq-list reveal-stagger">
            <?php foreach ($faqs as $faq): ?>
                <details class="faq-item reveal">
                    <summary><?= htmlspecialchars((string) $faq['question'], ENT_QUOTES, 'UTF-8') ?></summary>
                    <div class="faq-item__body"><?= nl2br(htmlspecialchars((string) $faq['answer'], ENT_QUOTES, 'UTF-8')) ?></div>
                </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="cta-band">
    <div class="container-narrow reveal">
        <p class="text-uppercase">Pronto para começar?</p>
        <h2>Descubra a experiência ideal para o seu pet</h2>
        <p class="lead">Comece pelo quiz ou escolha um plano — a box chega com cuidado e surpresa.</p>
        <div class="hero-actions" style="justify-content:center">
            <a href="/quiz" class="btn btn-gold">Fazer o quiz</a>
            <a href="/planos" class="btn btn-secondary">Ver planos</a>
        </div>
    </div>
</section>

<div id="modal-lead" class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-lead-title">
    <div class="modal-dialog">
        <button type="button" class="modal-close" data-close-modal aria-label="Fechar">&times;</button>
        <h2 id="modal-lead-title">Cadastre seu perfil</h2>
        <p class="lead">Receba ofertas e novidades do Clubinho Pet Box.</p>
        <form id="form-lead" class="stack" action="/lead" method="POST">
            <div class="form-group">
                <label for="lead-name">Nome completo *</label>
                <input id="lead-name" type="text" name="name" required>
            </div>
            <div class="form-group">
                <label for="lead-email">E-mail *</label>
                <input id="lead-email" type="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="lead-phone">Telefone / WhatsApp *</label>
                <input id="lead-phone" type="tel" name="phone" required placeholder="(00) 00000-0000">
            </div>
            <div class="form-group">
                <label for="lead-pet">Tipo de pet *</label>
                <select id="lead-pet" name="pet_type" required>
                    <option value="">Selecione...</option>
                    <option value="dog">Cachorro</option>
                    <option value="cat">Gato</option>
                    <option value="both">Ambos</option>
                </select>
            </div>
            <div class="form-group">
                <label for="lead-city">Cidade</label>
                <input id="lead-city" type="text" name="city">
            </div>
            <div class="form-group cluster">
                <input type="checkbox" name="accept_newsletter" id="accept_newsletter" value="1" style="width:auto">
                <label for="accept_newsletter" style="margin:0;font-weight:400">Aceito receber novidades por e-mail</label>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Cadastrar perfil</button>
        </form>
    </div>
</div>

<script src="/assets/js/billing-toggle.js" defer></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const phone = document.querySelector('#lead-phone');
  if (phone) {
    phone.addEventListener('input', (e) => {
      let value = e.target.value.replace(/\D/g, '').slice(0, 11);
      value = value.replace(/(\d{2})(\d)/, '($1) $2');
      value = value.replace(/(\d{5})(\d)/, '$1-$2');
      e.target.value = value;
    });
  }
  const form = document.getElementById('form-lead');
  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      try {
        const body = new FormData(form);
        if (window.ClubinhoTrack && ClubinhoTrack.visitorId) {
          body.set('visitor_id', ClubinhoTrack.visitorId());
        }
        const response = await fetch('/lead', { method: 'POST', body });
        if (response.ok) {
          alert('Perfil cadastrado com sucesso!');
          document.getElementById('modal-lead').classList.remove('is-open');
          form.reset();
        } else {
          alert('Erro ao cadastrar. Tente novamente.');
        }
      } catch {
        alert('Erro ao cadastrar. Tente novamente.');
      }
    });
  }
});
</script>
<?php require BASE_PATH . '/templates/site/footer.php'; ?>
