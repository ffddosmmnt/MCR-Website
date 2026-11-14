<?php

declare(strict_types=1);

// Development router: always pass this file when starting PHP's built-in server.
ini_set('display_errors', '0');
ini_set('log_errors', '1');
header('X-Content-Type-Options: nosniff');

$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');

if (str_contains($path, "\0") || str_contains($path, '\\') || preg_match('~(?:^|/)\.\.?(/|$)~', $path)) {
    http_response_code(404);
    echo 'Not found.';
    return;
}
if (in_array($path, ['/showcase.php', '/ticket-transaction.php', '/news.php'], true)) {
    require __DIR__ . $path;
    return;
}
$phpAliases = [
    '/showcase.html' => '/showcase.php',
    '/ticket-transaction.html' => '/ticket-transaction.php',
    '/news.html' => '/news.php',
];
if (isset($phpAliases[$path])) {
    $query = $_SERVER['QUERY_STRING'] ?? '';
    header('Location: ' . $phpAliases[$path] . ($query === '' ? '' : '?' . $query), true, 308);
    return;
}

$publicApis = ['albums', 'home', 'music', 'news', 'article', 'tour'];
if (preg_match('~\A/api/([a-z]+)\.php\z~', $path, $apiMatch) && in_array($apiMatch[1], $publicApis, true)) {
    require __DIR__ . '/api/' . $apiMatch[1] . '.php';
    return;
}

if (in_array($path, ['/dashboard', '/dashboard-auth'], true)) {
    header('Location: ' . $path . '/', true, 308);
    return;
}
$dashboardPages = [
    '/dashboard/' => 'dashboard/index.php', '/dashboard/index.php' => 'dashboard/index.php',
    '/dashboard-auth/' => 'dashboard-auth/index.php', '/dashboard-auth/index.php' => 'dashboard-auth/index.php',
    '/dashboard-auth/login.php' => 'dashboard-auth/login.php', '/dashboard-auth/logout.php' => 'dashboard-auth/logout.php',
];
if (isset($dashboardPages[$path]) && is_file(__DIR__ . '/' . $dashboardPages[$path])) {
    require __DIR__ . '/' . $dashboardPages[$path];
    return;
}

$legacyPages = ['index', 'music', 'tour', 'news', 'news-detail'];
foreach ($legacyPages as $page) {
    if ($path === '/' . $page . '.php') {
        $query = $_SERVER['QUERY_STRING'] ?? '';
        header('Location: ' . $page . '.html' . ($query === '' ? '' : '?' . $query), true, 308);
        return;
    }
}

$staticFiles = [
    'index.html', 'music.html', 'news.html', 'news-detail.html', 'tour.html',
    'about.html',
    'base.css', 'home.css', 'about.css', 'music.css', 'tour.css', 'showcase.css',
    'news.css', 'news-detail.css', 'ticket-transaction.css', 'script.js', 'site-config.js', 'content.js',
    'dashboard/adminlte.min.css', 'dashboard/template.css', 'dashboard/dashboard.js',
    'dashboard-auth/adminlte.min.css', 'dashboard-auth/template.css', 'dashboard-auth/dashboard.js',
];
$relativePath = $path === '/' ? 'index.html' : ltrim($path, '/');
$file = null;

if (in_array($relativePath, $staticFiles, true)) {
    $file = __DIR__ . '/' . $relativePath;
} elseif (preg_match('~\Adashboard/uploads/[a-f0-9]{32}\.(?:jpg|png|webp)\z~', $relativePath)) {
    $uploadRoot = realpath(__DIR__ . '/dashboard/uploads');
    $candidate = realpath(__DIR__ . '/' . $relativePath);
    if ($uploadRoot !== false && $candidate !== false
        && str_starts_with($candidate, $uploadRoot . DIRECTORY_SEPARATOR)) {
        $file = $candidate;
    }
} elseif (str_starts_with($relativePath, 'assets/')) {
    $assetRoot = realpath(__DIR__ . '/assets');
    $candidate = realpath(__DIR__ . '/' . $relativePath);
    if ($assetRoot !== false && $candidate !== false
        && str_starts_with($candidate, $assetRoot . DIRECTORY_SEPARATOR)) {
        $file = $candidate;
    }
}

$types = [
    'html' => 'text/html; charset=utf-8', 'css' => 'text/css; charset=utf-8',
    'js' => 'text/javascript; charset=utf-8', 'png' => 'image/png',
    'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'svg' => 'image/svg+xml',
    'webp' => 'image/webp', 'gif' => 'image/gif', 'ico' => 'image/x-icon',
];
$type = $file === null ? null : ($types[strtolower(pathinfo($file, PATHINFO_EXTENSION))] ?? null);

if ($file === null || !is_file($file) || $type === null) {
    http_response_code(404);
    echo 'Not found.';
    return;
}

if (!in_array($_SERVER['REQUEST_METHOD'] ?? '', ['GET', 'HEAD'], true)) {
    http_response_code(405);
    header('Allow: GET, HEAD');
    return;
}

header('Content-Type: ' . $type);
if ($_SERVER['REQUEST_METHOD'] !== 'HEAD') {
    readfile($file);
}
