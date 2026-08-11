<?php

declare(strict_types=1);

namespace ClubinhoPet\Ui;

use ClubinhoPet\Identity\Domain\AuthSession;
use ClubinhoPet\Shared\Domain\DomainException;
use ClubinhoPet\Shared\Infrastructure\AppContainer;
use ClubinhoPet\Shared\Infrastructure\Env;

final class HttpKernel
{
    public function __construct(private AppContainer $app)
    {
    }

    public function handle(string $method, string $path): void
    {
        $path = '/' . trim($path, '/');
        if ($path === '/') {
            header('Location: /index.html');
            exit;
        }

        try {
            match (true) {
                $method === 'GET' && $path === '/api/v1/plans' => $this->json($this->app->listPlans()->handle()),
                $method === 'GET' && preg_match('#^/api/v1/plans/([a-z0-9\-]+)$#', $path, $m) === 1
                    => $this->json($this->app->getPlanBySlug()->handle($m[1])),
                $method === 'GET' && $path === '/api/v1/config'
                    => $this->json([
                        'success' => true,
                        'data' => $this->app->getPublicSettings()->handle(),
                    ]),
                $method === 'GET' && $path === '/api/config.php'
                    => $this->json([
                        'success' => true,
                        'data' => $this->app->getPublicSettings()->handle(),
                    ]),
                $method === 'GET' && $path === '/checkout' => $this->checkout(),
                $method === 'GET' && $path === '/sitemap.xml' => $this->sitemap(),
                $method === 'GET' && $path === '/sitemap.php' => $this->sitemapRedirect(),
                $method === 'GET' && $path === '/blog' => $this->blogIndex(),
                $method === 'GET' && preg_match('#^/blog/([a-z0-9\-]+)$#', $path, $m) === 1
                    => $this->blogShow($m[1]),
                $method === 'POST' && $path === '/api/v1/events' => $this->ingestEvents(),
                $method === 'POST' && $path === '/subscribe' => $this->subscribe(),
                $method === 'POST' && $path === '/contact' => $this->contact(),
                $method === 'POST' && $path === '/lead' => $this->lead(),

                $method === 'GET' && $path === '/admin' => $this->adminRedirect('/admin/dashboard'),
                $method === 'GET' && $path === '/admin/login' => $this->renderAdmin('login', [
                    'error' => isset($_GET['error']),
                ], false),
                $method === 'POST' && $path === '/admin/login' => $this->adminLogin(),
                $method === 'POST' && $path === '/admin/logout' => $this->adminLogout(),
                $method === 'GET' && $path === '/admin/logout' => $this->adminLogout(),

                $method === 'GET' && $path === '/admin/dashboard' => $this->adminDashboard(),
                $method === 'GET' && $path === '/admin/plans' => $this->adminPlans(),
                $method === 'POST' && $path === '/admin/plans/checkout-url' => $this->adminUpdatePlanUrl(),
                $method === 'GET' && $path === '/admin/subscriptions' => $this->adminSubscriptions(),
                $method === 'GET' && $path === '/admin/settings' => $this->adminSettings(),
                $method === 'POST' && $path === '/admin/settings' => $this->adminUpdateSetting(),
                $method === 'GET' && $path === '/admin/boxes' => $this->adminBoxes(),
                $method === 'POST' && $path === '/admin/boxes' => $this->adminCreateBox(),
                $method === 'POST' && $path === '/admin/boxes/ship' => $this->adminShipBox(),
                $method === 'GET' && $path === '/admin/contacts' => $this->adminContacts(),
                $method === 'POST' && $path === '/admin/contacts/read' => $this->adminMarkContactRead(),
                $method === 'GET' && $path === '/admin/leads' => $this->adminLeads(),
                $method === 'GET' && $path === '/admin/blog' => $this->adminBlog(),
                $method === 'GET' && $path === '/admin/blog/new' => $this->adminBlogForm(null),
                $method === 'POST' && $path === '/admin/blog' => $this->adminBlogSave(null),
                $method === 'GET' && preg_match('#^/admin/blog/(\d+)/edit$#', $path, $m) === 1
                    => $this->adminBlogForm((int) $m[1]),
                $method === 'POST' && preg_match('#^/admin/blog/(\d+)$#', $path, $m) === 1
                    => $this->adminBlogSave((int) $m[1]),

                $method === 'GET' && $path === '/api/v1/admin/plans' => $this->apiAuthJson(fn () => $this->app->listPlans()->handle()),
                $method === 'GET' && $path === '/api/v1/admin/subscriptions' => $this->apiAuthJson(fn () => $this->app->listSubscriptions()->handle()),
                $method === 'GET' && $path === '/api/v1/admin/settings' => $this->apiAuthJson(fn () => $this->app->listSettings()->handle()),
                $method === 'PUT' && $path === '/api/v1/admin/settings' => $this->apiUpdateSetting(),
                $method === 'GET' && $path === '/api/v1/admin/boxes' => $this->apiAuthJson(fn () => $this->app->listBoxes()->handle()),
                $method === 'POST' && $path === '/api/v1/admin/boxes' => $this->apiCreateBox(),
                $method === 'POST' && $path === '/api/v1/admin/boxes/ship' => $this->apiShipBox(),
                $method === 'GET' && $path === '/api/v1/admin/contacts' => $this->apiAuthJson(fn () => $this->app->listContacts()->handle()),
                $method === 'POST' && $path === '/api/v1/admin/contacts/read' => $this->apiMarkContactRead(),
                $method === 'POST' && $path === '/api/v1/admin/plans/checkout-url' => $this->apiUpdatePlanUrl(),

                default => $this->notFound(),
            };
        } catch (DomainException $e) {
            $this->json(['error' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Internal server error', 'detail' => $e->getMessage()], 500);
        }
    }

    private function auth(): AuthSession
    {
        return $this->app->auth();
    }

    private function requireAdmin(): void
    {
        if (!$this->auth()->isLoggedIn()) {
            if (str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
                $this->json(['error' => 'Unauthorized'], 401);
                exit;
            }
            header('Location: /admin/login');
            exit;
        }
    }

    private function checkout(): void
    {
        $plan = (string) ($_GET['plan'] ?? '');
        $billing = strtolower((string) ($_GET['billing'] ?? 'monthly'));
        if (!in_array($billing, ['monthly', 'annual'], true)) {
            $billing = 'monthly';
        }

        // Annual SKUs/checkout URLs can be added later as dedicated plans.
        // For now billing is accepted/logged and we use the plan slug checkout URL.
        error_log(sprintf('checkout plan=%s billing=%s', $plan, $billing));

        $this->recordServerEvent('checkout_click', '/checkout', $plan, null, [
            'billing' => $billing,
            'source' => 'server',
        ]);

        $result = $this->app->startCheckout()->handle(
            new \ClubinhoPet\Subscription\Application\StartCheckout\StartCheckoutCommand($plan)
        );
        header('Location: ' . $result->checkoutUrl);
        exit;
    }

    private function sitemapRedirect(): void
    {
        header('Location: /sitemap.xml', true, 301);
        exit;
    }

    private function sitemap(): void
    {
        $base = rtrim(Env::get('APP_URL', 'http://localhost:8080') ?? 'http://localhost:8080', '/');
        $today = date('Y-m-d');

        $urls = [
            ['loc' => $base . '/', 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['loc' => $base . '/planos.html', 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => $base . '/blog', 'priority' => '0.85', 'changefreq' => 'weekly'],
            ['loc' => $base . '/sobre.html', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => $base . '/contato.html', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => $base . '/quiz.html', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => $base . '/assinatura.html', 'priority' => '0.8', 'changefreq' => 'monthly'],
        ];

        try {
            foreach ($this->app->listPlans()->handle() as $plan) {
                $slug = (string) ($plan['slug'] ?? '');
                if ($slug === '') {
                    continue;
                }
                $urls[] = [
                    'loc' => $base . '/assinatura.html?plan=' . rawurlencode($slug),
                    'priority' => '0.6',
                    'changefreq' => 'weekly',
                ];
            }
        } catch (\Throwable) {
            // Pages-only sitemap if catalog is unavailable.
        }

        try {
            foreach ($this->app->listPublishedPosts()->handle(200) as $post) {
                $slug = (string) ($post['slug'] ?? '');
                if ($slug === '') {
                    continue;
                }
                $urls[] = [
                    'loc' => $base . '/blog/' . rawurlencode($slug),
                    'priority' => '0.7',
                    'changefreq' => 'monthly',
                ];
            }
        } catch (\Throwable) {
            // skip blog if unavailable
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $entry) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . htmlspecialchars($entry['loc'], ENT_XML1) . "</loc>\n";
            $xml .= '    <lastmod>' . $today . "</lastmod>\n";
            $xml .= '    <changefreq>' . $entry['changefreq'] . "</changefreq>\n";
            $xml .= '    <priority>' . $entry['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';

        http_response_code(200);
        header('Content-Type: application/xml; charset=utf-8');
        echo $xml;
        exit;
    }

    private function subscribe(): void
    {
        $str = static fn (string $key): ?string => isset($_POST[$key]) && $_POST[$key] !== '' ? (string) $_POST[$key] : null;

        $billing = strtolower((string) ($_POST['billing'] ?? 'monthly'));
        if (!in_array($billing, ['monthly', 'annual'], true)) {
            $billing = 'monthly';
        }

        $address = $str('address');
        $addressNote = $address !== null
            ? $address . ' | billing: ' . $billing
            : 'billing: ' . $billing;

        $plan = (string) ($_POST['plan'] ?? '');

        $result = $this->app->registerInterest()->handle(
            new \ClubinhoPet\Subscription\Application\RegisterInterest\RegisterInterestCommand(
                planSlug: $plan,
                name: (string) ($_POST['name'] ?? ''),
                email: (string) ($_POST['email'] ?? ''),
                petName: (string) ($_POST['pet_name'] ?? ''),
                petType: $str('pet_type'),
                petAge: isset($_POST['pet_age']) && $_POST['pet_age'] !== '' ? (int) $_POST['pet_age'] : null,
                petBreed: $str('pet_breed'),
                dietaryRestrictions: $str('dietary_restrictions'),
                phone: $str('phone'),
                address: $addressNote,
            )
        );

        $this->recordServerEvent('subscribe_submit', '/subscribe', $plan, (string) ($_POST['email'] ?? ''), [
            'billing' => $billing,
            'source' => 'server',
        ]);

        if ($this->wantsJson()) {
            $result['billing'] = $billing;
            $this->json($result, 201);
            return;
        }

        header('Location: /checkout?plan=' . urlencode($plan) . '&billing=' . urlencode($billing));
        exit;
    }

    private function contact(): void
    {
        $name = (string) ($_POST['name'] ?? $_POST['nome'] ?? '');
        $email = (string) ($_POST['email'] ?? '');
        $subject = (string) ($_POST['subject'] ?? $_POST['assunto'] ?? '');
        $message = (string) ($_POST['message'] ?? $_POST['mensagem'] ?? '');

        $created = $this->app->submitContact()->handle($name, $email, $subject, $message);

        $this->recordServerEvent('contact_submit', '/contato.html', null, $email, [
            'source' => 'server',
        ]);

        if ($this->wantsJson()) {
            $this->json($created, 201);
            return;
        }

        header('Location: /contato.html?sent=1');
        exit;
    }

    private function lead(): void
    {
        $data = $_POST !== [] ? $_POST : $this->jsonBody();
        $name = (string) ($data['name'] ?? '');
        $email = (string) ($data['email'] ?? '');
        $phone = (string) ($data['phone'] ?? '');
        $petType = (string) ($data['pet_type'] ?? '');
        $city = isset($data['city']) && $data['city'] !== '' ? (string) $data['city'] : null;
        $visitorId = isset($data['visitor_id']) && $data['visitor_id'] !== '' ? (string) $data['visitor_id'] : null;
        $accept = isset($data['accept_newsletter']) && (string) $data['accept_newsletter'] !== '0' && (string) $data['accept_newsletter'] !== '';

        $created = $this->app->submitLead()->handle($name, $email, $phone, $petType, $city, $visitorId, (bool) $accept);

        $this->recordServerEvent('lead_submit', '/', null, $email, [
            'source' => 'server',
            'pet_type' => $petType,
        ], $visitorId);

        $this->json(['ok' => true] + $created, 201);
    }

    private function ingestEvents(): void
    {
        $payload = $this->jsonBody();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $ipHash = $ip !== '' ? hash('sha256', $ip) : null;
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $result = $this->app->ingestEvents()->handle($payload, $ipHash, $ua);
        $this->json(['ok' => true] + $result);
    }

    /**
     * @param array<string, mixed>|null $properties
     */
    private function recordServerEvent(
        string $eventName,
        ?string $path = null,
        ?string $planSlug = null,
        ?string $email = null,
        ?array $properties = null,
        ?string $visitorId = null,
    ): void {
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $this->app->ingestEvents()->recordServer(
                eventName: $eventName,
                path: $path,
                planSlug: $planSlug,
                email: $email,
                properties: $properties,
                visitorId: $visitorId,
                sessionId: null,
                ipHash: $ip !== '' ? hash('sha256', $ip) : null,
                userAgent: $_SERVER['HTTP_USER_AGENT'] ?? null,
            );
        } catch (\Throwable) {
            // fail-soft
        }
    }

    private function adminLogin(): void
    {
        try {
            $this->app->loginAdmin()->handle(
                (string) ($_POST['username'] ?? $_POST['email'] ?? ''),
                (string) ($_POST['password'] ?? ''),
            );
            header('Location: /admin/dashboard');
            exit;
        } catch (DomainException) {
            header('Location: /admin/login?error=1');
            exit;
        }
    }

    private function adminLogout(): void
    {
        $this->app->logoutAdmin()->handle();
        header('Location: /admin/login');
        exit;
    }

    private function adminDashboard(): void
    {
        $this->requireAdmin();
        $range = (int) ($_GET['range'] ?? 30);
        $metrics = $this->app->dashboardMetrics()->handle($range);
        $this->renderAdmin('dashboard', [
            'metrics' => $metrics,
            'contacts_new' => $this->app->listContacts()->countNew(),
            'user' => $this->auth()->userName(),
        ]);
    }

    private function adminPlans(): void
    {
        $this->requireAdmin();
        $this->renderAdmin('plans', [
            'plans' => $this->app->listPlans()->handle(),
            'contacts_new' => $this->app->listContacts()->countNew(),
        ]);
    }

    private function adminUpdatePlanUrl(): void
    {
        $this->requireAdmin();
        $this->app->updatePlanCheckoutUrl()->handle((int) ($_POST['plan_id'] ?? 0), (string) ($_POST['checkout_url'] ?? ''));
        header('Location: /admin/plans?updated=1');
        exit;
    }

    private function adminSubscriptions(): void
    {
        $this->requireAdmin();
        $status = isset($_GET['status']) ? (string) $_GET['status'] : null;
        if ($status === '' || $status === 'all') {
            $status = null;
        }
        $this->renderAdmin('subscriptions', [
            'items' => $this->app->listSubscriptions()->handle(100, $status),
            'status' => $status ?? 'all',
            'contacts_new' => $this->app->listContacts()->countNew(),
        ]);
    }

    private function adminSettings(): void
    {
        $this->requireAdmin();
        $this->renderAdmin('settings', ['settings' => $this->app->listSettings()->handle()]);
    }

    private function adminUpdateSetting(): void
    {
        $this->requireAdmin();
        $this->app->updateSetting()->handle(
            (string) ($_POST['key'] ?? ''),
            (string) ($_POST['value'] ?? ''),
            $this->auth()->userId(),
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
        );
        header('Location: /admin/settings?updated=1');
        exit;
    }

    private function adminBoxes(): void
    {
        $this->requireAdmin();
        $this->renderAdmin('boxes', [
            'boxes' => $this->app->listBoxes()->handle(),
            'subscriptions' => $this->app->listSubscriptions()->handle(),
            'status_counts' => $this->app->boxRepository()->countByStatus(),
            'contacts_new' => $this->app->listContacts()->countNew(),
        ]);
    }

    private function adminCreateBox(): void
    {
        $this->requireAdmin();
        $this->app->createBox()->handle(
            (int) ($_POST['subscription_id'] ?? 0),
            isset($_POST['box_number']) && $_POST['box_number'] !== '' ? (int) $_POST['box_number'] : null,
            isset($_POST['notes']) && $_POST['notes'] !== '' ? (string) $_POST['notes'] : null,
        );
        header('Location: /admin/boxes?created=1');
        exit;
    }

    private function adminShipBox(): void
    {
        $this->requireAdmin();
        $this->app->shipBox()->handle(
            (int) ($_POST['box_id'] ?? 0),
            isset($_POST['tracking_number']) && $_POST['tracking_number'] !== '' ? (string) $_POST['tracking_number'] : null,
        );
        header('Location: /admin/boxes?shipped=1');
        exit;
    }

    private function adminContacts(): void
    {
        $this->requireAdmin();
        $filter = isset($_GET['status']) ? (string) $_GET['status'] : 'new';
        $status = ($filter === 'all' || $filter === '') ? null : $filter;
        $this->renderAdmin('contacts', [
            'items' => $this->app->listContacts()->handle(100, $status),
            'status' => $filter === '' ? 'new' : $filter,
            'contacts_new' => $this->app->listContacts()->countNew(),
        ]);
    }

    private function adminLeads(): void
    {
        $this->requireAdmin();
        $this->renderAdmin('leads', [
            'items' => $this->app->listLeads()->handle(),
            'contacts_new' => $this->app->listContacts()->countNew(),
        ]);
    }

    private function blogIndex(): void
    {
        $posts = $this->app->listPublishedPosts()->handle();
        require BASE_PATH . '/templates/blog/index.php';
    }

    private function blogShow(string $slug): void
    {
        try {
            $post = $this->app->getPublishedPost()->handle($slug);
        } catch (DomainException) {
            http_response_code(404);
            echo '404 Post não encontrado';
            return;
        }
        require BASE_PATH . '/templates/blog/show.php';
    }

    private function adminBlog(): void
    {
        $this->requireAdmin();
        $this->renderAdmin('blog', [
            'items' => $this->app->listAdminPosts()->handle(),
            'flash' => isset($_GET['saved']),
        ]);
    }

    private function adminBlogForm(?int $id): void
    {
        $this->requireAdmin();
        $post = null;
        if ($id !== null) {
            $post = $this->app->getAdminPost()->handle($id);
        }
        $this->renderAdmin('blog_form', [
            'post' => $post,
            'error' => null,
        ]);
    }

    private function adminBlogSave(?int $id): void
    {
        $this->requireAdmin();
        try {
            $this->app->savePost()->handle($_POST, $id);
            header('Location: /admin/blog?saved=1');
            exit;
        } catch (DomainException $e) {
            $post = $_POST;
            $post['id'] = $id;
            $this->renderAdmin('blog_form', [
                'post' => $post,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function adminMarkContactRead(): void
    {
        $this->requireAdmin();
        $this->app->markContactRead()->handle((int) ($_POST['id'] ?? 0));
        header('Location: /admin/contacts?updated=1');
        exit;
    }

    private function apiAuthJson(callable $fn): void
    {
        $this->requireAdmin();
        $this->json($fn());
    }

    private function apiUpdateSetting(): void
    {
        $this->requireAdmin();
        $body = $this->jsonBody();
        $this->app->updateSetting()->handle(
            (string) ($body['key'] ?? ''),
            (string) ($body['value'] ?? ''),
            $this->auth()->userId(),
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
        );
        $this->json(['ok' => true]);
    }

    private function apiCreateBox(): void
    {
        $this->requireAdmin();
        $body = $this->jsonBody();
        $created = $this->app->createBox()->handle(
            (int) ($body['subscription_id'] ?? 0),
            isset($body['box_number']) ? (int) $body['box_number'] : null,
            isset($body['notes']) ? (string) $body['notes'] : null,
        );
        $this->json($created, 201);
    }

    private function apiShipBox(): void
    {
        $this->requireAdmin();
        $body = $this->jsonBody();
        $this->json($this->app->shipBox()->handle(
            (int) ($body['box_id'] ?? 0),
            isset($body['tracking_number']) ? (string) $body['tracking_number'] : null,
        ));
    }

    private function apiMarkContactRead(): void
    {
        $this->requireAdmin();
        $body = $this->jsonBody();
        $this->json($this->app->markContactRead()->handle((int) ($body['id'] ?? 0)));
    }

    private function apiUpdatePlanUrl(): void
    {
        $this->requireAdmin();
        $body = $this->jsonBody();
        $this->app->updatePlanCheckoutUrl()->handle((int) ($body['plan_id'] ?? 0), (string) ($body['checkout_url'] ?? ''));
        $this->json(['ok' => true]);
    }

    private function adminRedirect(string $to): void
    {
        header('Location: ' . $to);
        exit;
    }

    /** @param array<string, mixed> $data */
    private function renderAdmin(string $view, array $data = [], bool $requireAuth = true): void
    {
        if ($requireAuth) {
            $this->requireAdmin();
        }
        if (!isset($data['contacts_new']) && $requireAuth) {
            try {
                $data['contacts_new'] = $this->app->listContacts()->countNew();
            } catch (\Throwable) {
                $data['contacts_new'] = 0;
            }
        }
        extract($data, EXTR_SKIP);
        $auth = $this->auth();
        require BASE_PATH . '/templates/admin/layout.php';
    }

    /** @param array<string, mixed>|list<mixed> $data */
    private function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /** @return array<string, mixed> */
    private function jsonBody(): array
    {
        $raw = file_get_contents('php://input') ?: '';
        if ($raw === '') {
            return $_POST;
        }
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function wantsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $xhr = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';

        return str_contains($accept, 'application/json') || strtolower($xhr) === 'xmlhttprequest';
    }

    private function notFound(): void
    {
        if (str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
            $this->json(['error' => 'Not found'], 404);
            return;
        }
        http_response_code(404);
        echo '404 Not Found';
    }
}
