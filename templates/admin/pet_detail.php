<?php
declare(strict_types=1);
/** @var array<string,mixed> $pet */
/** @var list<array<string,mixed>> $quizzes */
/** @var array<string,string> $attributes */
/** @var list<array<string,mixed>> $recommendations */
?>
<p><a href="/admin/pets">← Pets</a>
<?php if (!empty($pet['customer_id'])): ?>
 · <a href="/admin/customers/<?= (int) $pet['customer_id'] ?>">Tutor</a>
<?php endif; ?>
</p>
<h1><?= htmlspecialchars((string) $pet['name'], ENT_QUOTES, 'UTF-8') ?></h1>
<div class="card">
    <p><strong>Espécie:</strong> <?= htmlspecialchars((string) ($pet['species'] ?? $pet['type'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></p>
    <p><strong>Porte:</strong> <?= htmlspecialchars((string) ($pet['size'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></p>
    <p><strong>Tutor:</strong> <?= htmlspecialchars((string) ($pet['customer_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?>
        · <?= htmlspecialchars((string) ($pet['customer_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
    <p><strong>Restrições:</strong> <?= htmlspecialchars((string) ($pet['dietary_restrictions'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></p>
    <p><strong>Notas:</strong> <?= htmlspecialchars((string) ($pet['health_notes'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></p>
</div>

<div class="card">
    <h2>Atributos (último quiz)</h2>
    <?php if ($attributes === []): ?><p class="empty">—</p><?php else: ?>
    <ul>
        <?php foreach ($attributes as $k => $v): ?>
            <li><code><?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8') ?></code> = <?= htmlspecialchars($v, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Recomendações</h2>
    <?php if ($recommendations === []): ?><p class="empty">—</p><?php else: ?>
        <?php foreach ($recommendations as $r): ?>
            <p><a href="/admin/recommendations/<?= (int) $r['id'] ?>">#<?= (int) $r['id'] ?></a>
                · <?= htmlspecialchars((string) $r['status'], ENT_QUOTES, 'UTF-8') ?>
                · <?= htmlspecialchars((string) ($r['suggested_plan_slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
