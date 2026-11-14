<?php
declare(strict_types=1);
require_once __DIR__ . '/view.php';

/** Public, read-only content API. Never enable credentialed CORS on these routes. */
function public_api(callable $read): void
{
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, OPTIONS');
    $method = $_SERVER['REQUEST_METHOD'] ?? '';
    if ($method === 'OPTIONS') {
        http_response_code(204);
        return;
    }
    if ($method !== 'GET') {
        header('Allow: GET, OPTIONS');
        http_response_code(405);
        echo json_encode(['error' => ['code' => 'method_not_allowed', 'message' => 'Use GET for this resource.']]);
        return;
    }
    try {
        $response = json_encode(['data' => $read()], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    } catch (Throwable $error) {
        if ($error instanceof InvalidArgumentException) {
            $status = 400; $code = 'invalid_request'; $message = 'The request parameters are invalid.';
        } elseif ($error instanceof DomainException) {
            $status = 404; $code = 'not_found'; $message = 'The requested content was not found.';
        } else {
            $status = 503; $code = 'service_unavailable'; $message = 'Content is temporarily unavailable.';
            error_log('[mcr] Public content read failed (' . get_class($error) . ').');
        }
        http_response_code($status);
        $response = json_encode(['error' => ['code' => $code, 'message' => $message]]);
    }
    echo $response;
}

function query_text(string $name, int $maxLength): string
{
    $value = $_GET[$name] ?? '';
    if (!is_string($value) || !mb_check_encoding($value, 'UTF-8') || mb_strlen($value, 'UTF-8') > $maxLength) {
        throw new InvalidArgumentException('Invalid query text.');
    }
    return trim($value);
}

function public_album(array $album): array
{
    $album['id'] = (int) $album['id'];
    $album['cover_image'] = album_cover($album['cover_image']);
    $album['date_display'] = date_label($album['release_date']);
    return $album;
}

function public_article(array $article): array
{
    $article['id'] = (int) $article['id'];
    $article['date_display'] = date_label($article['news_date'] === null ? null : substr($article['news_date'], 0, 10));
    $article['excerpt'] = excerpt($article['content']);
    return $article;
}

function public_concert(array $concert): array
{
    $concert['id'] = (int) $concert['id'];
    $concert['date_display'] = date_label($concert['concert_date'], true);
    return $concert;
}
