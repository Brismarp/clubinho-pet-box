<?php

declare(strict_types=1);

$item = $item ?? [];
$isEdit = !empty($item['id']);
$action = $isEdit ? '/admin/happy-media/' . (int) $item['id'] : '/admin/happy-media';
?>
<h1><?= $isEdit ? 'Editar mídia' : 'Nova mídia' ?></h1>
<?php if (!empty($error)): ?><div class="error"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<form method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>" enctype="multipart/form-data" class="card">
    <p><label>Pet<br><input name="pet_name" required value="<?= htmlspecialchars((string) ($item['pet_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></label></p>
    <p><label>@ tutor<br><input name="tutor_handle" value="<?= htmlspecialchars((string) ($item['tutor_handle'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></label></p>
    <p><label>Tipo
        <select name="type">
            <?php foreach (['image','video'] as $t): ?>
                <option value="<?= $t ?>" <?= (($item['type'] ?? 'image') === $t) ? 'selected' : '' ?>><?= $t ?></option>
            <?php endforeach; ?>
        </select>
    </label></p>
    <p><label>Media path<br><input name="media_path" value="<?= htmlspecialchars((string) ($item['media_path'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="/assets/images/mood/q01.jpg"></label></p>
    <p><label>Upload foto ou vídeo<br><input type="file" name="media_file" accept=".jpg,.jpeg,.png,.webp,.mp4,.webm,image/*,video/mp4,video/webm"></label></p>
    <p><label>URL vídeo externa (opcional)<br><input name="media_url" value="<?= htmlspecialchars((string) ($item['media_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></label></p>
    <p><label>Orientação
        <select name="orientation">
            <?php foreach (['portrait','landscape'] as $o): ?>
                <option value="<?= $o ?>" <?= (($item['orientation'] ?? 'landscape') === $o) ? 'selected' : '' ?>><?= $o ?></option>
            <?php endforeach; ?>
        </select>
    </label></p>
    <p><label>Status
        <select name="status">
            <?php foreach (['pending','approved','rejected'] as $st): ?>
                <option value="<?= $st ?>" <?= (($item['status'] ?? 'pending') === $st) ? 'selected' : '' ?>><?= $st ?></option>
            <?php endforeach; ?>
        </select>
    </label></p>
    <p><label>Ordem<br><input type="number" name="display_order" value="<?= (int) ($item['display_order'] ?? 0) ?>"></label></p>
    <p><button type="submit">Salvar</button> <a class="btn btn-secondary" href="/admin/happy-media">Voltar</a></p>
</form>
