<?php

declare(strict_types=1);

use ClubinhoPet\Identity\Domain\UserRole;

$item = $item ?? [];
$isEdit = !empty($item['id']);
$action = $isEdit ? '/admin/users/' . (int) $item['id'] : '/admin/users';
?>
<h1><?= $isEdit ? 'Editar usuário' : 'Novo usuário' ?></h1>
<?php if (!empty($error)): ?><div class="error"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<form method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>" class="card">
    <p><label>Nome<br><input name="name" required value="<?= htmlspecialchars((string) ($item['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></label></p>
    <p><label>E-mail<br><input type="email" name="email" required value="<?= htmlspecialchars((string) ($item['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></label></p>
    <p><label>Role
        <select name="role">
            <?php foreach (UserRole::values() as $role): ?>
                <option value="<?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>"
                    <?= (($item['role'] ?? 'atendimento') === $role) ? 'selected' : '' ?>><?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
    </label></p>
    <p><label>Senha<?= $isEdit ? ' (deixe em branco para manter)' : '' ?><br>
        <input type="password" name="password" <?= $isEdit ? '' : 'required minlength="8"' ?> autocomplete="new-password">
    </label></p>
    <p><label><input type="checkbox" name="is_active" value="1" <?= !isset($item['is_active']) || !empty($item['is_active']) ? 'checked' : '' ?>> Ativo</label></p>
    <p>
        <button type="submit">Salvar</button>
        <a class="btn btn-secondary" href="/admin/users">Voltar</a>
    </p>
</form>
