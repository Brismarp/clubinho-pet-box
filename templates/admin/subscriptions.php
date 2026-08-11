<?php declare(strict_types=1); ?>
<h1>Assinaturas</h1>
<table>
    <thead><tr><th>ID</th><th>Email</th><th>Plan</th><th>Pet</th><th>Status</th><th>Notes</th></tr></thead>
    <tbody>
    <?php foreach ($items as $item): ?>
        <tr>
            <td><?= (int) $item['id'] ?></td>
            <td><?= htmlspecialchars((string) $item['email'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= (int) $item['plan_id'] ?></td>
            <td><?= htmlspecialchars((string) ($item['pet_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) $item['status'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) ($item['notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
