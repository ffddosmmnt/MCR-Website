<?php

declare(strict_types=1);

// Provisioning is deliberately CLI-only; never expose public registration or role selection.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/../../app/database.php';
$password = '';
try {
    $name = trim($argv[1] ?? '');
    $email = trim($argv[2] ?? '');
    $password = rtrim(stream_get_contents(STDIN, 1025), "\r\n");
    if ($name === '' || !mb_check_encoding($name, 'UTF-8') || mb_strlen($name) > 100
        || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150
        || strlen($password) < 12 || strlen($password) > 72 || str_contains($password, "\0")) {
        throw new InvalidArgumentException('Use: php dashboard/internal/create-admin.php "Admin name" "email"; send a 12–72 byte password through standard input.');
    }
    $stmt = database()->prepare("INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,'admin')");
    $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
    fwrite(STDOUT, "Administrator created. Existing users were not changed.\n");
} catch (InvalidArgumentException $error) {
    fwrite(STDERR, $error->getMessage() . "\n");
    exit(1);
} catch (Throwable $error) {
    fwrite(STDERR, "Administrator could not be created. Check DB configuration, permissions, and whether the email already exists.\n");
    exit(1);
} finally {
    unset($password);
}
