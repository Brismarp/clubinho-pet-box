<?php

declare(strict_types=1);

/** @var array<string, mixed> $post */
/** @var list<array<string, mixed>> $related */
use ClubinhoPet\Shared\Infrastructure\Env;

$related = $related ?? [];
$base = rtrim(Env::get('APP_URL', 'http://localhost:8080') ?? 'http://localhost:8080', '/');
$activeNav = 'blog';
$pageTitle = (string) (($post['seo_title'] ?? null) ?: ($post['title'] . ' | Clubinho Pet Box'));
$metaDescription = (string) (($post['seo_description'] ?? null) ?: ($post['excerpt'] ?? 'Artigo Clubinho Pet Box'));
$canonicalPath = '/blog/' . (string) $post['slug'];
$cover = (string) ($post['cover_image'] ?: '/assets/images/boxes/box-love.jpg');
if ($cover !== '' && !str_starts_with($cover, 'http') && !str_starts_with($cover, '/')) {
    $cover = '/' . ltrim($cover, '/');
}
$ogImage = str_starts_with($cover, 'http') ? $cover : ($base . $cover);

require BASE_PATH . '/templates/site/header.php';
?>
<article class="blog-article">
    <header class="blog-article__hero">
        <div class="blog-article__hero-media">
            <img src="<?= htmlspecialchars($cover, ENT_QUOTES, 'UTF-8') ?>" alt="" width="1600" height="900" fetchpriority="high">
        </div>
        <div class="container-narrow blog-article__hero-copy reveal">
            <p class="text-uppercase text-gold"><a href="/blog">Blog</a></p>
            <h1><?= htmlspecialchars((string) $post['title'], ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="blog-item-meta"><?= htmlspecialchars((string) ($post['published_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    </header>
    <div class="section bg-white">
        <div class="container-narrow">
            <div class="blog-prose reveal">
                <?= $post['body'] /* trusted admin HTML */ ?>
            </div>
            <div class="hero-actions reveal" style="justify-content:center;margin-top:var(--space-8)">
                <a class="btn btn-gold" href="/quiz">Encontrar minha box</a>
                <a class="btn btn-secondary" href="/blog">Voltar ao blog</a>
            </div>
        </div>
    </div>
</article>

<?php if ($related !== []): ?>
<section class="section bg-cream">
    <div class="container">
        <header class="section-head reveal">
            <p class="text-uppercase text-gold">Continue lendo</p>
            <h2>Outros artigos</h2>
        </header>
        <div class="blog-mosaic blog-mosaic--related reveal-stagger">
            <?php foreach ($related as $row):
                $rCover = (string) ($row['cover_image'] ?: '/assets/images/mood/q01.jpg');
                if ($rCover !== '' && !str_starts_with($rCover, 'http') && !str_starts_with($rCover, '/')) {
                    $rCover = '/' . ltrim($rCover, '/');
                }
                $href = '/blog/' . rawurlencode((string) $row['slug']);
                ?>
                <article class="blog-mosaic__item reveal reveal--up">
                    <a class="blog-mosaic__media" href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>">
                        <img src="<?= htmlspecialchars($rCover, ENT_QUOTES, 'UTF-8') ?>" alt="" width="640" height="480" loading="lazy">
                    </a>
                    <p class="blog-item-meta"><?= htmlspecialchars((string) ($row['published_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                    <h3 class="blog-mosaic__title">
                        <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars((string) $row['title'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </h3>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
<?php require BASE_PATH . '/templates/site/footer.php'; ?>
