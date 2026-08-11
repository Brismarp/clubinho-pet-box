<?php declare(strict_types=1); /** @var list<array<string,mixed>> $items */ ?>
<h1>Auditoria</h1>
<p class="muted">Últimos eventos registrados (settings, quiz, envio, webhooks, recomendações).</p>
<div class="card">
<?php if ($items === []): ?>
    <p class="empty">Nenhum log ainda.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Ação</th>
                <th>Entidade</th>
                <th>Usuário</th>
                <th>Quando</th>
                <th>Detalhe</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $row): ?>
            <tr>
                <td><?= (int) $row['id'] ?></td>
                <td><?= htmlspecialchars((string) $row['action'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="muted">
                    <?= htmlspecialchars((string) ($row['entity_type'] ?? '—'), ENT_QUOTES, 'UTF-8') ?>
                    <?php if (!empty($row['entity_id'])): ?>#<?= (int) $row['entity_id'] ?><?php endif; ?>
                </td>
                <td><?= $row['user_id'] !== null ? (int) $row['user_id'] : '—' ?></td>
                <td class="muted"><?= htmlspecialchars((string) ($row['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td class="muted" style="max-width:18rem;word-break:break-word;font-size:.8rem">
                    <?= htmlspecialchars((string) ($row['new_values'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
</div>
