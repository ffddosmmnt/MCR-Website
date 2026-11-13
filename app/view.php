<?php

declare(strict_types=1);

require_once __DIR__ . '/content.php';

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** No exception/SQL details leave the server. Null means loading failed, not empty data. */
function load_content(callable $loader): ?array
{
    try {
        return $loader();
    } catch (Throwable $error) {
        error_log('[mcr] Content read failed (' . get_class($error) . ').');
        http_response_code(503);
        header('Cache-Control: no-store');
        return null;
    }
}

function page_header(string $pageTitle, string $activePage, string $stylesheet): void
{
    header('Content-Type: text/html; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) {
        http_response_code(405);
        header('Allow: GET, HEAD');
        exit('Method not allowed.');
    }
    require __DIR__ . '/views/header.php';
}

function page_footer(): void
{
    require __DIR__ . '/views/footer.php';
}

function content_notice(string $message, bool $error = false): void
{
    echo '<p class="content-notice" role="' . ($error ? 'alert' : 'status') . '">' . e($message) . '</p>';
}

function article_url(array $article): string
{
    return 'news-detail.php?slug=' . rawurlencode($article['slug']);
}

/** Keep database-supplied image paths local; never turn them into external/traversal URLs. */
function album_cover(?string $path): string
{
    $imageRoot = $path !== null && str_starts_with($path, 'assets/') ? 'assets' : null;
    if ($path !== null && preg_match('~\Adashboard/uploads/[a-f0-9]{32}\.(?:jpg|png|webp)\z~', $path)) {
        $imageRoot = 'dashboard/uploads';
    }
    if ($path !== null && $imageRoot !== null && !str_contains($path, '\\')
        && !preg_match('~(?:^|/)\.\.?(/|$)~', $path)
        && preg_match('/\.(?:png|jpe?g|webp|gif)\z/i', $path)) {
        $assetRoot = realpath(dirname(__DIR__) . '/' . $imageRoot);
        $file = realpath(dirname(__DIR__) . '/' . $path);
        if ($assetRoot !== false && $file !== false && is_file($file)
            && str_starts_with($file, $assetRoot . DIRECTORY_SEPARATOR)) {
            return $path;
        }
    }
    return 'assets/Blackparade.png';
}

function date_label(?string $date, bool $withTime = false): string
{
    if ($date === null) {
        return 'Date to be announced';
    }
    $value = DateTimeImmutable::createFromFormat($withTime ? '!Y-m-d H:i:s' : '!Y-m-d', $date, new DateTimeZone('UTC'));
    $errors = DateTimeImmutable::getLastErrors();
    if ($value === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
        return 'Date unavailable';
    }
    return $withTime
        ? $value->setTimezone(new DateTimeZone('Asia/Jakarta'))->format('d M Y, H:i') . ' WIB'
        : $value->format('d M Y');
}

function excerpt(?string $content): string
{
    return mb_strimwidth(preg_replace('/\s+/u', ' ', $content ?? '') ?? '', 0, 180, '…', 'UTF-8');
}
