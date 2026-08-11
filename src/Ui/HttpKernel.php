<?php

declare(strict_types=1);

namespace ClubinhoPet\Ui;

use ClubinhoPet\Identity\Domain\AuthSession;
use ClubinhoPet\Shared\Domain\DomainException;
use ClubinhoPet\Shared\Infrastructure\AppContainer;

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
                $method === 'POST' && $path === '/subscribe' => $this->subscribe(),
                $method === 'POST' && $path === '/contact' => $this->contact(),

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
        $result = $this->app->startCheckout()->handle(
            new \ClubinhoPet\Subscription\Application\StartCheckout\StartCheckoutCommand($plan)
        );
        header('Location: ' . $result->checkoutUrl);
        exit;
    }

    private function subscribe(): void
    {
        $str = static fn (string $key): ?string => isset($_POST[$key]) && $_POST[$key] !== '' ? (string) $_POST[$key] : null;

        $result = $this->app->registerInterest()->handle(
            new \ClubinhoPet\Subscription\Application\RegisterInterest\RegisterInterestCommand(
                planSlug: (string) ($_POST['plan'] ?? ''),
                name: (string) ($_POST['name'] ?? ''),
                email: (string) ($_POST['email'] ?? ''),
                petName: (string) ($_POST['pet_name'] ?? ''),
                petType: $str('pet_type'),
                petAge: isset($_POST['pet_age']) && $_POST['pet_age'] !== '' ? (int) $_POST['pet_age'] : null,
                petBreed: $str('pet_breed'),
                dietaryRestrictions: $str('dietary_restrictions'),
                phone: $str('phone'),
                address: $str('address'),
            )
        );

        if ($this->wantsJson()) {
            $this->json($result, 201);
            return;
        }

        header('Location: ' . $result['checkout_url']);
        exit;
    }

    private function contact(): void
    {
        $name = (string) ($_POST['name'] ?? $_POST['nome'] ?? '');
        $email = (string) ($_POST['email'] ?? '');
        $subject = (string) ($_POST['subject'] ?? $_POST['assunto'] ?? '');
        $message = (string) ($_POST['message'] ?? $_POST['mensagem'] ?? '');

        $created = $this->app->submitContact()->handle($name, $email, $subject, $message);

        if ($this->wantsJson()) {
            $this->json($created, 201);
            return;
        }

        header('Location: /contato.html?sent=1');
        exit;
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
        $this->renderAdmin('dashboard', [
            'plans' => count($this->app->listPlans()->handle()),
            'subscriptions' => count($this->app->listSubscriptions()->handle()),
            'contacts' => count($this->app->listContacts()->handle()),
            'boxes' => count($this->app->listBoxes()->handle()),
            'user' => $this->auth()->userName(),
        ]);
    }

    private function adminPlans(): void
    {
        $this->requireAdmin();
        $this->renderAdmin('plans', ['plans' => $this->app->listPlans()->handle()]);
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
        $this->renderAdmin('subscriptions', ['items' => $this->app->listSubscriptions()->handle()]);
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
        $this->renderAdmin('contacts', ['items' => $this->app->listContacts()->handle()]);
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
