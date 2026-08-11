<?php declare(strict_types=1); ?>
<?php
/** @var list<array<string,mixed>> $items */
/** @var string $status */
$canWrite = $canWrite ?? true;
?>
<h1>Assinaturas</h1>
<?php if (isset($_GET['updated'])): ?><div class="flash">Status atualizado.</div><?php endif; ?>
<?php if (!empty($_GET['error'])): ?><div class="error"><?= htmlspecialchars((string) $_GET['error'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php $status = $status ?? 'all'; ?>
<div class="filters">
    <?php foreach (['all' => 'Todos', 'active' => 'Active', 'paused' => 'Paused', 'cancelled' => 'Cancelled', 'expired' => 'Expired', 'pending' => 'Pending'] as $val => $label): ?>
        <a class="<?= $status === $val ? 'is-active' : '' ?>" href="/admin/subscriptions?status=<?= urlencode($val) ?>"><?= $label ?></a>
    <?php endforeach; ?>
</div>

<?php if (empty($items)): ?>
    <div class="card empty">Nenhuma assinatura neste filtro.</div>
<?php else: ?>
<table>
    <thead>
        <tr>
            <th>ID</th><th>Email</th><th>Plano</th><th>Pet</th><th>Status</th>
            <th>Início</th><th>Próx. cobrança</th><th>Notes</th><th>Ações</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $item):
        $notes = (string) ($item['notes'] ?? '');
        $billing = null;
        if (preg_match('/billing:\s*(monthly|annual)/i', $notes, $m)) {
            $billing = strtolower($m[1]);
        }
        $st = (string) $item['status'];
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
            <td><?= htmlspecialchars($st, ENT_QUOTES, 'UTF-8') ?></td>
            <td class="muted"><?= htmlspecialchars((string) ($item['start_date'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
            <td class="muted"><?= htmlspecialchars((string) ($item['next_billing_date'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
            <td>
                <?php if ($billing): ?><strong><?= htmlspecialchars($billing, ENT_QUOTES, 'UTF-8') ?></strong><br><?php endif; ?>
                <span class="muted"><?= htmlspecialchars($notes, ENT_QUOTES, 'UTF-8') ?></span>
            </td>
            <td>
                <?php if ($canWrite): ?>
                    <?php if ($st === 'active'): ?>
                        <form class="inline" method="post" action="/admin/subscriptions/status"
                              onsubmit="return confirm('Pausar esta assinatura?');">
                            <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                            <input type="hidden" name="action" value="pause">
                            <button type="submit">Pausar</button>
                        </form>
                        <form class="inline" method="post" action="/admin/subscriptions/status"
                              onsubmit="var t=prompt('Digite CANCELAR para confirmar:'); if(!t) return false; this.querySelector('[name=confirm]').value=t; return t.toUpperCase()==='CANCELAR';">
                            <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                            <input type="hidden" name="action" value="cancel">
                            <input type="hidden" name="confirm" value="">
                            <button type="submit">Cancelar</button>
                        </form>
                    <?php elseif ($st === 'paused'): ?>
                        <form class="inline" method="post" action="/admin/subscriptions/status"
                              onsubmit="return confirm('Reativar esta assinatura?');">
                            <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                            <input type="hidden" name="action" value="reactivate">
                            <button type="submit">Reativar</button>
                        </form>
                        <form class="inline" method="post" action="/admin/subscriptions/status"
                              onsubmit="var t=prompt('Digite CANCELAR para confirmar:'); if(!t) return false; this.querySelector('[name=confirm]').value=t; return t.toUpperCase()==='CANCELAR';">
                            <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                            <input type="hidden" name="action" value="cancel">
                            <input type="hidden" name="confirm" value="">
                            <button type="submit">Cancelar</button>
                        </form>
                    <?php else: ?>
                        <span class="muted">—</span>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="muted">somente leitura</span>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
