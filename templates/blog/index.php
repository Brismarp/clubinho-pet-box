<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $posts */
$activeNav = 'blog';
$pageTitle = 'Blog | Clubinho Pet Box';
$metaDescription = 'Guias e inspirações sobre cuidados com cães — Clubinho Pet Box.';
$canonicalPath = '/blog';
require BASE_PATH . '/templates/site/header.php';

$normalizeCover = static function (array $post): string {
    $cover = (string) ($post['cover_image'] ?: '/assets/images/mood/q01.jpg');
    if ($cover !== '' && !str_starts_with($cover, 'http') && !str_starts_with($cover, '/')) {
        $cover = '/' . ltrim($cover, '/');
    }

    return $cover;
};

$featured = array_slice($posts, 0, min(5, count($posts)));
$rest = array_slice($posts, count($featured) > 0 ? 1 : 0);
// mosaic starts after first featured used in carousel; keep all except duplicates of carousel items lightly
$mosaic = array_slice($posts, min(3, count($posts)));
?>
<section class="page-hero blog-hero">
    <div class="container reveal">
        <p class="text-uppercase text-gold">Portal Clubinho</p>
        <h1>Blog para tutores de cães</h1>
        <p class="lead">Cuidados, comportamento e ideias para momentos especiais com o seu dog.</p>
    </div>
</section>

<section class="section bg-white blog-portal">
    <div class="container">
        <?php if ($posts === []): ?>
            <p class="empty">Em breve novos artigos. Enquanto isso, faça o <a href="/quiz">quiz</a> ou veja os <a href="/planos">planos</a>.</p>
        <?php else: ?>
            <div class="blog-featured-grid reveal">
                <div
                    class="blog-carousel"
                    data-blog-carousel
                    data-interval="5000"
                    aria-roledescription="carrossel"
                    aria-label="Destaques do blog"
                >
                    <div class="blog-carousel__viewport">
                        <?php foreach ($featured as $i => $post):
                            $cover = $normalizeCover($post);
                            $href = '/blog/' . rawurlencode((string) $post['slug']);
                            ?>
                            <article class="blog-carousel__slide<?= $i === 0 ? ' is-active' : '' ?>" data-slide="<?= $i ?>"<?= $i === 0 ? '' : ' hidden' ?>>
                                <a class="blog-carousel__media" href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>">
                                    <img src="<?= htmlspecialchars($cover, ENT_QUOTES, 'UTF-8') ?>" alt="" width="1200" height="750"<?= $i === 0 ? ' fetchpriority="high"' : ' loading="lazy"' ?>>
                                </a>
                                <div class="blog-carousel__body">
                                    <p class="blog-item-meta"><?= htmlspecialchars((string) ($post['published_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                                    <h2 class="blog-carousel__title">
                                        <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>">
                                            <?= htmlspecialchars((string) $post['title'], ENT_QUOTES, 'UTF-8') ?>
                                        </a>
                                    </h2>
                                    <p class="blog-carousel__excerpt"><?= htmlspecialchars((string) ($post['excerpt'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                                    <a class="btn btn-secondary" href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>">Ler artigo</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($featured) > 1): ?>
                        <div class="blog-carousel__controls">
                            <button type="button" class="blog-carousel__btn" data-carousel-prev aria-label="Anterior">‹</button>
                            <div class="blog-carousel__dots" role="tablist" aria-label="Slides">
                                <?php foreach ($featured as $i => $_): ?>
                                    <button type="button" class="blog-carousel__dot<?= $i === 0 ? ' is-active' : '' ?>" data-carousel-dot="<?= $i ?>" aria-label="Ir para destaque <?= $i + 1 ?>"></button>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="blog-carousel__btn" data-carousel-next aria-label="Próximo">›</button>
                        </div>
                    <?php endif; ?>
                </div>

                <aside class="blog-side-rail reveal-stagger">
                    <?php foreach (array_slice($posts, 1, 3) as $post):
                        $cover = $normalizeCover($post);
                        $href = '/blog/' . rawurlencode((string) $post['slug']);
                        ?>
                        <article class="blog-rail-item reveal reveal--up">
                            <a class="blog-rail-item__media" href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>">
                                <img src="<?= htmlspecialchars($cover, ENT_QUOTES, 'UTF-8') ?>" alt="" width="400" height="300" loading="lazy">
                            </a>
                            <div>
                                <p class="blog-item-meta"><?= htmlspecialchars((string) ($post['published_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                                <h3 class="blog-rail-item__title">
                                    <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars((string) $post['title'], ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                </h3>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </aside>
            </div>

            <?php if ($mosaic !== []): ?>
                <header class="section-head reveal" style="margin-top:var(--space-8)">
                    <p class="text-uppercase text-gold">Mais leituras</p>
                    <h2>Últimas publicações</h2>
                </header>
                <div class="blog-mosaic reveal-stagger">
                    <?php foreach ($mosaic as $post):
                        $cover = $normalizeCover($post);
                        $href = '/blog/' . rawurlencode((string) $post['slug']);
                        ?>
                        <article class="blog-mosaic__item reveal reveal--up">
                            <a class="blog-mosaic__media" href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>">
                                <img src="<?= htmlspecialchars($cover, ENT_QUOTES, 'UTF-8') ?>" alt="" width="640" height="480" loading="lazy">
                            </a>
                            <p class="blog-item-meta"><?= htmlspecialchars((string) ($post['published_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                            <h3 class="blog-mosaic__title">
                                <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars((string) $post['title'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </h3>
                            <p class="blog-mosaic__excerpt"><?= htmlspecialchars((string) ($post['excerpt'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="cta-band" style="margin-top:var(--space-8);border-radius:var(--radius-md)">
            <div class="container-narrow reveal">
                <p class="text-uppercase">Descubra a box ideal</p>
                <h2>Responda o quiz ou escolha um plano</h2>
                <div class="hero-actions" style="justify-content:center">
                    <a class="btn btn-gold" href="/quiz">Fazer quiz</a>
                    <a class="btn btn-secondary" href="/planos">Ver planos</a>
                </div>
            </div>
        </div>
    </div>
</section>
<script src="/assets/js/blog-carousel.js" defer></script>
<?php require BASE_PATH . '/templates/site/footer.php'; ?>
