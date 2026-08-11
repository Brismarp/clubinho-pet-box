<?php declare(strict_types=1); ?>
<h1>Boxes</h1>
<?php if (!empty($_GET['created'])): ?><div class="flash">Box criada.</div><?php endif; ?>
<?php if (!empty($_GET['shipped'])): ?><div class="flash">Box marcada como shipped.</div><?php endif; ?>

<div class="card">
    <h2>Nova box</h2>
    <form method="post" action="/admin/boxes">
        <p>
            <label>Subscription
                <select name="subscription_id" required>
                    <option value="">—</option>
                    <?php foreach ($subscriptions as $sub): ?>
                        <option value="<?= (int) $sub['id'] ?>">#<?= (int) $sub['id'] ?> · <?= htmlspecialchars((string) $sub['email'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </p>
        <p><label>Box number<br><input type="number" name="box_number" min="1"></label></p>
        <p><label>Notes<br><input name="notes"></label></p>
        <p><button type="submit">Criar</button></p>
    </form>
</div>

<table>
    <thead><tr><th>ID</th><th>Sub</th><th>Status</th><th>Tracking</th><th>Ação</th></tr></thead>
    <tbody>
    <?php foreach ($boxes as $box): ?>
        <tr>
            <td><?= (int) $box['id'] ?></td>
            <td><?= (int) $box['subscription_id'] ?></td>
            <td><?= htmlspecialchars((string) $box['status'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) ($box['tracking_number'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
            <td>
                <?php if ($box['status'] !== 'shipped'): ?>
                <form class="inline" method="post" action="/admin/boxes/ship">
                    <input type="hidden" name="box_id" value="<?= (int) $box['id'] ?>">
                    <input name="tracking_number" placeholder="tracking">
                    <button type="submit">Ship</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
