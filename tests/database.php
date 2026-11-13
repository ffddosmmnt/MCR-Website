<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/database.php';

function check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$user = getenv('DB_USER');
putenv('DB_USER');
try {
    database();
    throw new LogicException('Missing credentials were accepted.');
} catch (RuntimeException $error) {
    check($error->getMessage() === 'Database credentials are not configured.', 'Unexpected configuration error.');
}
putenv('DB_USER=' . $user);

$port = getenv('DB_PORT');
putenv('DB_PORT=3306;dbname=other');
try {
    database();
    throw new LogicException('Invalid port was accepted.');
} catch (RuntimeException $error) {
    check($error->getMessage() === 'Database connection settings are invalid.', 'Unexpected validation error.');
}
putenv('DB_PORT=' . $port);

$connection = database();
check($connection === database(), 'Connection was not reused.');
check((bool) $connection->getAttribute(PDO::ATTR_EMULATE_PREPARES) === false, 'Prepared statement emulation is enabled.');
check($connection->query('SELECT @@character_set_connection')->fetchColumn() === 'utf8mb4', 'Incorrect charset.');
$query = $connection->prepare('SELECT ? AS value');
$sample = "dummy 😀 <script>alert('test')</script>";
$query->execute([$sample]);
check($query->fetchColumn() === $sample, 'UTF-8 prepared statement round trip failed.');
echo "PASS: configuration validation, connection reuse, native prepares, utf8mb4 round trip.\n";
