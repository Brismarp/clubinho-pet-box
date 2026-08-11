<?php

declare(strict_types=1);

/** @var string $pageTitle */
/** @var string $metaDescription */
/** @var string $canonicalPath */
/** @var string $ogImage */
/** @var string|null $activeNav */

use ClubinhoPet\Shared\Infrastructure\Env;

$base = rtrim(Env::get('APP_URL', 'http://localhost:8080') ?? 'http://localhost:8080', '/');
$pageTitle = $pageTitle ?? 'Clubinho Pet Box';
$metaDescription = $metaDescription ?? 'Assinatura premium de produtos selecionados para o seu pet.';
$canonicalPath = $canonicalPath ?? '/';
$ogImage = $ogImage ?? $base . '/assets/images/boxes/box-love.jpg';
$canonical = $base . $canonicalPath;
$activeNav = $activeNav ?? null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="canonical" href="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:image" content="<?= htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <a class="skip-link" href="#main">Ir para o conteúdo</a>
    <header class="header">
        <div class="header-container">
            <a href="/" class="logo-link">
                <img src="/assets/images/logo/logo.png" alt="Clubinho Pet Box" class="logo" width="176" height="56" onerror="this.src='/assets/images/logo/logo.jpg'">
            </a>
            <button class="nav-toggle" type="button" aria-label="Abrir menu" aria-expanded="false" aria-controls="primary-nav">
                <span></span><span></span><span></span>
            </button>
            <nav id="primary-nav" class="nav" aria-label="Principal">
                <a href="/" class="nav-link<?= $activeNav === 'home' ? ' is-active' : '' ?>"<?= $activeNav === 'home' ? ' aria-current="page"' : '' ?>>Início</a>
                <a href="/planos.html" class="nav-link<?= $activeNav === 'planos' ? ' is-active' : '' ?>">Planos</a>
                <a href="/blog" class="nav-link<?= $activeNav === 'blog' ? ' is-active' : '' ?>"<?= $activeNav === 'blog' ? ' aria-current="page"' : '' ?>>Blog</a>
                <a href="/sobre.html" class="nav-link<?= $activeNav === 'sobre' ? ' is-active' : '' ?>">Sobre</a>
                <a href="/quiz" class="nav-link<?= $activeNav === 'quiz' ? ' is-active' : '' ?>"<?= $activeNav === 'quiz' ? ' aria-current="page"' : '' ?>>Quiz</a>
                <a href="/contato.html" class="nav-link<?= $activeNav === 'contato' ? ' is-active' : '' ?>">Contato</a>
                <a href="/planos.html" class="btn btn-primary">Assinar</a>
            </nav>
        </div>
    </header>
    <main id="main">
