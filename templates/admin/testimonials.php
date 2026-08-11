<?php declare(strict_types=1); ?>
<h1>Depoimentos</h1>
<?php if (!empty($_GET['saved'])): ?><div class="flash">Salvo.</div><?php endif; ?>
<p><a class="btn" href="/admin/testimonials/new">Novo</a></p>
<table>
    <thead><tr><th>ID</th><th>Nome</th><th>Pet</th><th>Status</th><th>Ordem</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($items as $item): ?>
        <tr>
            <td><?= (int) $item['id'] ?></td>
            <td><?= htmlspecialchars((string) $item['name'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) ($item['pet_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) $item['status'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= (int) $item['display_order'] ?></td>
            <td><a href="/admin/testimonials/<?= (int) $item['id'] ?>/edit">Editar</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
