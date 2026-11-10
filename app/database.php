<?php

declare(strict_types=1);

/** One connection per request. Configuration comes from the process environment. */
function database(): PDO
{
    static $connection = null;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $host = getenv('DB_HOST');
    $port = getenv('DB_PORT');
    $name = getenv('DB_NAME');
    $user = getenv('DB_USER');
    $password = getenv('DB_PASSWORD');

    $host = $host === false ? '127.0.0.1' : $host;
    $port = $port === false ? '3306' : $port;
    $name = $name === false ? 'mcr_website' : $name;

    if ($user === false || $user === '' || $password === false) {
        throw new RuntimeException('Database credentials are not configured.');
    }

    if (!preg_match('/\A[a-zA-Z0-9_.:-]+\z/', $host)
        || !preg_match('/\A[a-zA-Z0-9_]+\z/', $name)
        || !ctype_digit($port)
        || (int) $port < 1
        || (int) $port > 65535) {
        throw new RuntimeException('Database connection settings are invalid.');
    }

    $candidate = new PDO(
        "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 3,
        ],
    );

    // concerts.concert_date is TIMESTAMP: read it in a deterministic, labelled timezone.
    $candidate->exec("SET time_zone = '+00:00'");
    $connection = $candidate;

    return $connection;
}
