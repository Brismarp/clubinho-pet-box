<?php
declare(strict_types=1);
$item = $item ?? [];
$isEdit = !empty($item['id']);
$action = $isEdit ? '/admin/products/' . (int) $item['id'] : '/admin/products';
$tagsText = '';
if (!empty($item['tags']) && is_array($item['tags'])) {
    foreach ($item['tags'] as $t) {
        $tagsText .= ($t['tag_key'] ?? '') . '=' . ($t['tag_value'] ?? '') . "\n";
    }
} elseif (!empty($item['tags_text'])) {
    $tagsText = (string) $item['tags_text'];
}
?>
<h1><?= $isEdit ? 'Editar produto' : 'Novo produto' ?></h1>
<?php if (!empty($error)): ?><div class="error"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<form method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>" class="card">
    <p><label>Nome<br><input name="name" required value="<?= htmlspecialchars((string) ($item['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></label></p>
    <p><label>SKU<br><input name="sku" value="<?= htmlspecialchars((string) ($item['sku'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></label></p>
    <p><label>Categoria<br><input name="category" value="<?= htmlspecialchars((string) ($item['category'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></label></p>
    <p><label>Descrição<br><textarea name="description" rows="3"><?= htmlspecialchars((string) ($item['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea></label></p>
    <p><label>Preço<br><input name="price" type="number" step="0.01" value="<?= htmlspecialchars((string) ($item['price'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></label></p>
    <p><label>Estoque<br><input name="stock_qty" type="number" value="<?= (int) ($item['stock_qty'] ?? 0) ?>"></label></p>
    <p><label>Estoque mínimo<br><input name="stock_min" type="number" value="<?= (int) ($item['stock_min'] ?? 0) ?>"></label></p>
    <p><label><input type="checkbox" name="is_available" value="1" <?= !isset($item['is_available']) || !empty($item['is_available']) ? 'checked' : '' ?> style="width:auto"> Disponível</label></p>
    <p><label>Tags (uma por linha, chave=valor)<br>
        <textarea name="tags_text" rows="8" style="max-width:100%"><?= htmlspecialchars($tagsText, ENT_QUOTES, 'UTF-8') ?></textarea>
    </label></p>
    <p><button type="submit">Salvar</button> <a class="btn btn-secondary" href="/admin/products">Voltar</a></p>
</form>
