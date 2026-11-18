<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/shop-api.php';

// One server-side account session for the website and both dashboard entry points.
function admin_session(): void
{
    try {
        shop_session();
    } catch (Throwable $error) {
        error_log('[mcr] Account session unavailable (' . get_class($error) . ').');
        admin_fail(503, 'Session storage is unavailable.');
    }
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');

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
    if (!isset($_SESSION['user_id'])) {
        admin_redirect('login.php');
    }
    // shop_user re-reads the role/password version; browser-supplied roles are never trusted.
    $user = shop_user(false);
    if ($user === null || $user['role'] !== 'admin') {
        admin_fail(403, 'Administrator access is required.');
    }
    return $user;
}

function admin_head(string $title): void
{
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="en" data-bs-theme="dark"><head><meta charset="utf-8"><meta name="color-scheme" content="dark"><meta name="viewport" content="width=device-width, initial-scale=1"><title>' . e($title) . ' | MCR Admin</title><link rel="stylesheet" href="adminlte.min.css"><link rel="stylesheet" href="template.css"></head><body>';
}
