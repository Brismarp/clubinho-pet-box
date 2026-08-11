<?php declare(strict_types=1); ?>
<h1>Pets Felizes</h1>
<?php if (!empty($_GET['saved'])): ?><div class="flash">Salvo.</div><?php endif; ?>
<p><a class="btn" href="/admin/happy-media/new">Nova mídia</a></p>
<table>
    <thead><tr><th>ID</th><th>Pet</th><th>Tipo</th><th>Status</th><th>Ordem</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($items as $item): ?>
        <tr>
            <td><?= (int) $item['id'] ?></td>
            <td><?= htmlspecialchars((string) $item['pet_name'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) $item['type'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) $item['status'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= (int) $item['display_order'] ?></td>
            <td><a href="/admin/happy-media/<?= (int) $item['id'] ?>/edit">Editar</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
