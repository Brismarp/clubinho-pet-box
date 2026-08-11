<?php
declare(strict_types=1);
/** @var array<string,mixed> $item */
$answers = $item['answers'] ?? [];
$attributes = $item['attributes'] ?? [];
$recs = $item['recommendations'] ?? [];
?>
<h1>Quiz #<?= (int) $item['id'] ?></h1>
<p><a href="/admin/quizzes">← Voltar</a></p>
<div class="card">
    <p><strong>Status:</strong> <?= htmlspecialchars((string) $item['status'], ENT_QUOTES, 'UTF-8') ?></p>
    <p><strong>Tutor:</strong> <?= htmlspecialchars((string) ($item['customer_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?>
        · <?= htmlspecialchars((string) ($item['customer_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
        · <?= htmlspecialchars((string) ($item['customer_phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
    <p><strong>Pet:</strong> <?= htmlspecialchars((string) ($item['pet_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?>
        (<?= htmlspecialchars((string) ($item['pet_species'] ?? ''), ENT_QUOTES, 'UTF-8') ?>,
        <?= htmlspecialchars((string) ($item['pet_size'] ?? ''), ENT_QUOTES, 'UTF-8') ?>)</p>
    <p><strong>Plano sugerido:</strong> <?= htmlspecialchars((string) ($item['suggested_plan_slug'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></p>
</div>

<div class="card">
    <h2>Atributos</h2>
    <?php if ($attributes === []): ?>
        <p class="muted">Nenhum atributo (quiz incompleto).</p>
    <?php else: ?>
        <table>
            <thead><tr><th>Chave</th><th>Valor</th></tr></thead>
            <tbody>
            <?php foreach ($attributes as $attr): ?>
                <tr>
                    <td><?= htmlspecialchars((string) $attr['attr_key'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) $attr['attr_value'], ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Respostas</h2>
    <?php foreach ($answers as $step => $qs): ?>
        <h3 style="margin-top:1rem"><?= htmlspecialchars((string) $step, ENT_QUOTES, 'UTF-8') ?></h3>
        <ul>
            <?php foreach ($qs as $q => $v): ?>
                <li><strong><?= htmlspecialchars((string) $q, ENT_QUOTES, 'UTF-8') ?>:</strong>
                    <?= htmlspecialchars(is_scalar($v) ? (string) $v : json_encode($v, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endforeach; ?>
</div>

<div class="card">
    <h2>Recomendações</h2>
    <?php if ($recs === []): ?>
        <p class="muted">Nenhuma ainda.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($recs as $r): ?>
                <li><a href="/admin/recommendations/<?= (int) $r['id'] ?>">#<?= (int) $r['id'] ?></a>
                    — <?= htmlspecialchars((string) $r['status'], ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
