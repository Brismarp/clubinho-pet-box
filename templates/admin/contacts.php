<?php declare(strict_types=1); ?>
<h1>Contatos</h1>
<?php if (!empty($_GET['updated'])): ?><div class="flash">Atualizado.</div><?php endif; ?>
<table>
    <thead><tr><th>ID</th><th>Nome</th><th>Email</th><th>Assunto</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($items as $item): ?>
        <tr>
            <td><?= (int) $item['id'] ?></td>
            <td><?= htmlspecialchars((string) $item['name'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) $item['email'], ENT_QUOTES, 'UTF-8') ?></td>
            <td>
                <strong><?= htmlspecialchars((string) $item['subject'], ENT_QUOTES, 'UTF-8') ?></strong><br>
                <span class="muted"><?= htmlspecialchars((string) $item['message'], ENT_QUOTES, 'UTF-8') ?></span>
            </td>
            <td><?= htmlspecialchars((string) $item['status'], ENT_QUOTES, 'UTF-8') ?></td>
            <td>
                <?php if ($item['status'] === 'new'): ?>
                <form method="post" action="/admin/contacts/read">
                    <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                    <button type="submit">Marcar lido</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
