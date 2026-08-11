<?php declare(strict_types=1); ?>
<h1>Blog</h1>
<?php if (!empty($flash)): ?><div class="flash">Post salvo.</div><?php endif; ?>
<p><a class="btn" href="/admin/blog/new">Novo post</a></p>

<?php if (empty($items)): ?>
    <div class="card empty">Nenhum post ainda.</div>
<?php else: ?>
<table>
    <thead><tr><th>ID</th><th>Título</th><th>Slug</th><th>Status</th><th>Published</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($items as $item): ?>
        <tr>
            <td><?= (int) $item['id'] ?></td>
            <td><?= htmlspecialchars((string) $item['title'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><a href="/blog/<?= htmlspecialchars((string) $item['slug'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"><?= htmlspecialchars((string) $item['slug'], ENT_QUOTES, 'UTF-8') ?></a></td>
            <td><?= htmlspecialchars((string) $item['status'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) ($item['published_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
            <td><a href="/admin/blog/<?= (int) $item['id'] ?>/edit">Editar</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
