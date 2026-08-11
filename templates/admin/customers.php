<?php declare(strict_types=1); /** @var list<array<string,mixed>> $items */ /** @var string $q */ ?>
<h1>Clientes</h1>
<form class="filters" method="get" action="/admin/customers">
    <input type="search" name="q" value="<?= htmlspecialchars($q ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Nome, e-mail ou telefone" style="max-width:280px">
    <button type="submit">Buscar</button>
</form>
<div class="card">
<?php if ($items === []): ?>
    <p class="empty">Nenhum cliente.</p>
<?php else: ?>
    <table>
        <thead><tr><th>ID</th><th>Nome</th><th>E-mail</th><th>Telefone</th><th>Pets</th><th>Quizzes</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($items as $row): ?>
            <tr>
                <td><?= (int) $row['id'] ?></td>
                <td><?= htmlspecialchars((string) $row['name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) $row['email'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($row['phone'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= (int) ($row['pets_count'] ?? 0) ?></td>
                <td><?= (int) ($row['quizzes_count'] ?? 0) ?></td>
                <td><a href="/admin/customers/<?= (int) $row['id'] ?>">Abrir</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
</div>
