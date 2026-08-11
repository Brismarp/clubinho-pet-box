<?php
declare(strict_types=1);
/** @var list<array<string,mixed>> $products */
/** @var list<array<string,mixed>> $movements */
?>
<h1>Estoque</h1>
<?php if (isset($_GET['saved'])): ?><div class="flash">Movimento registrado.</div><?php endif; ?>

<div class="card">
    <h2>Ajuste rápido</h2>
    <form method="post" action="/admin/stock/adjust" class="inline"
          onsubmit="return confirm('Confirmar ajuste de estoque?');">
        <select name="product_id" required style="width:auto;min-width:12rem">
            <?php foreach ($products as $p): ?>
                <option value="<?= (int) $p['id'] ?>"><?= htmlspecialchars((string) $p['name'], ENT_QUOTES, 'UTF-8') ?> (<?= (int) $p['stock_qty'] ?>)</option>
            <?php endforeach; ?>
        </select>
        <select name="reason" style="width:auto">
            <option value="receive">Entrada (receive)</option>
            <option value="adjust">Ajuste</option>
        </select>
        <input type="number" name="delta" required placeholder="delta (+/-)" style="width:7rem">
        <input type="text" name="note" placeholder="nota" style="max-width:12rem">
        <button type="submit">Aplicar</button>
    </form>
</div>

<div class="card">
    <h2>Produtos</h2>
    <table>
        <thead><tr><th>Produto</th><th>SKU</th><th>Qtd</th><th>Mín</th><th>Alerta</th></tr></thead>
        <tbody>
        <?php foreach ($products as $p): ?>
            <tr>
                <td><?= htmlspecialchars((string) $p['name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($p['sku'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= (int) $p['stock_qty'] ?></td>
                <td><?= (int) $p['stock_min'] ?></td>
                <td><?= !empty($p['is_low']) ? '⚠ baixo' : 'ok' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="card">
    <h2>Movimentos recentes</h2>
    <table>
        <thead><tr><th>ID</th><th>Produto</th><th>Delta</th><th>Saldo</th><th>Motivo</th><th>Quando</th></tr></thead>
        <tbody>
        <?php foreach ($movements as $m): ?>
            <tr>
                <td><?= (int) $m['id'] ?></td>
                <td><?= htmlspecialchars((string) $m['product_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= (int) $m['delta'] ?></td>
                <td><?= (int) $m['balance_after'] ?></td>
                <td><?= htmlspecialchars((string) $m['reason'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="muted"><?= htmlspecialchars((string) $m['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
