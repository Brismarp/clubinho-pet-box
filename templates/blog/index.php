<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $posts */
$activeNav = 'blog';
$pageTitle = 'Blog | Clubinho Pet Box';
$metaDescription = 'Guias e inspirações sobre cuidados com cães — Clubinho Pet Box.';
$canonicalPath = '/blog';
require BASE_PATH . '/templates/site/header.php';
?>
<section class="page-hero">
    <div class="container reveal">
        <p class="text-uppercase text-gold">Portal Clubinho</p>
        <h1>Blog para tutores de cães</h1>
        <p class="lead">Cuidados, comportamento e ideias para momentos especiais com o seu dog.</p>
    </div>
</section>
<section class="section bg-white">
    <div class="container">
        <?php if ($posts === []): ?>
            <p class="empty">Em breve novos artigos. Enquanto isso, faça o <a href="/quiz.html">quiz</a> ou veja os <a href="/planos.html">planos</a>.</p>
        <?php else: ?>
            <div class="blog-list">
                <?php foreach ($posts as $post):
                    $cover = (string) ($post['cover_image'] ?: '/assets/images/mood/q01.jpg');
                    if ($cover !== '' && !str_starts_with($cover, 'http') && !str_starts_with($cover, '/')) {
                        $cover = '/' . ltrim($cover, '/');
                    }
                    ?>
                    <article class="blog-item reveal">
                        <div>
                            <p class="blog-item-meta"><?= htmlspecialchars((string) ($post['published_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                            <h2 class="blog-item-title">
                                <a href="/blog/<?= htmlspecialchars((string) $post['slug'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars((string) $post['title'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </h2>
                            <p class="lead" style="font-size:var(--text-base);margin:0 0 1rem">
                                <?= htmlspecialchars((string) ($post['excerpt'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                            </p>
                            <a class="btn btn-secondary" href="/blog/<?= htmlspecialchars((string) $post['slug'], ENT_QUOTES, 'UTF-8') ?>">Ler artigo</a>
                        </div>
                        <div class="blog-item-media">
                            <a href="/blog/<?= htmlspecialchars((string) $post['slug'], ENT_QUOTES, 'UTF-8') ?>">
                                <img src="<?= htmlspecialchars($cover, ENT_QUOTES, 'UTF-8') ?>" alt="" width="800" height="560" loading="lazy">
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="section-cta" style="margin-top:var(--space-8);text-align:center">
            <h2>Descubra a box ideal</h2>
            <p class="lead">Responda o quiz ou escolha um plano.</p>
            <div class="hero-actions" style="justify-content:center">
                <a class="btn btn-gold" href="/quiz.html">Fazer quiz</a>
                <a class="btn btn-secondary" href="/planos.html">Ver planos</a>
            </div>
        </div>
    </div>
</section>
<?php require BASE_PATH . '/templates/site/footer.php'; ?>
