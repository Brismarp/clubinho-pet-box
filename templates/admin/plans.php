<?php declare(strict_types=1); ?>
<h1>Planos</h1>
<?php if (!empty($_GET['updated'])): ?><div class="flash">Checkout URL atualizada.</div><?php endif; ?>
<table>
    <thead><tr><th>ID</th><th>Nome</th><th>Slug</th><th>Preço</th><th>Checkout URL</th></tr></thead>
    <tbody>
    <?php foreach ($plans as $plan): ?>
        <tr>
            <td><?= (int) $plan['id'] ?></td>
            <td><?= htmlspecialchars((string) $plan['name'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) $plan['slug'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= number_format((float) $plan['price'], 2, ',', '.') ?></td>
            <td>
                <form class="inline" method="post" action="/admin/plans/checkout-url">
                    <input type="hidden" name="plan_id" value="<?= (int) $plan['id'] ?>">
                    <input name="checkout_url" value="<?= htmlspecialchars((string) ($plan['checkout_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit">Salvar</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
