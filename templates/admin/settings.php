<?php declare(strict_types=1); ?>
<h1>Settings</h1>
<?php if (!empty($_GET['updated'])): ?><div class="flash">Setting atualizada.</div><?php endif; ?>
<table>
    <thead><tr><th>Key</th><th>Value</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($settings as $setting): ?>
        <tr>
            <td><?= htmlspecialchars((string) $setting['key'], ENT_QUOTES, 'UTF-8') ?></td>
            <td colspan="2">
                <form class="inline" method="post" action="/admin/settings">
                    <input type="hidden" name="key" value="<?= htmlspecialchars((string) $setting['key'], ENT_QUOTES, 'UTF-8') ?>">
                    <input name="value" value="<?= htmlspecialchars((string) ($setting['value'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit">Salvar</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
