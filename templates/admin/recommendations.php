<?php
declare(strict_types=1);
/** @var list<array<string,mixed>> $items */
/** @var string $status */
$status = $status ?? 'pending_review';
?>
<h1>Recomendações</h1>
<div class="filters">
    <?php foreach (['pending_review' => 'Pendentes', 'approved' => 'Aprovadas', 'rejected' => 'Rejeitadas', 'all' => 'Todas'] as $k => $label): ?>
        <a href="/admin/recommendations?status=<?= urlencode($k) ?>" class="<?= $status === $k ? 'is-active' : '' ?>"><?= $label ?></a>
    <?php endforeach; ?>
</div>
<div class="card">
<?php if ($items === []): ?>
    <p class="empty">Nenhuma recomendação nesta fila.</p>
<?php else: ?>
    <table>
        <thead>
            <tr><th>ID</th><th>Status</th><th>Tutor</th><th>Pet</th><th>Plano</th><th>Data</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($items as $row): ?>
            <tr>
                <td><?= (int) $row['id'] ?></td>
                <td><?= htmlspecialchars((string) $row['status'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($row['customer_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($row['pet_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($row['suggested_plan_slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td class="muted"><?= htmlspecialchars((string) ($row['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td><a href="/admin/recommendations/<?= (int) $row['id'] ?>">Revisar</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
</div>
