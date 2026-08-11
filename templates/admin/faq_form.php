<?php

declare(strict_types=1);

$item = $item ?? [];
$isEdit = !empty($item['id']);
$action = $isEdit ? '/admin/faq/' . (int) $item['id'] : '/admin/faq';
?>
<h1><?= $isEdit ? 'Editar FAQ' : 'Nova FAQ' ?></h1>
<?php if (!empty($error)): ?><div class="error"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<form method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>" class="card">
    <p><label>Pergunta<br><input name="question" required value="<?= htmlspecialchars((string) ($item['question'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></label></p>
    <p><label>Resposta<br><textarea name="answer" rows="5" required><?= htmlspecialchars((string) ($item['answer'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea></label></p>
    <p><label>Ordem<br><input type="number" name="display_order" value="<?= (int) ($item['display_order'] ?? 0) ?>"></label></p>
    <p><label><input type="checkbox" name="is_published" value="1" <?= !isset($item['is_published']) || !empty($item['is_published']) ? 'checked' : '' ?> style="width:auto"> Publicado</label></p>
    <p><button type="submit">Salvar</button> <a class="btn btn-secondary" href="/admin/faq">Voltar</a></p>
</form>
