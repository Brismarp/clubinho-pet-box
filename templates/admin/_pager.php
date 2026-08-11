<?php

declare(strict_types=1);

/** @var array<string, mixed> $pager */
/** @var string $baseQs */
$pager = $pager ?? [];
$baseQs = $baseQs ?? '';
if (($pager['total_pages'] ?? 1) <= 1) {
    return;
}
$page = (int) ($pager['page'] ?? 1);
$totalPages = (int) ($pager['total_pages'] ?? 1);
$sep = $baseQs === '' ? '' : '&';
?>
<p class="muted" style="margin-top:1rem">
    Página <?= $page ?> de <?= $totalPages ?> · <?= (int) ($pager['total'] ?? 0) ?> itens
    <?php if (!empty($pager['has_prev'])): ?>
        · <a href="?<?= $baseQs . $sep ?>page=<?= $page - 1 ?>">Anterior</a>
    <?php endif; ?>
    <?php if (!empty($pager['has_next'])): ?>
        · <a href="?<?= $baseQs . $sep ?>page=<?= $page + 1 ?>">Próxima</a>
    <?php endif; ?>
</p>
