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

if ($path === '/api/albums.php') {
    require __DIR__ . '/api/albums.php';
    return;
}

$staticFiles = [
    'index.html', 'about.html', 'music.html', 'tour.html', 'showcase.html',
    'news.html', 'news-detail.html', 'ticket-transaction.html',
    'base.css', 'home.css', 'about.css', 'music.css', 'tour.css', 'showcase.css',
    'news.css', 'news-detail.css', 'ticket-transaction.css', 'script.js',
];
$relativePath = $path === '/' ? 'index.html' : ltrim($path, '/');
$file = null;

if (in_array($relativePath, $staticFiles, true)) {
    $file = __DIR__ . '/' . $relativePath;
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
