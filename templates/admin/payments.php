<?php declare(strict_types=1); /** @var list<array<string,mixed>> $items */ /** @var string $status */ ?>
<h1>Pagamentos</h1>
<div class="filters">
    <?php foreach (['all', 'paid', 'pending', 'failed', 'refunded', 'cancelled'] as $st): ?>
        <a class="<?= ($status ?? 'all') === $st ? 'is-active' : '' ?>" href="/admin/payments?status=<?= $st ?>"><?= $st ?></a>
    <?php endforeach; ?>
</div>
<div class="card">
<?php if ($items === []): ?>
    <p class="empty">Nenhum pagamento.</p>
<?php else: ?>
    <table>
        <thead><tr><th>ID</th><th>Status</th><th>Valor</th><th>E-mail</th><th>External</th><th>Pago em</th></tr></thead>
        <tbody>
        <?php foreach ($items as $row): ?>
            <tr>
                <td><?= (int) $row['id'] ?></td>
                <td><?= htmlspecialchars((string) $row['status'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>R$ <?= number_format(((int) $row['amount_cents']) / 100, 2, ',', '.') ?></td>
                <td><?= htmlspecialchars((string) ($row['customer_email'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                <td class="muted"><?= htmlspecialchars((string) ($row['external_id'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                <td class="muted"><?= htmlspecialchars((string) ($row['paid_at'] ?? $row['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
</div>
