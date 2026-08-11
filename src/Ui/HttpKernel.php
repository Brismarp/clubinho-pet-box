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
            $this->homePage();
            return;
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
                $method === 'GET' && $path === '/quiz' => $this->quizPage(),
                $method === 'GET' && $path === '/planos' => $this->marketingPage('planos'),
                $method === 'GET' && $path === '/sobre' => $this->marketingPage('sobre'),
                $method === 'GET' && $path === '/contato' => $this->marketingPage('contato'),
                $method === 'GET' && $path === '/assinatura' => $this->marketingPage('assinatura'),
                $method === 'GET' && $path === '/privacidade' => $this->marketingPage('privacidade'),
                $method === 'GET' && $path === '/termos' => $this->marketingPage('termos'),
                $method === 'POST' && $path === '/webhooks/kirvano' => $this->kirvanoWebhook(),
                $method === 'POST' && $path === '/api/v1/quiz/sessions' => $this->quizStart(),
                $method === 'GET' && preg_match('#^/api/v1/quiz/sessions/([a-f0-9]+)$#', $path, $m) === 1
                    => $this->json($this->app->quiz()->getSession($m[1])),
                $method === 'PATCH' && preg_match('#^/api/v1/quiz/sessions/([a-f0-9]+)$#', $path, $m) === 1
                    => $this->json($this->app->quiz()->saveStep($m[1], $this->jsonBody())),
                $method === 'POST' && preg_match('#^/api/v1/quiz/sessions/([a-f0-9]+)/save$#', $path, $m) === 1
                    => $this->json($this->app->quiz()->saveStep($m[1], $this->jsonBody())),
                $method === 'POST' && preg_match('#^/api/v1/quiz/sessions/([a-f0-9]+)/complete$#', $path, $m) === 1
                    => $this->json($this->app->quiz()->complete($m[1], $this->jsonBody())),
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
                $method === 'GET' && $path === '/admin/audit' => $this->adminAudit(),
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

                $method === 'GET' && $path === '/admin/testimonials' => $this->adminTestimonials(),
                $method === 'GET' && $path === '/admin/testimonials/new' => $this->adminTestimonialForm(null),
                $method === 'POST' && $path === '/admin/testimonials' => $this->adminTestimonialSave(null),
                $method === 'GET' && preg_match('#^/admin/testimonials/(\d+)/edit$#', $path, $m) === 1
                    => $this->adminTestimonialForm((int) $m[1]),
                $method === 'POST' && preg_match('#^/admin/testimonials/(\d+)$#', $path, $m) === 1
                    => $this->adminTestimonialSave((int) $m[1]),

                $method === 'GET' && $path === '/admin/happy-media' => $this->adminHappyMedia(),
                $method === 'GET' && $path === '/admin/happy-media/new' => $this->adminHappyMediaForm(null),
                $method === 'POST' && $path === '/admin/happy-media' => $this->adminHappyMediaSave(null),
                $method === 'GET' && preg_match('#^/admin/happy-media/(\d+)/edit$#', $path, $m) === 1
                    => $this->adminHappyMediaForm((int) $m[1]),
                $method === 'POST' && preg_match('#^/admin/happy-media/(\d+)$#', $path, $m) === 1
                    => $this->adminHappyMediaSave((int) $m[1]),

                $method === 'GET' && $path === '/admin/faq' => $this->adminFaq(),
                $method === 'GET' && $path === '/admin/faq/new' => $this->adminFaqForm(null),
                $method === 'POST' && $path === '/admin/faq' => $this->adminFaqSave(null),
                $method === 'GET' && preg_match('#^/admin/faq/(\d+)/edit$#', $path, $m) === 1
                    => $this->adminFaqForm((int) $m[1]),
                $method === 'POST' && preg_match('#^/admin/faq/(\d+)$#', $path, $m) === 1
                    => $this->adminFaqSave((int) $m[1]),

                $method === 'GET' && $path === '/admin/quizzes' => $this->adminQuizzes(),
                $method === 'GET' && preg_match('#^/admin/quizzes/(\d+)$#', $path, $m) === 1
                    => $this->adminQuizDetail((int) $m[1]),
                $method === 'GET' && $path === '/admin/products' => $this->adminProducts(),
                $method === 'GET' && $path === '/admin/products/new' => $this->adminProductForm(null),
                $method === 'POST' && $path === '/admin/products' => $this->adminProductSave(null),
                $method === 'GET' && preg_match('#^/admin/products/(\d+)/edit$#', $path, $m) === 1
                    => $this->adminProductForm((int) $m[1]),
                $method === 'POST' && preg_match('#^/admin/products/(\d+)$#', $path, $m) === 1
                    => $this->adminProductSave((int) $m[1]),
                $method === 'GET' && $path === '/admin/recommendations' => $this->adminRecommendations(),
                $method === 'GET' && preg_match('#^/admin/recommendations/(\d+)$#', $path, $m) === 1
                    => $this->adminRecommendationDetail((int) $m[1]),
                $method === 'POST' && preg_match('#^/admin/recommendations/(\d+)/status$#', $path, $m) === 1
                    => $this->adminRecommendationStatus((int) $m[1]),
                $method === 'POST' && $path === '/admin/recommendations/item' => $this->adminRecommendationItemToggle(),

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
            ['loc' => $base . '/planos', 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => $base . '/blog', 'priority' => '0.85', 'changefreq' => 'weekly'],
            ['loc' => $base . '/sobre', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => $base . '/contato', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => $base . '/quiz', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => $base . '/assinatura', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => $base . '/privacidade', 'priority' => '0.3', 'changefreq' => 'yearly'],
            ['loc' => $base . '/termos', 'priority' => '0.3', 'changefreq' => 'yearly'],
        ];

        try {
            foreach ($this->app->listPlans()->handle() as $plan) {
                $slug = (string) ($plan['slug'] ?? '');
                if ($slug === '') {
                    continue;
                }
                $urls[] = [
                    'loc' => $base . '/assinatura?plan=' . rawurlencode($slug),
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

        header('Location: /contato?sent=1');
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
            $this->auth()->userId(),
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
        $related = [];
        try {
            foreach ($this->app->listPublishedPosts()->handle(12) as $row) {
                if ((string) ($row['slug'] ?? '') === $slug) {
                    continue;
                }
                $related[] = $row;
                if (count($related) >= 3) {
                    break;
                }
            }
        } catch (\Throwable) {
            $related = [];
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

    private function homePage(): void
    {
        $home = $this->app->homeContent();
        $testimonials = $home->publishedTestimonials();
        $happyMedia = $home->approvedHappyMedia();
        $faqs = $home->publishedFaqs();
        require BASE_PATH . '/templates/site/home.php';
    }

    private function quizPage(): void
    {
        $activeNav = 'quiz';
        $pageTitle = 'Quiz | Clubinho Pet Box';
        $metaDescription = 'Quiz inteligente: responda sobre o perfil do pet e descubra a box ideal.';
        $canonicalPath = '/quiz';
        require BASE_PATH . '/templates/site/quiz.php';
    }

    private function marketingPage(string $name): void
    {
        $file = BASE_PATH . '/templates/site/' . $name . '.php';
        if (!is_file($file)) {
            $this->notFound();
            return;
        }
        require $file;
    }

    private function kirvanoWebhook(): void
    {
        $secret = Env::get('KIRVANO_WEBHOOK_SECRET', '') ?? '';
        if ($secret !== '') {
            $provided = (string) ($_SERVER['HTTP_X_KIRVANO_SECRET']
                ?? $_SERVER['HTTP_X_WEBHOOK_SECRET']
                ?? '');
            if (!hash_equals($secret, $provided)) {
                $this->json(['error' => 'Unauthorized'], 401);
                return;
            }
        }
        $payload = $this->jsonBody();
        $headers = [];
        foreach ($_SERVER as $k => $v) {
            if (str_starts_with($k, 'HTTP_') && is_string($v)) {
                $headers[$k] = $v;
            }
        }
        $result = $this->app->kirvanoWebhook()->handle($payload, $headers);
        $this->json($result);
    }

    private function adminAudit(): void
    {
        $this->requireAdmin();
        $this->renderAdmin('audit', [
            'items' => $this->app->activityLogRepository()->recent(150),
        ]);
    }

    private function quizStart(): void
    {
        $body = $this->jsonBody();
        $token = isset($body['token']) ? (string) $body['token'] : null;
        $this->json($this->app->quiz()->start($token ?: null), 201);
    }

    private function adminQuizzes(): void
    {
        $this->requireAdmin();
        $this->renderAdmin('quizzes', ['items' => $this->app->quiz()->adminListSessions()]);
    }

    private function adminQuizDetail(int $id): void
    {
        $this->requireAdmin();
        $this->renderAdmin('quiz_detail', ['item' => $this->app->quiz()->adminSessionDetail($id)]);
    }

    private function adminProducts(): void
    {
        $this->requireAdmin();
        $this->renderAdmin('products', ['items' => $this->app->quiz()->adminListProducts()]);
    }

    private function adminProductForm(?int $id): void
    {
        $this->requireAdmin();
        $item = $id !== null ? $this->app->quiz()->adminGetProduct($id) : null;
        if ($id !== null && $item === null) {
            throw new DomainException('Product not found.');
        }
        $this->renderAdmin('product_form', ['item' => $item, 'error' => null]);
    }

    private function adminProductSave(?int $id): void
    {
        $this->requireAdmin();
        try {
            if (!isset($_POST['is_available'])) {
                $_POST['is_available'] = '0';
            }
            $this->app->quiz()->adminSaveProduct($_POST, $id);
            header('Location: /admin/products?saved=1');
            exit;
        } catch (DomainException $e) {
            $item = $_POST;
            $item['id'] = $id;
            $this->renderAdmin('product_form', ['item' => $item, 'error' => $e->getMessage()]);
        }
    }

    private function adminRecommendations(): void
    {
        $this->requireAdmin();
        $status = isset($_GET['status']) ? (string) $_GET['status'] : 'pending_review';
        if ($status === 'all') {
            $status = null;
        }
        $this->renderAdmin('recommendations', [
            'items' => $this->app->quiz()->adminListRecommendations($status),
            'status' => $status ?? 'all',
        ]);
    }

    private function adminRecommendationDetail(int $id): void
    {
        $this->requireAdmin();
        $this->renderAdmin('recommendation_detail', [
            'item' => $this->app->quiz()->adminRecommendationDetail($id),
        ]);
    }

    private function adminRecommendationStatus(int $id): void
    {
        $this->requireAdmin();
        $status = (string) ($_POST['status'] ?? '');
        $this->app->quiz()->adminSetRecommendationStatus($id, $status, $this->auth()->userId());
        header('Location: /admin/recommendations/' . $id . '?saved=1');
        exit;
    }

    private function adminRecommendationItemToggle(): void
    {
        $this->requireAdmin();
        $itemId = (int) ($_POST['item_id'] ?? 0);
        $selected = ((string) ($_POST['is_selected'] ?? '1')) === '1';
        $recId = (int) ($_POST['recommendation_id'] ?? 0);
        $this->app->quiz()->adminToggleItem($itemId, $selected);
        header('Location: /admin/recommendations/' . $recId . '?saved=1');
        exit;
    }

    private function adminTestimonials(): void
    {
        $this->requireAdmin();
        $this->renderAdmin('testimonials', ['items' => $this->app->homeContent()->allTestimonials()]);
    }

    private function adminTestimonialForm(?int $id): void
    {
        $this->requireAdmin();
        $item = null;
        if ($id !== null) {
            foreach ($this->app->homeContent()->allTestimonials() as $row) {
                if ((int) $row['id'] === $id) {
                    $item = $row;
                    break;
                }
            }
            if ($item === null) {
                throw new DomainException('Testimonial not found.');
            }
        }
        $this->renderAdmin('testimonial_form', ['item' => $item, 'error' => null]);
    }

    private function adminTestimonialSave(?int $id): void
    {
        $this->requireAdmin();
        try {
            $data = $_POST;
            $uploaded = $this->storeUpload('photo_file', 'testimonials');
            if ($uploaded !== null) {
                $data['photo_path'] = $uploaded;
            }
            $this->app->homeContent()->saveTestimonial($data, $id);
            header('Location: /admin/testimonials?saved=1');
            exit;
        } catch (DomainException $e) {
            $item = $_POST;
            $item['id'] = $id;
            $this->renderAdmin('testimonial_form', ['item' => $item, 'error' => $e->getMessage()]);
        }
    }

    private function adminHappyMedia(): void
    {
        $this->requireAdmin();
        $this->renderAdmin('happy_media', ['items' => $this->app->homeContent()->allHappyMedia()]);
    }

    private function adminHappyMediaForm(?int $id): void
    {
        $this->requireAdmin();
        $item = null;
        if ($id !== null) {
            foreach ($this->app->homeContent()->allHappyMedia() as $row) {
                if ((int) $row['id'] === $id) {
                    $item = $row;
                    break;
                }
            }
            if ($item === null) {
                throw new DomainException('Media not found.');
            }
        }
        $this->renderAdmin('happy_media_form', ['item' => $item, 'error' => null]);
    }

    private function adminHappyMediaSave(?int $id): void
    {
        $this->requireAdmin();
        try {
            $data = $_POST;
            $uploaded = $this->storeUpload('media_file', 'happy');
            if ($uploaded !== null) {
                $data['media_path'] = $uploaded;
            }
            $this->app->homeContent()->saveHappyMedia($data, $id);
            header('Location: /admin/happy-media?saved=1');
            exit;
        } catch (DomainException $e) {
            $item = $_POST;
            $item['id'] = $id;
            $this->renderAdmin('happy_media_form', ['item' => $item, 'error' => $e->getMessage()]);
        }
    }

    private function adminFaq(): void
    {
        $this->requireAdmin();
        $this->renderAdmin('faq', ['items' => $this->app->homeContent()->allFaqs()]);
    }

    private function adminFaqForm(?int $id): void
    {
        $this->requireAdmin();
        $item = null;
        if ($id !== null) {
            foreach ($this->app->homeContent()->allFaqs() as $row) {
                if ((int) $row['id'] === $id) {
                    $item = $row;
                    break;
                }
            }
            if ($item === null) {
                throw new DomainException('FAQ not found.');
            }
        }
        $this->renderAdmin('faq_form', ['item' => $item, 'error' => null]);
    }

    private function adminFaqSave(?int $id): void
    {
        $this->requireAdmin();
        try {
            if (!isset($_POST['is_published'])) {
                $_POST['is_published'] = '0';
            }
            $this->app->homeContent()->saveFaq($_POST, $id);
            header('Location: /admin/faq?saved=1');
            exit;
        } catch (DomainException $e) {
            $item = $_POST;
            $item['id'] = $id;
            $this->renderAdmin('faq_form', ['item' => $item, 'error' => $e->getMessage()]);
        }
    }

    private function storeUpload(string $field, string $subdir): ?string
    {
        if (empty($_FILES[$field]['tmp_name']) || !is_uploaded_file($_FILES[$field]['tmp_name'])) {
            return null;
        }
        $name = (string) ($_FILES[$field]['name'] ?? '');
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            throw new DomainException('Invalid upload type.');
        }
        $dir = BASE_PATH . '/public/uploads/' . $subdir;
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new DomainException('Upload directory unavailable.');
        }
        $filename = bin2hex(random_bytes(8)) . '.' . $ext;
        $dest = $dir . '/' . $filename;
        if (!move_uploaded_file($_FILES[$field]['tmp_name'], $dest)) {
            throw new DomainException('Upload failed.');
        }

        return '/uploads/' . $subdir . '/' . $filename;
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
            $this->auth()->userId(),
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
