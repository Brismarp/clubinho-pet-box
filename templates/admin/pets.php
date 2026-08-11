<?php declare(strict_types=1); /** @var list<array<string,mixed>> $items */ /** @var string $q */ ?>
<h1>Pets</h1>
<form class="filters" method="get" action="/admin/pets">
    <input type="search" name="q" value="<?= htmlspecialchars($q ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Pet ou tutor" style="max-width:280px">
    <button type="submit">Buscar</button>
</form>
<div class="card">
<?php if ($items === []): ?>
    <p class="empty">Nenhum pet.</p>
<?php else: ?>
    <table>
        <thead><tr><th>ID</th><th>Pet</th><th>Espécie</th><th>Tutor</th><th>E-mail</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($items as $row): ?>
            <tr>
                <td><?= (int) $row['id'] ?></td>
                <td><?= htmlspecialchars((string) $row['name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($row['species'] ?? $row['type'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($row['customer_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($row['customer_email'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><a href="/admin/pets/<?= (int) $row['id'] ?>">Abrir</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
</div>
