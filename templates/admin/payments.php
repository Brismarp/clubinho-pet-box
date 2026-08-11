<?php declare(strict_types=1);
/** @var list<array<string,mixed>> $items */
/** @var string $status */
/** @var array<string,mixed> $summary */
/** @var array<string,mixed> $pager */
/** @var array<string,mixed> $filters */
$summary = $summary ?? [];
$pager = $pager ?? [];
$filters = $filters ?? ['status' => 'all', 'q' => '', 'range' => 30];
$status = $status ?? ($filters['status'] ?? 'all') ?: 'all';
$q = (string) ($filters['q'] ?? '');
$range = (int) ($filters['range'] ?? 30);
?>
<h1>Pagamentos</h1>
<div class="filters">
    <span class="muted">Período:</span>
    <?php foreach ([7, 30, 90] as $d): ?>
        <a class="<?= $range === $d ? 'is-active' : '' ?>"
           href="/admin/payments?range=<?= $d ?>&status=<?= urlencode((string) $status) ?>&q=<?= urlencode($q) ?>"><?= $d ?>d</a>
    <?php endforeach; ?>
</div>
<div class="grid">
    <div class="card">
        <div class="muted">Receita paid (<?= $range ?>d)</div>
        <div class="stat">R$ <?= number_format(((int) ($summary['paid_cents'] ?? 0)) / 100, 2, ',', '.') ?></div>
    </div>
    <div class="card">
        <div class="muted">Pending</div>
        <div class="stat"><?= (int) ($summary['pending_count'] ?? 0) ?></div>
    </div>
    <div class="card">
        <div class="muted">Failed</div>
        <div class="stat"><?= (int) ($summary['failed_count'] ?? 0) ?></div>
    </div>
    <div class="card">
        <div class="muted">Refunded</div>
        <div class="stat"><?= (int) ($summary['refunded_count'] ?? 0) ?></div>
    </div>
    <div class="card">
        <div class="muted">Inadimplência (ativos sem paid 45d)</div>
        <div class="stat"><?= (int) ($summary['delinquent_active_subs'] ?? 0) ?></div>
    </div>
</div>
<form class="filters" method="get" action="/admin/payments">
    <input type="hidden" name="range" value="<?= $range ?>">
    <input type="search" name="q" value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>" placeholder="E-mail" style="max-width:220px">
    <select name="status" style="width:auto">
        <?php foreach (['all', 'paid', 'pending', 'failed', 'refunded', 'cancelled'] as $st): ?>
            <option value="<?= $st ?>" <?= $status === $st ? 'selected' : '' ?>><?= $st ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Filtrar</button>
</form>
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
    <?php
    $baseQs = http_build_query([
        'range' => $range,
        'status' => $status,
        'q' => $q,
    ]);
    require __DIR__ . '/_pager.php';
    ?>
<?php endif; ?>
</div>
