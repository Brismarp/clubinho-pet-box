<?php

declare(strict_types=1);

/** @var callable(string):bool $can */
/** @var string $currentPath */
/** @var int $contacts_new */

$can = $can ?? static fn (string $p): bool => true;
$currentPath = $currentPath ?? '';
$contacts_new = (int) ($contacts_new ?? 0);

$groups = [
    [
        'id' => 'overview',
        'label' => 'Visão geral',
        'items' => [
            ['/admin/dashboard', 'Dashboard', 'dashboard'],
        ],
    ],
    [
        'id' => 'ops',
        'label' => 'Operação',
        'items' => [
            ['/admin/customers', 'Clientes', 'customers'],
            ['/admin/pets', 'Pets', 'pets'],
            ['/admin/quizzes', 'Quizzes', 'quizzes'],
            ['/admin/recommendations', 'Recomendações', 'recommendations'],
            ['/admin/boxes', 'Boxes', 'boxes'],
            ['/admin/subscriptions', 'Assinaturas', 'subscriptions'],
        ],
    ],
    [
        'id' => 'catalog',
        'label' => 'Catálogo',
        'items' => [
            ['/admin/products', 'Produtos', 'products'],
            ['/admin/stock', 'Estoque', 'stock'],
            ['/admin/plans', 'Planos', 'plans'],
            ['/admin/rule-sets', 'Regras', ['content', 'recommendations']],
        ],
    ],
    [
        'id' => 'finance',
        'label' => 'Financeiro',
        'items' => [
            ['/admin/payments', 'Pagamentos', 'payments'],
            ['/admin/outbox', 'Outbox', 'outbox'],
        ],
    ],
    [
        'id' => 'content',
        'label' => 'Conteúdo',
        'items' => [
            ['/admin/blog', 'Blog', 'content'],
            ['/admin/testimonials', 'Depoimentos', 'content'],
            ['/admin/happy-media', 'Pets Felizes', 'content'],
            ['/admin/faq', 'FAQ', 'content'],
        ],
    ],
    [
        'id' => 'crm',
        'label' => 'Relacionamento',
        'items' => [
            ['/admin/contacts', 'Contatos', 'contacts'],
            ['/admin/leads', 'Leads', 'leads'],
        ],
    ],
    [
        'id' => 'system',
        'label' => 'Sistema',
        'items' => [
            ['/admin/users', 'Usuários', 'users'],
            ['/admin/settings', 'Configurações', 'settings'],
            ['/admin/audit', 'Auditoria', 'audit'],
        ],
    ],
];
?>
<aside class="admin-sidebar" id="adminSidebar" aria-label="Navegação do painel">
    <div class="admin-sidebar__brand">
        <a href="/admin/dashboard" class="admin-sidebar__logo">Clubinho Pet Box</a>
        <p class="admin-sidebar__tag">Painel</p>
    </div>
    <nav class="admin-sidebar__nav">
        <?php foreach ($groups as $group):
            $visible = [];
            foreach ($group['items'] as $item) {
                [$href, $label, $perm] = $item;
                $allowed = is_array($perm)
                    ? array_reduce($perm, static fn (bool $ok, string $p): bool => $ok || $can($p), false)
                    : $can($perm);
                if ($allowed) {
                    $visible[] = $item;
                }
            }
            if ($visible === []) {
                continue;
            }
            ?>
            <details class="admin-nav-group" data-group="<?= htmlspecialchars($group['id'], ENT_QUOTES, 'UTF-8') ?>" open>
                <summary class="admin-nav-group__label"><?= htmlspecialchars($group['label'], ENT_QUOTES, 'UTF-8') ?></summary>
                <div class="admin-nav-group__links">
                    <?php foreach ($visible as [$href, $label, $perm]):
                        $active = str_starts_with($currentPath, $href);
                        ?>
                        <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>"
                           class="admin-nav-link<?= $active ? ' is-active' : '' ?>"
                            <?= $active ? 'aria-current="page"' : '' ?>>
                            <span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                            <?php if ($href === '/admin/contacts' && $contacts_new > 0): ?>
                                <span class="badge"><?= $contacts_new ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </details>
        <?php endforeach; ?>
    </nav>
    <div class="admin-sidebar__foot">
        <a href="/" class="admin-nav-link">Ver site</a>
        <a href="/admin/logout" class="admin-nav-link">Sair</a>
    </div>
</aside>
<div class="admin-sidebar-backdrop" id="adminSidebarBackdrop" hidden></div>
