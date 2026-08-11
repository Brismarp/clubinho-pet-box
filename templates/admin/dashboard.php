<?php

declare(strict_types=1);

/** @var array<string, mixed> $metrics */
$metrics = $metrics ?? [];
$range = (int) ($metrics['range_days'] ?? 30);
$ops = $metrics['operational'] ?? [];
$funnel = $metrics['funnel']['counts'] ?? [];
$unique = (int) ($metrics['funnel']['unique_visitors'] ?? 0);
$pageViews = (int) ($funnel['page_view'] ?? 0);
$maxFunnel = max(1, $pageViews, $unique);

$steps = [
    ['key' => 'page_view', 'label' => 'Page views', 'value' => $pageViews],
    ['key' => 'unique', 'label' => 'Visitantes únicos', 'value' => $unique],
    ['key' => 'quiz_start', 'label' => 'Quiz start', 'value' => (int) ($funnel['quiz_start'] ?? 0)],
    ['key' => 'quiz_complete', 'label' => 'Quiz complete', 'value' => (int) ($funnel['quiz_complete'] ?? 0)],
    ['key' => 'checkout_click', 'label' => 'Checkout click', 'value' => (int) ($funnel['checkout_click'] ?? 0)],
    ['key' => 'subscribe_submit', 'label' => 'Assinatura (form)', 'value' => (int) ($funnel['subscribe_submit'] ?? 0)],
    ['key' => 'lead_submit', 'label' => 'Leads', 'value' => (int) ($funnel['lead_submit'] ?? 0)],
    ['key' => 'contact_submit', 'label' => 'Contatos', 'value' => (int) ($funnel['contact_submit'] ?? 0)],
];
?>
<h1>Dashboard</h1>

<div class="filters">
    <span class="muted">Período:</span>
    <?php foreach ([7, 30, 90] as $d): ?>
        <a class="<?= $range === $d ? 'is-active' : '' ?>" href="/admin/dashboard?range=<?= $d ?>"><?= $d ?> dias</a>
    <?php endforeach; ?>
</div>

<div class="grid">
    <div class="card">
        <div class="muted">Assinaturas (período)</div>
        <div class="stat"><?= (int) ($ops['subscriptions_period'] ?? 0) ?></div>
        <div class="muted">total <?= (int) ($ops['subscriptions_total'] ?? 0) ?></div>
    </div>
    <div class="card">
        <div class="muted">Contatos novos</div>
        <div class="stat"><?= (int) ($ops['contacts_new'] ?? 0) ?></div>
        <div class="muted"><?= (int) ($ops['contacts_period'] ?? 0) ?> no período</div>
    </div>
    <div class="card">
        <div class="muted">Boxes a preparar</div>
        <div class="stat"><?= (int) ($ops['boxes_preparing'] ?? 0) ?></div>
        <div class="muted"><?= (int) ($ops['boxes_shipped'] ?? 0) ?> enviadas</div>
    </div>
    <div class="card">
        <div class="muted">Leads (período)</div>
        <div class="stat"><?= (int) ($ops['leads_period'] ?? 0) ?></div>
        <div class="muted"><?= (int) ($ops['plans_active'] ?? 0) ?> planos ativos</div>
    </div>
    <div class="card">
        <div class="muted">Quizzes concluídos</div>
        <div class="stat"><?= (int) ($metrics['quiz_complete'] ?? 0) ?></div>
    </div>
    <div class="card">
        <div class="muted">Receita (pagamentos paid)</div>
        <div class="stat">R$ <?= number_format(((int) ($ops['revenue_cents'] ?? 0)) / 100, 2, ',', '.') ?></div>
        <div class="muted"><a href="/admin/payments">Ver pagamentos</a></div>
    </div>
    <div class="card">
        <div class="muted">Recomendações pendentes</div>
        <div class="stat"><?= (int) ($ops['recommendations_pending'] ?? 0) ?></div>
        <div class="muted"><a href="/admin/recommendations">Fila</a></div>
    </div>
    <div class="card">
        <div class="muted">Estoque baixo</div>
        <div class="stat"><?= (int) ($ops['low_stock'] ?? 0) ?></div>
        <div class="muted"><a href="/admin/stock">Estoque</a></div>
    </div>
</div>

<div class="card">
    <h2>Funil (<?= (int) $range ?> dias)</h2>
    <?php if ($pageViews === 0 && $unique === 0): ?>
        <p class="empty">Ainda sem tráfego tracked. Abra o site público para começar a coletar eventos.</p>
    <?php else: ?>
        <?php foreach ($steps as $step):
            $pct = min(100, (int) round(($step['value'] / $maxFunnel) * 100));
            ?>
            <div class="progress-row">
                <div><?= htmlspecialchars($step['label'], ENT_QUOTES, 'UTF-8') ?></div>
                <div class="bar"><span style="width:<?= $pct ?>%"></span></div>
                <div><?= (int) $step['value'] ?></div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="grid">
    <div class="card">
        <h2>Top páginas</h2>
        <?php $paths = $metrics['top_paths'] ?? []; ?>
        <?php if ($paths === []): ?>
            <p class="muted">Sem page views no período.</p>
        <?php else: ?>
            <table>
                <thead><tr><th>Path</th><th>Views</th></tr></thead>
                <tbody>
                <?php foreach ($paths as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) $row['path'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= (int) $row['views'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <div class="card">
        <h2>Checkout por plano</h2>
        <?php $byPlan = $metrics['checkout_by_plan'] ?? []; ?>
        <?php if ($byPlan === []): ?>
            <p class="muted">Sem checkout clicks no período.</p>
        <?php else: ?>
            <table>
                <thead><tr><th>Plano</th><th>Clicks</th></tr></thead>
                <tbody>
                <?php foreach ($byPlan as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) $row['plan_slug'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= (int) $row['clicks'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <div class="card">
        <h2>Assinaturas por status</h2>
        <?php $byStatus = $ops['subscriptions_by_status'] ?? []; ?>
        <?php if ($byStatus === []): ?>
            <p class="muted">Nenhuma assinatura.</p>
        <?php else: ?>
            <table>
                <thead><tr><th>Status</th><th>Qtd</th></tr></thead>
                <tbody>
                <?php foreach ($byStatus as $st => $c): ?>
                    <tr><td><?= htmlspecialchars((string) $st, ENT_QUOTES, 'UTF-8') ?></td><td><?= (int) $c ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
