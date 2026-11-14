<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/database.php';

if (PHP_SAPI !== 'cli' || $argc !== 3) {
    fwrite(STDERR, "Usage: php tools/create-admin.php \"Name\" email@example.com\n");
    exit(2);
}

$name = trim($argv[1]);
$email = trim($argv[2]);
$password = trim((string) fgets(STDIN));
if ($name === '' || mb_strlen($name, 'UTF-8') > 100
    || !filter_var($email, FILTER_VALIDATE_EMAIL)
    || strlen($password) < 12 || strlen($password) > 72) {
    fwrite(STDERR, "Invalid name, email, or password length.\n");
    exit(2);
}

try {
    database()->prepare(
        'INSERT INTO users (name, email, password_hash, role)
         VALUES (:name, :email, :password_hash, "admin")',
    )->execute([
        'name' => $name,
        'email' => $email,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
    ]);
    fwrite(STDOUT, "Administrator created.\n");
} catch (PDOException $exception) {
    fwrite(STDERR, $exception->errorInfo[1] === 1062
        ? "That email address is already registered.\n"
        : "Administrator creation failed.\n");
    exit(1);
}
