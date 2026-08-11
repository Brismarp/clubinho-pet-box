<?php

declare(strict_types=1);

namespace ClubinhoPet\Shared\Infrastructure;

final class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    public static function token(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (empty($_SESSION[self::SESSION_KEY]) || !is_string($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    public static function fieldHtml(): string
    {
        $token = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');

        return '<input type="hidden" name="_csrf" value="' . $token . '">';
    }

    public static function isValid(?string $token): bool
    {
        if ($token === null || $token === '') {
            return false;
        }
        $expected = self::token();

        return hash_equals($expected, $token);
    }

    public static function assertValid(?string $token): void
    {
        if (!self::isValid($token)) {
            if (!headers_sent()) {
                header('HTTP/1.1 419 Page Expired');
                http_response_code(419);
            }
            $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
            $wantsJson = str_contains($accept, 'application/json')
                || str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/');
            if ($wantsJson) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => 'CSRF token mismatch'], JSON_UNESCAPED_UNICODE);
            } else {
                header('Content-Type: text/plain; charset=utf-8');
                echo '419 CSRF token mismatch';
            }
            exit;
        }
    }

    /** Token from POST body, JSON body, or X-CSRF-Token header. */
    public static function requestToken(?array $jsonBody = null): ?string
    {
        $header = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (is_string($header) && $header !== '') {
            return $header;
        }
        if (isset($_POST['_csrf']) && is_string($_POST['_csrf'])) {
            return $_POST['_csrf'];
        }
        if (is_array($jsonBody) && isset($jsonBody['_csrf']) && is_string($jsonBody['_csrf'])) {
            return $jsonBody['_csrf'];
        }

        return null;
    }
}
