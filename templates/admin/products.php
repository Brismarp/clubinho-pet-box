<?php declare(strict_types=1); /** @var list<array<string,mixed>> $items */ /** @var string $q */ /** @var array<string,mixed> $pager */ ?>
<h1>Produtos</h1>
<p><a class="btn" href="/admin/products/new">Novo produto</a></p>
<?php if (isset($_GET['saved'])): ?><div class="flash">Salvo.</div><?php endif; ?>
<form class="filters" method="get" action="/admin/products">
    <input type="search" name="q" value="<?= htmlspecialchars($q ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Nome, SKU ou categoria" style="max-width:280px">
    <button type="submit">Buscar</button>
</form>
<div class="card">
<table>
    <thead>
        <tr>
            <th>SKU</th>
            <th>Nome</th>
            <th>Categoria</th>
            <th>Estoque</th>
            <th>Disponível</th>
            <th>Tags</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php if ($items === []): ?>
        <tr><td colspan="7" class="empty">Nenhum produto.</td></tr>
    <?php endif; ?>
    <?php foreach ($items as $row): ?>
        <tr>
            <td><?= htmlspecialchars((string) ($row['sku'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) $row['name'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) ($row['category'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= (int) ($row['stock_qty'] ?? 0) ?></td>
            <td><?= !empty($row['is_available']) ? 'sim' : 'não' ?></td>
            <td class="muted">
                <?php
                $tags = $row['tags'] ?? [];
                $parts = [];
                foreach ($tags as $t) {
                    $parts[] = $t['tag_key'] . '=' . $t['tag_value'];
                }
                echo htmlspecialchars(implode(', ', array_slice($parts, 0, 6)), ENT_QUOTES, 'UTF-8');
                if (count($parts) > 6) {
                    echo '…';
                }
                ?>
            </td>
            <td><a href="/admin/products/<?= (int) $row['id'] ?>/edit">Editar</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php
$baseQs = http_build_query(['q' => $q ?? '']);
require __DIR__ . '/_pager.php';
?>
</div>
