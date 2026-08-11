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
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #fff8f3;
            --ink: #1f2430;
            --gold: #ff6b6b;
            --teal: #2ec4b6;
            --line: #e6edf5;
            --white: #fff;
            --muted: #6b7385;
            --radius: 22px;
            --radius-sm: 14px;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Nunito, system-ui, sans-serif;
            background:
              radial-gradient(900px 420px at 0% 0%, #e8f6ff, transparent 55%),
              radial-gradient(700px 380px at 100% 0%, #e6faf3, transparent 50%),
              linear-gradient(180deg, #fff8f3 0%, #fff1e8 100%);
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
            font-family: Fraunces, Georgia, serif;
            font-size: 1.45rem; letter-spacing: .01em;
        }
        nav { display: flex; flex-wrap: wrap; gap: .35rem .85rem; align-items: center; }
        nav a {
            font-size: .88rem; color: var(--ink); font-weight: 600;
            padding: .35rem .55rem; border-radius: var(--radius-sm);
            border-bottom: 2px solid transparent;
        }
        nav a.is-active { border-color: transparent; background: color-mix(in srgb, var(--gold) 14%, white); color: var(--gold); }
        nav a:hover { color: var(--gold); text-decoration: none; }
        .badge {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 1.25rem; height: 1.25rem; padding: 0 .35rem;
            border-radius: 999px; background: var(--ink); color: #fff;
            font-size: .7rem; font-weight: 700; margin-left: .25rem;
        }
        .card {
            background: var(--white); border: 1px solid var(--line);
            border-radius: var(--radius); padding: 1.25rem; margin-bottom: 1rem;
            box-shadow: 0 10px 28px rgba(31, 36, 48, 0.05);
        }
        table { width: 100%; border-collapse: collapse; font-size: .9rem; }
        th, td { text-align: left; padding: .65rem .45rem; border-bottom: 1px solid var(--line); vertical-align: top; }
        th { font-size: .75rem; text-transform: uppercase; letter-spacing: .04em; color: var(--muted); font-weight: 700; }
        input, select, textarea, button {
            font: inherit; padding: .55rem .75rem; border: 1px solid var(--line);
            background: #fff; width: 100%; max-width: 420px; border-radius: var(--radius-sm);
        }
        button, .btn {
            background: var(--ink); color: #fff; border: 0; cursor: pointer;
            width: auto; padding: .6rem 1.1rem; border-radius: var(--radius-sm); font-weight: 700;
            transition: transform .2s ease, background .2s ease;
        }
        button:hover, .btn:hover { transform: translateY(-1px) scale(1.02); }
        .btn-secondary { background: transparent; color: var(--ink); border: 1px solid var(--ink); }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; }
        .stat { font-size: 1.85rem; font-weight: 700; line-height: 1.1; }
        .muted { color: var(--muted); font-size: .85rem; }
        .login-box { max-width: 420px; margin: 10vh auto; }
        .flash { background: #eef9f5; border: 1px solid #9ad9cb; padding: .75rem; margin-bottom: 1rem; border-radius: var(--radius-sm); }
        .error { background: #ffefef; border: 1px solid #f0b0b0; padding: .75rem; margin-bottom: 1rem; border-radius: var(--radius-sm); }
        form.inline { display: flex; gap: .5rem; align-items: center; flex-wrap: wrap; }
        form.inline input { max-width: 280px; }
        .filters { display: flex; flex-wrap: wrap; gap: .5rem; margin-bottom: 1rem; align-items: center; }
        .filters a, .chip {
            display: inline-block; padding: .4rem .8rem; border-radius: 999px;
            border: 1px solid var(--line); background: var(--white); color: var(--ink); font-size: .85rem; font-weight: 600;
        }
        .filters a.is-active, .chip.is-active { background: var(--gold); color: #fff; border-color: var(--gold); }
        .progress-row { display: grid; grid-template-columns: 9rem 1fr 3.5rem; gap: .75rem; align-items: center; margin: .45rem 0; font-size: .9rem; }
        .bar { height: .55rem; background: var(--line); border-radius: 999px; overflow: hidden; }
        .bar > span { display: block; height: 100%; background: var(--teal); border-radius: 999px; }
        .empty { padding: 1.5rem; text-align: center; color: var(--muted); }
        h1 { font-family: Fraunces, Georgia, serif; font-size: 1.75rem; margin: 0 0 1rem; font-weight: 600; }
        h2 { font-size: 1.1rem; margin: 0 0 .75rem; font-weight: 700; }
    </style>
</head>
<body>
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
                ['/admin/products', 'Produtos', 'products'],
                ['/admin/stock', 'Estoque', 'stock'],
                ['/admin/payments', 'Pagamentos', 'payments'],
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
                if (!$can($perm)) {
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
</body>
</html>
