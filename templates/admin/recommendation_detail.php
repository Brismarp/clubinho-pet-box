<?php
declare(strict_types=1);
/** @var array<string,mixed> $item */
$items = $item['items'] ?? [];
?>
<h1>Recomendação #<?= (int) $item['id'] ?></h1>
<p><a href="/admin/recommendations">← Fila</a>
   · <a href="/admin/quizzes/<?= (int) ($item['session_id'] ?? 0) ?>">Quiz</a></p>
<?php if (isset($_GET['saved'])): ?><div class="flash">Atualizado.</div><?php endif; ?>

<div class="card">
    <p><strong>Status:</strong> <?= htmlspecialchars((string) $item['status'], ENT_QUOTES, 'UTF-8') ?></p>
    <p><strong>Tutor:</strong> <?= htmlspecialchars((string) ($item['customer_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?>
        · <?= htmlspecialchars((string) ($item['customer_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
    <p><strong>Pet:</strong> <?= htmlspecialchars((string) ($item['pet_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?>
        (<?= htmlspecialchars((string) ($item['species'] ?? ''), ENT_QUOTES, 'UTF-8') ?>,
        <?= htmlspecialchars((string) ($item['size'] ?? ''), ENT_QUOTES, 'UTF-8') ?>)</p>
    <p><strong>Plano:</strong> <?= htmlspecialchars((string) ($item['suggested_plan_slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
    <form method="post" action="/admin/recommendations/<?= (int) $item['id'] ?>/status" class="inline" style="margin-top:1rem">
        <select name="status" style="width:auto">
            <?php foreach (['pending_review', 'approved', 'rejected'] as $st): ?>
                <option value="<?= $st ?>" <?= ($item['status'] ?? '') === $st ? 'selected' : '' ?>><?= $st ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Atualizar status</button>
    </form>
</div>

<div class="card">
    <h2>Itens sugeridos</h2>
    <table>
        <thead>
            <tr><th>Produto</th><th>SKU</th><th>Estoque</th><th>Score</th><th>Justificativa</th><th>Selecionado</th></tr>
        </thead>
        <tbody>
        <?php foreach ($items as $row): ?>
            <tr>
                <td><?= htmlspecialchars((string) $row['product_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($row['sku'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= (int) ($row['stock_qty'] ?? 0) ?></td>
                <td><?= (int) $row['score'] ?></td>
                <td><?= htmlspecialchars((string) $row['justification'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <form method="post" action="/admin/recommendations/item" class="inline">
                        <input type="hidden" name="recommendation_id" value="<?= (int) $item['id'] ?>">
                        <input type="hidden" name="item_id" value="<?= (int) $row['id'] ?>">
                        <input type="hidden" name="is_selected" value="<?= !empty($row['is_selected']) ? '0' : '1' ?>">
                        <button type="submit" class="btn-secondary"><?= !empty($row['is_selected']) ? 'Remover' : 'Incluir' ?></button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
