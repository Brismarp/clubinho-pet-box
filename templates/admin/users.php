<?php declare(strict_types=1); /** @var list<array<string,mixed>> $items */ ?>
<h1>Usuários admin</h1>
<?php if (isset($_GET['saved'])): ?><div class="flash">Usuário salvo.</div><?php endif; ?>
<?php if (isset($_GET['error'])): ?><div class="error"><?= htmlspecialchars((string) $_GET['error'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<p><a class="btn" href="/admin/users/new">Novo usuário</a></p>
<div class="card">
<?php if ($items === []): ?>
    <p class="empty">Nenhum usuário.</p>
<?php else: ?>
    <table>
        <thead>
            <tr><th>ID</th><th>Nome</th><th>E-mail</th><th>Role</th><th>Ativo</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($items as $row): ?>
            <tr>
                <td><?= (int) $row['id'] ?></td>
                <td><?= htmlspecialchars((string) $row['name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) $row['email'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) $row['role'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= !empty($row['is_active']) ? 'sim' : 'não' ?></td>
                <td>
                    <a href="/admin/users/<?= (int) $row['id'] ?>/edit">Editar</a>
                    <form class="inline" method="post" action="/admin/users/<?= (int) $row['id'] ?>/active"
                          onsubmit="return confirm('Alterar status deste usuário?');">
                        <input type="hidden" name="is_active" value="<?= !empty($row['is_active']) ? '0' : '1' ?>">
                        <button type="submit"><?= !empty($row['is_active']) ? 'Desativar' : 'Ativar' ?></button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
</div>
