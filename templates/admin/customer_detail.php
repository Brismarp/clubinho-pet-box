<?php
declare(strict_types=1);
/** @var array<string,mixed> $customer */
/** @var list<array<string,mixed>> $pets */
/** @var list<array<string,mixed>> $quizzes */
/** @var list<array<string,mixed>> $subscriptions */
/** @var list<array<string,mixed>> $boxes */
/** @var list<array<string,mixed>> $payments */
?>
<p><a href="/admin/customers">← Clientes</a></p>
<?php if (isset($_GET['erased'])): ?><div class="flash">Dados do cliente anonimizados/apagados (DSAR).</div><?php endif; ?>
<?php if (isset($_GET['erase_error'])): ?><div class="flash" style="background:#ffe8e8;border-color:#f5c2c2">
    <?= htmlspecialchars((string) $_GET['erase_error'], ENT_QUOTES, 'UTF-8') ?>
</div><?php endif; ?>
<h1><?= htmlspecialchars((string) $customer['name'], ENT_QUOTES, 'UTF-8') ?></h1>
<div class="card">
    <p><strong>E-mail:</strong> <?= htmlspecialchars((string) $customer['email'], ENT_QUOTES, 'UTF-8') ?></p>
    <p><strong>Telefone:</strong> <?= htmlspecialchars((string) ($customer['phone'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></p>
    <p><strong>Cidade:</strong> <?= htmlspecialchars((string) ($customer['city'] ?? '—'), ENT_QUOTES, 'UTF-8') ?>
        <?= htmlspecialchars((string) ($customer['state'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
    <p><strong>LGPD:</strong> <?= htmlspecialchars((string) ($customer['consent_lgpd_at'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></p>
    <p class="muted"><?= htmlspecialchars((string) ($customer['notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
    <p>
        <a href="/admin/customers/<?= (int) $customer['id'] ?>/export">Exportar JSON</a>
    </p>
</div>

<div class="card">
    <h2>Excluir dados (DSAR)</h2>
    <p class="muted">Digite <strong>EXCLUIR</strong> para anonimizar/apagar dados pessoais deste cliente.</p>
    <form method="post" action="/admin/customers/<?= (int) $customer['id'] ?>/erase" class="inline"
          onsubmit="return confirm('Confirma exclusão/anonymização LGPD deste cliente?');">
        <input type="text" name="confirm" placeholder="EXCLUIR" required autocomplete="off" style="max-width:10rem">
        <button type="submit">Apagar dados</button>
    </form>
</div>

<div class="card">
    <h2>Pets</h2>
    <?php if ($pets === []): ?><p class="empty">Nenhum pet.</p><?php else: ?>
    <ul>
        <?php foreach ($pets as $p): ?>
            <li><a href="/admin/pets/<?= (int) $p['id'] ?>"><?= htmlspecialchars((string) $p['name'], ENT_QUOTES, 'UTF-8') ?></a>
                · <?= htmlspecialchars((string) ($p['species'] ?? ''), ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Quizzes</h2>
    <?php if ($quizzes === []): ?><p class="empty">—</p><?php else: ?>
    <table>
        <thead><tr><th>ID</th><th>Status</th><th>Pet</th><th>Plano</th><th>Quando</th></tr></thead>
        <tbody>
        <?php foreach ($quizzes as $q): ?>
            <tr>
                <td><a href="/admin/quizzes/<?= (int) $q['id'] ?>"><?= (int) $q['id'] ?></a></td>
                <td><?= htmlspecialchars((string) $q['status'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($q['pet_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($q['suggested_plan_slug'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                <td class="muted"><?= htmlspecialchars((string) ($q['completed_at'] ?? $q['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<div class="grid">
    <div class="card">
        <h2>Assinaturas</h2>
        <?php if ($subscriptions === []): ?><p class="empty">—</p><?php else: ?>
            <?php foreach ($subscriptions as $s): ?>
                <p>#<?= (int) $s['id'] ?> · <?= htmlspecialchars((string) ($s['plan_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                    · <?= htmlspecialchars((string) $s['status'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div class="card">
        <h2>Boxes</h2>
        <?php if ($boxes === []): ?><p class="empty">—</p><?php else: ?>
            <?php foreach ($boxes as $b): ?>
                <p>#<?= (int) $b['id'] ?> · <?= htmlspecialchars((string) $b['status'], ENT_QUOTES, 'UTF-8') ?>
                    · <?= htmlspecialchars((string) ($b['tracking_number'] ?? 'sem tracking'), ENT_QUOTES, 'UTF-8') ?></p>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div class="card">
        <h2>Pagamentos</h2>
        <?php if ($payments === []): ?><p class="empty">—</p><?php else: ?>
            <?php foreach ($payments as $p): ?>
                <p>#<?= (int) $p['id'] ?> · <?= htmlspecialchars((string) $p['status'], ENT_QUOTES, 'UTF-8') ?>
                    · R$ <?= number_format(((int) $p['amount_cents']) / 100, 2, ',', '.') ?></p>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
