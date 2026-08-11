<?php

declare(strict_types=1);

/** @var array<string, mixed>|null $post */
$post = $post ?? [];
$isEdit = !empty($post['id']);
$action = $isEdit ? '/admin/blog/' . (int) $post['id'] : '/admin/blog';
?>
<h1><?= $isEdit ? 'Editar post' : 'Novo post' ?></h1>
<?php if (!empty($error)): ?><div class="error"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

<form method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>" class="card">
    <p><label>Título<br><input name="title" required value="<?= htmlspecialchars((string) ($post['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></label></p>
    <p><label>Slug<br><input name="slug" placeholder="gerado-do-titulo" value="<?= htmlspecialchars((string) ($post['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></label></p>
    <p><label>Excerpt<br><textarea name="excerpt" rows="3"><?= htmlspecialchars((string) ($post['excerpt'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea></label></p>
    <p><label>Body (HTML)<br><textarea name="body" rows="14" required><?= htmlspecialchars((string) ($post['body'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea></label></p>
    <p><label>Cover image path<br><input name="cover_image" placeholder="/assets/images/mood/q01.jpg" value="<?= htmlspecialchars((string) ($post['cover_image'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></label></p>
    <p><label>Status
        <select name="status">
            <?php foreach (['draft' => 'Draft', 'published' => 'Published'] as $val => $label): ?>
                <option value="<?= $val ?>" <?= (($post['status'] ?? 'draft') === $val) ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
    </label></p>
    <p><label>Published at<br><input name="published_at" placeholder="YYYY-MM-DD HH:MM:SS" value="<?= htmlspecialchars((string) ($post['published_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></label></p>
    <p><label>SEO title<br><input name="seo_title" value="<?= htmlspecialchars((string) ($post['seo_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></label></p>
    <p><label>SEO description<br><input name="seo_description" value="<?= htmlspecialchars((string) ($post['seo_description'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></label></p>
    <p>
        <button type="submit">Salvar</button>
        <a class="btn btn-secondary" href="/admin/blog">Cancelar</a>
    </p>
</form>
