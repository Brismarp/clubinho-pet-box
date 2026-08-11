<?php

declare(strict_types=1);

/** @var string $view */
/** @var \ClubinhoPet\Identity\Domain\AuthSession|null $auth */
$auth = $auth ?? null;
$title = $title ?? 'Admin | Clubinho Pet Box';
$contacts_new = (int) ($contacts_new ?? 0);
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
$isLogin = ($view === 'login');
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
<body class="admin-body<?= $isLogin ? ' admin-body--login' : '' ?>">
<?php if ($isLogin): ?>
    <div class="admin-login-wrap admin-reveal is-visible">
        <?php require __DIR__ . '/' . $view . '.php'; ?>
    </div>
<?php else: ?>
    <div class="admin-shell">
        <?php require __DIR__ . '/partials/sidebar.php'; ?>
        <div class="admin-workspace">
            <?php require __DIR__ . '/partials/topbar.php'; ?>
            <main class="admin-main admin-reveal" id="adminMain">
                <?php require __DIR__ . '/' . $view . '.php'; ?>
            </main>
        </div>
    </div>
<?php endif; ?>
<script src="/assets/js/admin.js" defer></script>
</body>
</html>
