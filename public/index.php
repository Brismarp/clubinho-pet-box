<?php

declare(strict_types=1);

/** @var ClubinhoPet\Shared\Infrastructure\AppContainer $app */
$app = require dirname(__DIR__) . '/src/Shared/Infrastructure/bootstrap.php';

use ClubinhoPet\Ui\HttpKernel;

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

// Static HTML files are served by Apache/php -S directly.
// This front controller handles dynamic routes only.

$kernel = new HttpKernel($app);
$kernel->handle($method, $uri);
