<?php

declare(strict_types=1);

use ClubinhoPet\Shared\Infrastructure\AppContainer;
use ClubinhoPet\Shared\Infrastructure\Env;

define('BASE_PATH', dirname(__DIR__, 3));

require_once BASE_PATH . '/vendor/autoload.php';

Env::load(BASE_PATH);

if (session_status() !== PHP_SESSION_ACTIVE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

date_default_timezone_set('America/Sao_Paulo');

return new AppContainer();
