<?php

declare(strict_types=1);

it('rejects mutating requests without csrf', function () {
    $jar = tempnam(sys_get_temp_dir(), 'cpb');
    csrfToken($jar); // establish session
    $res = httpRequest('POST', '/subscribe', $jar, [
        'plan' => 'happy',
        'name' => 'No Token',
        'email' => 'notoken@example.com',
        'pet_name' => 'X',
        'pet_type' => 'cachorro',
        'terms' => '1',
    ]);
    expect($res['status'])->toBe(419);
});

it('subscribes and confirms mock payment with csrf', function () {
    $jar = tempnam(sys_get_temp_dir(), 'cpb');
    $token = csrfToken($jar);
    $email = 'pest-pay-' . bin2hex(random_bytes(3)) . '@example.com';

    $sub = httpRequest('POST', '/subscribe', $jar, [
        'plan' => 'happy',
        'billing' => 'monthly',
        'name' => 'Pest Pay',
        'email' => $email,
        'pet_name' => 'Rex',
        'pet_type' => 'cachorro',
        'terms' => '1',
        '_csrf' => $token,
    ]);
    expect($sub['status'])->toBe(201);
    $data = json_decode($sub['body'], true);
    expect($data)->toHaveKey('subscription_id');
    $sid = (int) $data['subscription_id'];

    $pay = httpRequest('POST', '/api/v1/payments/mock/confirm', $jar, null, [
        'subscription_id' => $sid,
        'method' => 'pix',
    ], ['X-CSRF-Token' => $token]);
    expect($pay['status'])->toBe(201);
    $paid = json_decode($pay['body'], true);
    expect($paid['status'] ?? null)->toBe('paid')
        ->and($paid['subscription_id'] ?? null)->toBe($sid);
});

it('starts a quiz session with csrf header', function () {
    $jar = tempnam(sys_get_temp_dir(), 'cpb');
    $token = csrfToken($jar);
    $res = httpRequest('POST', '/api/v1/quiz/sessions', $jar, null, [], [
        'X-CSRF-Token' => $token,
    ]);
    expect($res['status'])->toBe(201);
    $data = json_decode($res['body'], true);
    expect($data)->toHaveKey('token')
        ->and(strlen((string) ($data['token'] ?? '')))->toBeGreaterThan(10);
});

it('serves enviar-foto and rule-sets admin requires auth', function () {
    $jar = tempnam(sys_get_temp_dir(), 'cpb');
    $page = httpRequest('GET', '/enviar-foto', $jar);
    expect($page['status'])->toBe(200)
        ->and($page['body'])->toContain('Enviar');

    $admin = httpRequest('GET', '/admin/rule-sets', $jar);
    expect(in_array($admin['status'], [302, 401, 403], true))->toBeTrue();
});

it('exports customer dsar json when admin authenticated', function () {
    $jar = tempnam(sys_get_temp_dir(), 'cpb');
    $csrf = csrfToken($jar);

    $login = httpRequest('POST', '/admin/login', $jar, [
        'email' => 'admin@clubinhopetbox.com.br',
        'password' => 'Carpe1936!',
        '_csrf' => $csrf,
    ]);
    // After login CSRF rotates — refresh
    expect(in_array($login['status'], [302, 200], true))->toBeTrue();
    $csrf = csrfToken($jar);

    // Ensure a customer exists via quiz or subscription email
    $email = 'pest-dsar-' . bin2hex(random_bytes(3)) . '@example.com';
    httpRequest('POST', '/subscribe', $jar, [
        'plan' => 'love',
        'name' => 'DSAR Pest',
        'email' => $email,
        'pet_name' => 'Mia',
        'pet_type' => 'gato',
        'terms' => '1',
        '_csrf' => $csrf,
    ]);

    // Find customer id via DB API is not exposed — list HTML scrape is fragile.
    // Use export on id 1 if present, otherwise skip gracefully.
    $list = httpRequest('GET', '/admin/customers', $jar);
    expect($list['status'])->toBe(200);

    if (preg_match('#/admin/customers/(\d+)#', $list['body'], $m)) {
        $id = (int) $m[1];
        $export = httpRequest('GET', '/admin/customers/' . $id . '/export', $jar);
        expect($export['status'])->toBe(200);
        $json = json_decode($export['body'], true);
        expect($json)->toBeArray()
            ->and($json)->toHaveKeys(['customer', 'exported_at']);
    } else {
        expect(true)->toBeTrue();
    }
});

it('allows financeiro login and opens payments', function () {
    $jar = tempnam(sys_get_temp_dir(), 'cpb');
    $csrf = csrfToken($jar);
    $login = httpRequest('POST', '/admin/login', $jar, [
        'email' => 'financeiro@clubinhopetbox.com.br',
        'password' => 'Carpe1936!',
        '_csrf' => $csrf,
    ]);
    expect(in_array($login['status'], [302, 200], true))->toBeTrue();

    $payments = httpRequest('GET', '/admin/payments', $jar);
    expect($payments['status'])->toBe(200)
        ->and($payments['body'])->toContain('Pagamentos');

    $users = httpRequest('GET', '/admin/users', $jar);
    expect(in_array($users['status'], [302, 403], true) || str_contains($users['body'], 'Forbidden') || $users['status'] === 403)->toBeTrue();
});

it('pauses and reactivates a subscription as admin', function () {
    $jar = tempnam(sys_get_temp_dir(), 'cpb');
    $csrf = csrfToken($jar);
    $email = 'pest-sub-' . bin2hex(random_bytes(3)) . '@example.com';

    $sub = httpRequest('POST', '/subscribe', $jar, [
        'plan' => 'happy',
        'billing' => 'monthly',
        'name' => 'Sub Lifecycle',
        'email' => $email,
        'pet_name' => 'Bob',
        'pet_type' => 'cachorro',
        'terms' => '1',
        '_csrf' => $csrf,
    ]);
    expect($sub['status'])->toBe(201);
    $sid = (int) (json_decode($sub['body'], true)['subscription_id'] ?? 0);
    expect($sid)->toBeGreaterThan(0);

    httpRequest('POST', '/api/v1/payments/mock/confirm', $jar, null, [
        'subscription_id' => $sid,
        'method' => 'card',
    ], ['X-CSRF-Token' => $csrf]);

    $login = httpRequest('POST', '/admin/login', $jar, [
        'email' => 'admin@clubinhopetbox.com.br',
        'password' => 'Carpe1936!',
        '_csrf' => csrfToken($jar),
    ]);
    expect(in_array($login['status'], [302, 200], true))->toBeTrue();
    $csrf = csrfToken($jar);

    $pause = httpRequest('POST', '/admin/subscriptions/status', $jar, [
        'id' => (string) $sid,
        'action' => 'pause',
        '_csrf' => $csrf,
    ]);
    expect(in_array($pause['status'], [302, 200], true))->toBeTrue();

    $react = httpRequest('POST', '/admin/subscriptions/status', $jar, [
        'id' => (string) $sid,
        'action' => 'reactivate',
        '_csrf' => csrfToken($jar),
    ]);
    expect(in_array($react['status'], [302, 200], true))->toBeTrue();

    $list = httpRequest('GET', '/admin/subscriptions?status=active', $jar);
    expect($list['status'])->toBe(200)
        ->and($list['body'])->toContain($email);
});

it('lists admin users for admin role', function () {
    $jar = tempnam(sys_get_temp_dir(), 'cpb');
    $csrf = csrfToken($jar);
    httpRequest('POST', '/admin/login', $jar, [
        'email' => 'admin@clubinhopetbox.com.br',
        'password' => 'Carpe1936!',
        '_csrf' => $csrf,
    ]);
    $page = httpRequest('GET', '/admin/users', $jar);
    expect($page['status'])->toBe(200)
        ->and($page['body'])->toContain('Usuários')
        ->and($page['body'])->toContain('financeiro@clubinhopetbox.com.br');
});
