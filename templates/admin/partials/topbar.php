<?php

declare(strict_types=1);

/** @var \ClubinhoPet\Identity\Domain\AuthSession|null $auth */
$auth = $auth ?? null;
$userLabel = '';
if ($auth !== null && $auth->userName()) {
    $userLabel = $auth->userName() . ' · ' . ($auth->role() ?? '');
}
?>
<header class="admin-topbar">
    <button type="button" class="admin-topbar__toggle" id="adminSidebarToggle" aria-controls="adminSidebar" aria-expanded="false" aria-label="Abrir menu">
        <span></span><span></span><span></span>
    </button>
    <div class="admin-topbar__title">
        <span class="admin-topbar__brand-mobile">Clubinho</span>
        <span class="muted admin-topbar__user"><?= htmlspecialchars($userLabel, ENT_QUOTES, 'UTF-8') ?></span>
    </div>
    <div class="admin-topbar__actions">
        <a class="admin-topbar__link" href="/">Site</a>
        <a class="admin-topbar__link" href="/admin/logout">Sair</a>
    </div>
</header>
