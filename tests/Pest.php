<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

uses(Tests\TestCase::class)->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function appBaseUrl(): string
{
    $env = getenv('APP_URL');
    if (is_string($env) && $env !== '') {
        return rtrim($env, '/');
    }
    // Pest runs inside the web container via `make test`
    if (file_exists('/.dockerenv')) {
        return 'http://127.0.0.1';
    }

    return 'http://localhost:8080';
}

/**
 * @return array{0: CurlHandle, 1: string} cookieJarPath
 */
function httpCookieJar(): array
{
    $path = tempnam(sys_get_temp_dir(), 'cpb_ck_');
    return [curl_init(), $path];
}

/**
 * @param array<string, string> $headers
 * @return array{status:int, body:string, headers:string}
 */
function httpRequest(
    string $method,
    string $path,
    ?string $cookieJar = null,
    ?array $form = null,
    ?array $json = null,
    array $headers = [],
): array {
    $ch = curl_init(appBaseUrl() . $path);
    $headerLines = [];
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_HEADER => true,
        CURLOPT_FOLLOWLOCATION => false,
    ]);
    if ($cookieJar !== null) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
    }
    foreach ($headers as $k => $v) {
        $headerLines[] = $k . ': ' . $v;
    }
    if ($json !== null) {
        $headerLines[] = 'Content-Type: application/json';
        $headerLines[] = 'Accept: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json, JSON_UNESCAPED_UNICODE));
    } elseif ($form !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form));
        $headerLines[] = 'Accept: application/json';
    }
    if ($headerLines !== []) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerLines);
    }
    $raw = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    $raw = is_string($raw) ? $raw : '';

    return [
        'status' => $status,
        'headers' => substr($raw, 0, $headerSize),
        'body' => substr($raw, $headerSize),
    ];
}

function csrfToken(string $cookieJar): string
{
    $res = httpRequest('GET', '/api/v1/csrf', $cookieJar);
    $data = json_decode($res['body'], true);

    return is_array($data) ? (string) ($data['token'] ?? '') : '';
}
