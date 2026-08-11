<?php declare(strict_types=1); ?>
<h1>FAQ</h1>
<?php if (!empty($_GET['saved'])): ?><div class="flash">Salvo.</div><?php endif; ?>
<p><a class="btn" href="/admin/faq/new">Nova pergunta</a></p>
<table>
    <thead><tr><th>ID</th><th>Pergunta</th><th>Publicado</th><th>Ordem</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($items as $item): ?>
        <tr>
            <td><?= (int) $item['id'] ?></td>
            <td><?= htmlspecialchars((string) $item['question'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= !empty($item['is_published']) ? 'sim' : 'não' ?></td>
            <td><?= (int) $item['display_order'] ?></td>
            <td><a href="/admin/faq/<?= (int) $item['id'] ?>/edit">Editar</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
