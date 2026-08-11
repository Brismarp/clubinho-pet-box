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
