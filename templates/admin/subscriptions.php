<?php declare(strict_types=1); ?>
<h1>Assinaturas</h1>
<?php $status = $status ?? 'all'; ?>
<div class="filters">
    <?php foreach (['all' => 'Todos', 'active' => 'Active', 'paused' => 'Paused', 'cancelled' => 'Cancelled', 'expired' => 'Expired'] as $val => $label): ?>
        <a class="<?= $status === $val ? 'is-active' : '' ?>" href="/admin/subscriptions?status=<?= urlencode($val) ?>"><?= $label ?></a>
    <?php endforeach; ?>
</div>

<?php if (empty($items)): ?>
    <div class="card empty">Nenhuma assinatura neste filtro.</div>
<?php else: ?>
<table>
    <thead>
        <tr>
            <th>ID</th><th>Email</th><th>Plano</th><th>Pet</th><th>Status</th><th>Data</th><th>Notes / billing</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $item):
        $notes = (string) ($item['notes'] ?? '');
        $billing = null;
        if (preg_match('/billing:\s*(monthly|annual)/i', $notes, $m)) {
            $billing = strtolower($m[1]);
        }
        ?>
        <tr>
            <td><?= (int) $item['id'] ?></td>
            <td><?= htmlspecialchars((string) $item['email'], ENT_QUOTES, 'UTF-8') ?></td>
            <td>
                <?= htmlspecialchars((string) ($item['plan_name'] ?: ('#' . $item['plan_id'])), ENT_QUOTES, 'UTF-8') ?>
                <?php if (!empty($item['plan_slug'])): ?>
                    <div class="muted"><?= htmlspecialchars((string) $item['plan_slug'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars((string) ($item['pet_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) $item['status'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) ($item['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
            <td>
                <?php if ($billing): ?><strong><?= htmlspecialchars($billing, ENT_QUOTES, 'UTF-8') ?></strong><br><?php endif; ?>
                <span class="muted"><?= htmlspecialchars($notes, ENT_QUOTES, 'UTF-8') ?></span>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
