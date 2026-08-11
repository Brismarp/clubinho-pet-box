<?php

declare(strict_types=1);

$item = $item ?? [];
$isEdit = !empty($item['id']);
$action = $isEdit ? '/admin/testimonials/' . (int) $item['id'] : '/admin/testimonials';
?>
<h1><?= $isEdit ? 'Editar depoimento' : 'Novo depoimento' ?></h1>
<?php if (!empty($error)): ?><div class="error"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<form method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>" enctype="multipart/form-data" class="card">
    <p><label>Nome<br><input name="name" required value="<?= htmlspecialchars((string) ($item['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></label></p>
    <p><label>Pet<br><input name="pet_name" value="<?= htmlspecialchars((string) ($item['pet_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></label></p>
    <p><label>Quote<br><textarea name="quote" rows="4" required><?= htmlspecialchars((string) ($item['quote'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea></label></p>
    <p><label>Photo path<br><input name="photo_path" value="<?= htmlspecialchars((string) ($item['photo_path'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="/assets/images/mood/q01.jpg"></label></p>
    <p><label>Upload (opcional)<br><input type="file" name="photo_file" accept=".jpg,.jpeg,.png,.webp"></label></p>
    <p><label>Status
        <select name="status">
            <?php foreach (['pending','published','hidden'] as $st): ?>
                <option value="<?= $st ?>" <?= (($item['status'] ?? 'pending') === $st) ? 'selected' : '' ?>><?= $st ?></option>
            <?php endforeach; ?>
        </select>
    </label></p>
    <p><label>Ordem<br><input type="number" name="display_order" value="<?= (int) ($item['display_order'] ?? 0) ?>"></label></p>
    <p><button type="submit">Salvar</button> <a class="btn btn-secondary" href="/admin/testimonials">Voltar</a></p>
</form>
