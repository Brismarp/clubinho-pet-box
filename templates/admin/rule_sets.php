<?php declare(strict_types=1); ?>
<h1>Rule sets</h1>
<p class="muted">Somente leitura — engine ativo usado nas recomendações do quiz.</p>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Código</th>
            <th>Título</th>
            <th>Ativo</th>
            <th>JSON (bytes)</th>
            <th>Criado em</th>
        </tr>
    </thead>
    <tbody>
    <?php if ($items === []): ?>
        <tr><td colspan="6" class="empty">Nenhum ruleset cadastrado.</td></tr>
    <?php endif; ?>
    <?php foreach ($items as $item): ?>
        <tr>
            <td><?= (int) $item['id'] ?></td>
            <td><?= htmlspecialchars((string) $item['code'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) $item['title'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= !empty($item['is_active']) ? 'sim' : 'não' ?></td>
            <td><?= (int) ($item['rules_bytes'] ?? 0) ?></td>
            <td><?= htmlspecialchars((string) ($item['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
