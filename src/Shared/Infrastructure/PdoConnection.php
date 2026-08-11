<?php

declare(strict_types=1);

namespace ClubinhoPet\Shared\Infrastructure;

use PDO;

final class PdoConnection
{
    private static ?PDO $pdo = null;

    public static function get(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $host = Env::require('DB_HOST');
        $port = Env::get('DB_PORT', '3306');
        $name = Env::require('DB_NAME');
        $user = Env::require('DB_USER');
        $pass = Env::get('DB_PASS', '') ?? '';
        $charset = Env::get('DB_CHARSET', 'utf8mb4') ?? 'utf8mb4';

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";

        self::$pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        self::$pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");

        return self::$pdo;
    }
}
