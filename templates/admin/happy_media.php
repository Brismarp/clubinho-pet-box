<?php declare(strict_types=1);
$statusFilter = (string) ($statusFilter ?? ($_GET['status'] ?? ''));
?>
<h1>Pets Felizes</h1>
<?php if (!empty($_GET['saved'])): ?><div class="flash">Salvo.</div><?php endif; ?>
<?php if (!empty($_GET['updated'])): ?><div class="flash">Status atualizado.</div><?php endif; ?>
<p><a class="btn" href="/admin/happy-media/new">Nova mídia</a></p>
<div class="filters">
    <a href="/admin/happy-media" class="<?= $statusFilter === '' ? 'is-active' : '' ?>">Todas</a>
    <a href="/admin/happy-media?status=pending" class="<?= $statusFilter === 'pending' ? 'is-active' : '' ?>">Pendentes</a>
    <a href="/admin/happy-media?status=approved" class="<?= $statusFilter === 'approved' ? 'is-active' : '' ?>">Aprovadas</a>
    <a href="/admin/happy-media?status=rejected" class="<?= $statusFilter === 'rejected' ? 'is-active' : '' ?>">Rejeitadas</a>
</div>
<table>
    <thead><tr><th>ID</th><th>Pet</th><th>Tipo</th><th>Status</th><th>Ordem</th><th></th></tr></thead>
    <tbody>
    <?php if ($items === []): ?>
        <tr><td colspan="6" class="empty">Nenhuma mídia neste filtro.</td></tr>
    <?php endif; ?>
    <?php foreach ($items as $item): ?>
        <tr>
            <td><?= (int) $item['id'] ?></td>
            <td><?= htmlspecialchars((string) $item['pet_name'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) $item['type'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) $item['status'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= (int) $item['display_order'] ?></td>
            <td style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center">
                <a href="/admin/happy-media/<?= (int) $item['id'] ?>/edit">Editar</a>
                <?php if (($item['status'] ?? '') !== 'approved'): ?>
                <form class="inline" method="post" action="/admin/happy-media/<?= (int) $item['id'] ?>/status">
                    <input type="hidden" name="status" value="approved">
                    <button type="submit">Aprovar</button>
                </form>
                <?php endif; ?>
                <?php if (($item['status'] ?? '') !== 'rejected'): ?>
                <form class="inline" method="post" action="/admin/happy-media/<?= (int) $item['id'] ?>/status">
                    <input type="hidden" name="status" value="rejected">
                    <button type="submit" class="btn-secondary">Rejeitar</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
