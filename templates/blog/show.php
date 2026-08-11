<?php

declare(strict_types=1);

/** @var array<string, mixed> $post */
use ClubinhoPet\Shared\Infrastructure\Env;

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
<section class="page-hero">
    <div class="container-narrow reveal">
        <p class="text-uppercase text-gold"><a href="/blog">Blog</a></p>
        <h1><?= htmlspecialchars((string) $post['title'], ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="blog-item-meta"><?= htmlspecialchars((string) ($post['published_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
    </div>
</section>
<article class="section bg-white">
    <div class="container-narrow">
        <img class="blog-post-cover" src="<?= htmlspecialchars($cover, ENT_QUOTES, 'UTF-8') ?>" alt="" width="1200" height="675">
        <div class="blog-prose">
            <?= $post['body'] /* trusted admin HTML */ ?>
        </div>
        <div style="margin-top:var(--space-8);text-align:center">
            <a class="btn btn-gold" href="/quiz">Encontrar minha box</a>
            <a class="btn btn-secondary" href="/blog">Voltar ao blog</a>
        </div>
    </div>
</article>
<?php require BASE_PATH . '/templates/site/footer.php'; ?>
