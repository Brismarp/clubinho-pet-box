<?php declare(strict_types=1); /** @var list<array<string,mixed>> $items */ ?>
<h1>Quizzes</h1>
<?php if (isset($_GET['saved'])): ?><div class="flash">Salvo.</div><?php endif; ?>
<div class="card">
<?php if ($items === []): ?>
    <p class="empty">Nenhum quiz ainda.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Status</th>
                <th>Tutor</th>
                <th>Pet</th>
                <th>Plano</th>
                <th>Data</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $row): ?>
            <tr>
                <td><?= (int) $row['id'] ?></td>
                <td><?= htmlspecialchars((string) $row['status'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <?= htmlspecialchars((string) ($row['customer_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?>
                    <div class="muted"><?= htmlspecialchars((string) ($row['customer_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                </td>
                <td><?= htmlspecialchars((string) ($row['pet_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($row['suggested_plan_slug'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                <td class="muted"><?= htmlspecialchars((string) ($row['completed_at'] ?? $row['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td><a href="/admin/quizzes/<?= (int) $row['id'] ?>">Ver</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
</div>
