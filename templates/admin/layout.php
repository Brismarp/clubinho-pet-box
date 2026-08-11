<?php

declare(strict_types=1);

/** @var string $view */
/** @var \ClubinhoPet\Identity\Domain\AuthSession|null $auth */
$auth = $auth ?? null;
$title = $title ?? 'Admin | Clubinho Pet Box';
$contacts_new = (int) ($contacts_new ?? 0);
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="csrf-token" content="<?= htmlspecialchars(\ClubinhoPet\Shared\Infrastructure\Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="admin-body">
<div class="wrap">
<?php if ($view !== 'login'): ?>
    <div class="top">
        <div class="brand">Clubinho Pet Box</div>
        <nav>
            <?php
            /** @var callable(string):bool $can */
            $can = $can ?? static fn (string $p): bool => true;
            $links = [
                ['/admin/dashboard', 'Dashboard', 'dashboard'],
                ['/admin/customers', 'Clientes', 'customers'],
                ['/admin/pets', 'Pets', 'pets'],
                ['/admin/plans', 'Planos', 'plans'],
                ['/admin/subscriptions', 'Assinaturas', 'subscriptions'],
                ['/admin/boxes', 'Boxes', 'boxes'],
                ['/admin/recommendations', 'Recomendações', 'recommendations'],
                ['/admin/rule-sets', 'Rule sets', ['content', 'recommendations']],
                ['/admin/products', 'Produtos', 'products'],
                ['/admin/stock', 'Estoque', 'stock'],
                ['/admin/payments', 'Pagamentos', 'payments'],
                ['/admin/outbox', 'Outbox', 'outbox'],
                ['/admin/contacts', 'Contatos', 'contacts'],
                ['/admin/leads', 'Leads', 'leads'],
                ['/admin/quizzes', 'Quizzes', 'quizzes'],
                ['/admin/blog', 'Blog', 'content'],
                ['/admin/testimonials', 'Depoimentos', 'content'],
                ['/admin/happy-media', 'Pets Felizes', 'content'],
                ['/admin/faq', 'FAQ', 'content'],
                ['/admin/settings', 'Settings', 'settings'],
                ['/admin/audit', 'Auditoria', 'audit'],
            ];
            foreach ($links as [$href, $label, $perm]):
                $allowed = is_array($perm)
                    ? array_reduce($perm, static fn (bool $ok, string $p): bool => $ok || $can($p), false)
                    : $can($perm);
                if (!$allowed) {
                    continue;
                }
                $active = str_starts_with($currentPath, $href);
            ?>
                <a href="<?= $href ?>" class="<?= $active ? 'is-active' : '' ?>">
                    <?= $label ?>
                    <?php if ($href === '/admin/contacts' && $contacts_new > 0): ?>
                        <span class="badge"><?= $contacts_new ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
            <a href="/">Site</a>
            <a href="/admin/logout">Sair</a>
        </nav>
    </div>
    <p class="muted"><?= htmlspecialchars($auth?->userName() ? ($auth->userName() . ' · ' . ($auth->role() ?? '')) : '', ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<?php require __DIR__ . '/' . $view . '.php'; ?>
</div>
<script>
(function () {
  var meta = document.querySelector('meta[name="csrf-token"]');
  var token = meta && meta.getAttribute('content');
  if (!token) return;
  window.__CSRF_TOKEN__ = token;
  document.querySelectorAll('form').forEach(function (form) {
    if (form.querySelector('input[name="_csrf"]')) return;
    var method = (form.getAttribute('method') || 'get').toLowerCase();
    if (method !== 'post') return;
    var input = document.createElement('input');
    input.type = 'hidden';
    input.name = '_csrf';
    input.value = token;
    form.appendChild(input);
  });
})();
</script>
</body>
</html>
