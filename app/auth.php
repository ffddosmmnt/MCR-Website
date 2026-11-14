<?php

declare(strict_types=1);

require_once __DIR__ . '/view.php';

function auth_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_name('mcr_user');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    ini_set('session.use_strict_mode', '1');
    session_start();
}

function auth_csrf(): string
{
    auth_session();
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

function auth_check_csrf(): void
{
    auth_session();
    if (!is_string($_POST['csrf'] ?? null)
        || !hash_equals((string) ($_SESSION['csrf'] ?? ''), $_POST['csrf'])) {
        throw new RuntimeException('Your form session expired. Please try again.');
    }
}

function auth_input(string $key, int $maxLength): string
{
    $value = $_POST[$key] ?? '';
    if (!is_string($value) || !mb_check_encoding($value, 'UTF-8') || str_contains($value, "\0")) {
        throw new InvalidArgumentException('Invalid form input.');
    }
    $value = trim($value);
    if ($value === '' || mb_strlen($value, 'UTF-8') > $maxLength) {
        throw new InvalidArgumentException('Please complete all fields within the allowed length.');
    }
    return $value;
}

function auth_current_user(): ?array
{
    auth_session();
    $userId = $_SESSION['user_id'] ?? null;
    if (!is_int($userId) || $userId < 1) {
        return null;
    }
    $query = database()->prepare('SELECT id, name, email, role FROM users WHERE id = :id');
    $query->execute(['id' => $userId]);
    $user = $query->fetch();
    if (!$user || !in_array($user['role'], ['user', 'admin'], true)) {
        auth_logout();
        return null;
    }
    return $user;
}

function auth_login(int $userId): void
{
    auth_session();
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

function auth_has_role(string $role): bool
{
    $user = auth_current_user();
    return $user !== null && $user['role'] === $role;
}

function auth_logout(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], '', (bool) $params['secure'], (bool) $params['httponly']);
    }
    session_destroy();
}

function auth_redirect_target(): string
{
    $target = $_POST['redirect'] ?? $_GET['redirect'] ?? 'index.html';
    if (!is_string($target) || !preg_match('~\A/[a-zA-Z0-9._/-]*\z~', $target)) {
        return 'index.html';
    }
    return $target;
}
