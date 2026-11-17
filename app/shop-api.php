<?php
declare(strict_types=1);
require_once __DIR__ . '/database.php';

final class ShopError extends RuntimeException
{
    public function __construct(public readonly int $status, string $message) { parent::__construct($message); }
}

function shop_session(): void
{
    $directory = dirname(__DIR__) . '/.runtime/shop-sessions';
    if (!is_dir($directory) && !@mkdir($directory, 0700, true) && !is_dir($directory)) {
        throw new ShopError(503, 'Session storage is unavailable.');
    }
    session_save_path($directory);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('mcr_shop');
    session_set_cookie_params(['httponly' => true, 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'samesite' => 'Lax', 'path' => '/']);
    if (!session_start()) throw new ShopError(503, 'Session storage is unavailable.');
    if (isset($_SESSION['last_active']) && time() - $_SESSION['last_active'] > 1800) {
        $_SESSION = [];
        session_regenerate_id(true);
    }
    $_SESSION['last_active'] = time();
    $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

/** Private, same-origin endpoints: never attach public wildcard CORS here. */
function shop_api(callable $handle): void
{
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    try {
        $method = $_SERVER['REQUEST_METHOD'] ?? '';
        if (!in_array($method, ['GET', 'POST'], true)) {
            header('Allow: GET, POST');
            throw new ShopError(405, 'Use GET or POST.');
        }
        shop_session();
        $input = [];
        if ($method === 'POST') {
            $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
            $scheme = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
            if ($origin !== '' && $origin !== $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? '')) {
                throw new ShopError(403, 'Submit this form on the website hosting it.');
            }
            $contentType = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '', 2)[0]));
            if ($contentType !== 'application/json') throw new ShopError(415, 'Send a JSON request.');
            $raw = file_get_contents('php://input', false, null, 0, 16385);
            if ($raw === false || strlen($raw) > 16384) throw new ShopError(413, 'Request is too large.');
            try { $input = json_decode($raw, true, 32, JSON_THROW_ON_ERROR); }
            catch (JsonException) { throw new ShopError(400, 'Invalid JSON.'); }
            if (!is_array($input) || array_is_list($input)) throw new ShopError(400, 'Send a JSON object.');
            if (!is_string($input['csrf'] ?? null) || !hash_equals($_SESSION['csrf'], $input['csrf'])) {
                throw new ShopError(403, 'Your session token is invalid. Reload this page.');
            }
        }
        echo json_encode(['data' => $handle($method, $input)], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    } catch (Throwable $error) {
        $status = $error instanceof ShopError ? $error->status : 503;
        http_response_code($status);
        if ($status === 503) error_log('[mcr] Shop request failed (' . get_class($error) . ').');
        echo json_encode(['error' => ['code' => $status, 'message' => $error instanceof ShopError ? $error->getMessage() : 'The service is temporarily unavailable.']]);
    }
}

function shop_text(array $input, string $key, int $max, bool $required = true): string
{
    $value = $input[$key] ?? '';
    if (!is_string($value) || !mb_check_encoding($value, 'UTF-8') || str_contains($value, "\0") || mb_strlen($value) > $max) throw new ShopError(422, "Invalid $key.");
    $value = trim($value);
    if ($required && $value === '') throw new ShopError(422, "$key is required.");
    return $value;
}

function shop_id(mixed $value, int $max = 4294967295): int
{
    if ((!is_int($value) && !is_string($value)) || !preg_match('/\A[1-9][0-9]*\z/', (string) $value)
        || strlen((string) $value) > 10 || (int) $value > $max) throw new ShopError(422, 'Invalid identifier or quantity.');
    return (int) $value;
}

function shop_user(bool $required = true): ?array
{
    $user = null;
    if (isset($_SESSION['user_id'])) {
        $query = database()->prepare('SELECT id, name, email, password_hash FROM users WHERE id = ?');
        $query->execute([$_SESSION['user_id']]);
        $candidate = $query->fetch();
        if ($candidate && hash_equals($_SESSION['auth_version'] ?? '', hash('sha256', $candidate['password_hash']))) {
            $user = ['id' => (int) $candidate['id'], 'name' => $candidate['name'], 'email' => $candidate['email']];
        } else { unset($_SESSION['user_id'], $_SESSION['auth_version']); }
    }
    if ($required && $user === null) throw new ShopError(401, 'Please sign in to continue.');
    return $user;
}

function shop_login(array $user): void
{
    session_regenerate_id(true);
    $_SESSION = ['user_id' => (int) $user['id'], 'auth_version' => hash('sha256', $user['password_hash']), 'csrf' => bin2hex(random_bytes(32)), 'last_active' => time()];
}
