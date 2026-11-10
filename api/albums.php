<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/content.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    header('Allow: GET');
    http_response_code(405);
    echo json_encode(['error' => ['code' => 'method_not_allowed', 'message' => 'Use GET for this resource.']]);
    return;
}

try {
    $albums = albums();

    foreach ($albums as &$album) {
        $album['id'] = (int) $album['id'];
    }
    unset($album);

    $response = json_encode(['data' => $albums], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
} catch (Throwable $error) {
    // Deliberately omit exception text: PDO errors can contain credentials or SQL.
    error_log('[mcr] Album read failed (' . get_class($error) . '). Check database configuration and server logs.');
    http_response_code(503);
    header('Cache-Control: no-store');
    $response = json_encode([
        'error' => ['code' => 'service_unavailable', 'message' => 'Albums are temporarily unavailable.'],
    ]);
}

echo $response;
