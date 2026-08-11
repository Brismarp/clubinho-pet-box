<?php declare(strict_types=1); ?>
<h1>Outbox — e-mail / WhatsApp (mock)</h1>
<p class="muted">Mensagens transacionais gravadas por <code>MockNotifier</code> com template renderizado em <code>payload_json._rendered</code>.</p>

<?php $channel = $channel ?? 'all'; ?>
<div class="filters" style="margin-bottom:1rem">
    <?php foreach (['all' => 'Todos', 'email' => 'E-mail', 'whatsapp' => 'WhatsApp', 'log' => 'Log'] as $k => $label): ?>
        <a class="chip<?= $channel === $k ? ' is-active' : '' ?>" href="/admin/outbox?channel=<?= urlencode($k) ?>"><?= $label ?></a>
    <?php endforeach; ?>
</div>

<?php if (empty($items)): ?>
    <div class="card empty">Nenhuma notificação no outbox.</div>
<?php else: ?>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Canal</th>
            <th>Para</th>
            <th>Template</th>
            <th>Assunto / corpo</th>
            <th>Status</th>
            <th>Enviado</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $row): ?>
        <?php
        $payload = [];
        if (!empty($row['payload_json'])) {
            $decoded = json_decode((string) $row['payload_json'], true);
            $payload = is_array($decoded) ? $decoded : [];
        }
        $rendered = is_array($payload['_rendered'] ?? null) ? $payload['_rendered'] : [];
        $subject = (string) ($rendered['subject'] ?? '—');
        $body = (string) ($rendered['body'] ?? '');
        ?>
        <tr>
            <td><?= (int) $row['id'] ?></td>
            <td><?= htmlspecialchars((string) $row['channel'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) $row['recipient'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><code><?= htmlspecialchars((string) $row['template_key'], ENT_QUOTES, 'UTF-8') ?></code></td>
            <td>
                <strong><?= htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') ?></strong>
                <?php if ($body !== ''): ?>
                    <div class="muted" style="white-space:pre-wrap;font-size:.85rem;max-width:28rem"><?= htmlspecialchars($body, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars((string) $row['status'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) ($row['sent_at'] ?? $row['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
