<?php declare(strict_types=1); ?>
<h1>Leads</h1>
<p class="muted">Cadastros do modal da home (somente leitura nesta onda).</p>

<?php if (empty($items)): ?>
    <div class="card empty">Nenhum lead ainda.</div>
<?php else: ?>
<table>
    <thead>
        <tr>
            <th>ID</th><th>Nome</th><th>Email</th><th>Telefone</th><th>Pet</th><th>Cidade</th><th>Newsletter</th><th>Data</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $item): ?>
        <tr>
            <td><?= (int) $item['id'] ?></td>
            <td><?= htmlspecialchars((string) $item['name'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) $item['email'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) $item['phone'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) $item['pet_type'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) ($item['city'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= !empty($item['accept_newsletter']) ? 'sim' : 'não' ?></td>
            <td><?= htmlspecialchars((string) ($item['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
