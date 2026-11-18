<?php
declare(strict_types=1);

// Shared administrator authentication for both dashboard entry points.
function admin_session(): void
{
    $sessionDirectory = __DIR__ . '/sessions';
    if (!is_dir($sessionDirectory) && !@mkdir($sessionDirectory, 0700) && !is_dir($sessionDirectory)) {
        admin_fail(503, 'Session storage is unavailable.');
    }
    session_save_path($sessionDirectory);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('mcr_admin');
    session_set_cookie_params(['httponly' => true, 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'samesite' => 'Lax', 'path' => '/']);
    if (!session_start()) {
        admin_fail(503, 'Session storage is unavailable.');
    }
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    if (isset($_SESSION['last_active']) && time() - $_SESSION['last_active'] > 1800) {
        $_SESSION = [];
        session_regenerate_id(true);
    }
    $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

function admin_csrf(): void
{
    if (!is_string($_POST['csrf'] ?? null) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        admin_fail(403, 'Your session token is invalid. Reload the page and try again.');
    }
}

function admin_fail(int $status, string $message): never
{
    http_response_code($status);
    admin_head('Request failed');
    echo '<main class="halaman"><div class="alert alert-danger" role="alert">' . e($message) . '</div><a href="index.php">Back to dashboard</a></main></body></html>';
    exit;
}

function admin_redirect(string $url = 'index.php'): never
{
    header('Location: ' . $url, true, 303);
    exit;
}

function admin_user(): array
{
    if (!isset($_SESSION['admin_id']) || time() - ($_SESSION['last_active'] ?? 0) > 1800) {
        unset($_SESSION['admin_id'], $_SESSION['last_active']);
        admin_redirect('login.php');
    }
    // Re-read role on every request so revocation takes effect immediately.
    $stmt = database()->prepare('SELECT id,name,role,password_hash FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['admin_id']]);
    $user = $stmt->fetch();
    if (!$user || $user['role'] !== 'admin' || !hash_equals($_SESSION['auth_version'] ?? '', hash('sha256', $user['password_hash']))) {
        unset($_SESSION['admin_id']);
        admin_fail(403, 'Administrator access is required.');
    }
    $_SESSION['last_active'] = time();
    return $user;
}

function admin_head(string $title): void
{
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="en" data-bs-theme="dark"><head><meta charset="utf-8"><meta name="color-scheme" content="dark"><meta name="viewport" content="width=device-width, initial-scale=1"><title>' . e($title) . ' | MCR Admin</title><link rel="stylesheet" href="adminlte.min.css"><link rel="stylesheet" href="template.css"></head><body>';
}
