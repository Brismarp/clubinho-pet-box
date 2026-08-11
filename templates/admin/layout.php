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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f5f4f0;
            --ink: #1a1a1a;
            --gold: #c9a961;
            --line: #e8e4dc;
            --white: #fff;
            --muted: #6b6b6b;
            --radius: 8px;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Outfit, system-ui, sans-serif;
            background: linear-gradient(180deg, #f7f5f0 0%, #f0ebe3 100%);
            color: var(--ink);
            min-height: 100vh;
        }
        a { color: var(--gold); text-decoration: none; }
        a:hover { text-decoration: underline; }
        .wrap { max-width: 1120px; margin: 0 auto; padding: 1.5rem clamp(1rem, 3vw, 1.75rem) 3rem; }
        .top {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 1.5rem; gap: 1rem; flex-wrap: wrap;
            padding-bottom: 1rem; border-bottom: 1px solid var(--line);
        }
        .brand {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 1.45rem; letter-spacing: .01em;
        }
        nav { display: flex; flex-wrap: wrap; gap: .35rem .85rem; align-items: center; }
        nav a {
            font-size: .88rem; color: var(--ink); font-weight: 500;
            padding: .25rem 0; border-bottom: 2px solid transparent;
        }
        nav a.is-active { border-color: var(--gold); }
        nav a:hover { color: var(--gold); text-decoration: none; }
        .badge {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 1.25rem; height: 1.25rem; padding: 0 .35rem;
            border-radius: 999px; background: var(--ink); color: #fff;
            font-size: .7rem; font-weight: 600; margin-left: .25rem;
        }
        .card {
            background: var(--white); border: 1px solid var(--line);
            border-radius: var(--radius); padding: 1.25rem; margin-bottom: 1rem;
        }
        table { width: 100%; border-collapse: collapse; font-size: .9rem; }
        th, td { text-align: left; padding: .65rem .45rem; border-bottom: 1px solid var(--line); vertical-align: top; }
        th { font-size: .75rem; text-transform: uppercase; letter-spacing: .04em; color: var(--muted); font-weight: 600; }
        input, select, textarea, button {
            font: inherit; padding: .5rem .65rem; border: 1px solid var(--line);
            background: #fff; width: 100%; max-width: 420px; border-radius: 6px;
        }
        button, .btn {
            background: var(--ink); color: #fff; border: 0; cursor: pointer;
            width: auto; padding: .55rem 1rem; border-radius: 6px; font-weight: 500;
        }
        .btn-secondary { background: transparent; color: var(--ink); border: 1px solid var(--ink); }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; }
        .stat { font-size: 1.85rem; font-weight: 600; line-height: 1.1; }
        .muted { color: var(--muted); font-size: .85rem; }
        .login-box { max-width: 420px; margin: 10vh auto; }
        .flash { background: #eef7ee; border: 1px solid #b7ddb7; padding: .75rem; margin-bottom: 1rem; border-radius: 6px; }
        .error { background: #fdecec; border: 1px solid #e2a0a0; padding: .75rem; margin-bottom: 1rem; border-radius: 6px; }
        form.inline { display: flex; gap: .5rem; align-items: center; flex-wrap: wrap; }
        form.inline input { max-width: 280px; }
        .filters { display: flex; flex-wrap: wrap; gap: .5rem; margin-bottom: 1rem; align-items: center; }
        .filters a, .chip {
            display: inline-block; padding: .35rem .7rem; border-radius: 6px;
            border: 1px solid var(--line); background: var(--white); color: var(--ink); font-size: .85rem;
        }
        .filters a.is-active, .chip.is-active { background: var(--ink); color: #fff; border-color: var(--ink); }
        .progress-row { display: grid; grid-template-columns: 9rem 1fr 3.5rem; gap: .75rem; align-items: center; margin: .45rem 0; font-size: .9rem; }
        .bar { height: .55rem; background: var(--line); border-radius: 999px; overflow: hidden; }
        .bar > span { display: block; height: 100%; background: var(--gold); border-radius: 999px; }
        .empty { padding: 1.5rem; text-align: center; color: var(--muted); }
        h1 { font-family: 'Playfair Display', Georgia, serif; font-size: 1.75rem; margin: 0 0 1rem; font-weight: 600; }
        h2 { font-size: 1.1rem; margin: 0 0 .75rem; font-weight: 600; }
    </style>
</head>
<body>
<div class="wrap">
<?php if ($view !== 'login'): ?>
    <div class="top">
        <div class="brand">Clubinho Pet Box</div>
        <nav>
            <?php
            $links = [
                '/admin/dashboard' => 'Dashboard',
                '/admin/plans' => 'Planos',
                '/admin/subscriptions' => 'Assinaturas',
                '/admin/boxes' => 'Boxes',
                '/admin/contacts' => 'Contatos',
                '/admin/leads' => 'Leads',
                '/admin/blog' => 'Blog',
                '/admin/testimonials' => 'Depoimentos',
                '/admin/happy-media' => 'Pets Felizes',
                '/admin/faq' => 'FAQ',
                '/admin/quizzes' => 'Quizzes',
                '/admin/products' => 'Produtos',
                '/admin/recommendations' => 'Recomendações',
                '/admin/settings' => 'Settings',
            ];
            foreach ($links as $href => $label):
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
</body>
</html>
