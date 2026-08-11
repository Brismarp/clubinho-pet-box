<?php

declare(strict_types=1);

/** @var string $view */
/** @var \ClubinhoPet\Identity\Domain\AuthSession|null $auth */
$auth = $auth ?? null;
$title = $title ?? 'Admin | Clubinho Pet Box';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        :root { --bg:#f5f4f0; --ink:#1a1a1a; --gold:#c9a961; --line:#e8e4dc; --white:#fff; }
        * { box-sizing: border-box; }
        body { margin:0; font-family: Georgia, 'Times New Roman', serif; background:var(--bg); color:var(--ink); }
        a { color: var(--gold); text-decoration: none; }
        .wrap { max-width: 1100px; margin: 0 auto; padding: 1.5rem; }
        .top { display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; gap:1rem; flex-wrap:wrap; }
        .brand { font-size: 1.5rem; letter-spacing: .02em; }
        nav a { margin-right: 1rem; font-family: system-ui, sans-serif; font-size: .9rem; color: var(--ink); }
        nav a:hover { color: var(--gold); }
        .card { background:var(--white); border:1px solid var(--line); padding:1.25rem; margin-bottom:1rem; }
        table { width:100%; border-collapse: collapse; font-family: system-ui, sans-serif; font-size:.9rem; }
        th, td { text-align:left; padding:.6rem .4rem; border-bottom:1px solid var(--line); vertical-align: top; }
        input, select, textarea, button { font: inherit; padding:.5rem .65rem; border:1px solid var(--line); background:#fff; width:100%; max-width:420px; }
        button, .btn { background:var(--ink); color:#fff; border:0; cursor:pointer; width:auto; padding:.55rem 1rem; font-family: system-ui, sans-serif; }
        .btn-secondary { background: transparent; color: var(--ink); border:1px solid var(--ink); }
        .grid { display:grid; grid-template-columns: repeat(auto-fit,minmax(180px,1fr)); gap:1rem; }
        .stat { font-size:2rem; font-family: system-ui, sans-serif; }
        .muted { color:#666; font-family: system-ui, sans-serif; font-size:.85rem; }
        .login-box { max-width:420px; margin: 10vh auto; }
        .flash { background:#eef7ee; border:1px solid #b7ddb7; padding:.75rem; margin-bottom:1rem; font-family:system-ui,sans-serif; }
        .error { background:#fdecec; border:1px solid #e2a0a0; padding:.75rem; margin-bottom:1rem; font-family:system-ui,sans-serif; }
        form.inline { display:flex; gap:.5rem; align-items:center; flex-wrap:wrap; }
        form.inline input { max-width:280px; }
    </style>
</head>
<body>
<div class="wrap">
<?php if ($view !== 'login'): ?>
    <div class="top">
        <div class="brand">Clubinho Pet Box</div>
        <nav>
            <a href="/admin/dashboard">Dashboard</a>
            <a href="/admin/plans">Planos</a>
            <a href="/admin/subscriptions">Assinaturas</a>
            <a href="/admin/boxes">Boxes</a>
            <a href="/admin/contacts">Contatos</a>
            <a href="/admin/settings">Settings</a>
            <a href="/">Site</a>
            <a href="/admin/logout">Sair</a>
        </nav>
    </div>
    <p class="muted"><?= htmlspecialchars($auth?->userName() ? ($auth->userName() . ' · ' . ($auth->role() ?? '')) : '', ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<?php require __DIR__ . '/' . $view . '.php'; ?>
</div>
</body>
</html>
