<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/view.php';
require_once __DIR__ . '/images.php';
require_once __DIR__ . '/orders.php';

/** Fixed schema allowlist: request parameters never become SQL identifiers. */
function admin_modules(): array
{
    return [
        'band_members' => ['Band members', ['name' => ['Name', 'text', 100], 'instrument' => ['Instrument', 'text', 100, true], 'cover_img' => ['Image', 'image', 65535, true]]],
        'albums' => ['Albums', ['title' => ['Title', 'text', 150], 'release_date' => ['Release date', 'date', 10, true], 'cover_image' => ['Cover image', 'image', 65535, true]]],
        'songs' => ['Songs', ['album_id' => ['Album', 'relation', 'albums'], 'title' => ['Title', 'text', 150], 'lyrics' => ['Lyrics', 'textarea', 65535, true]]],
        'news_articles' => ['News', ['title' => ['Title', 'text', 200], 'slug' => ['Slug', 'slug', 220], 'content' => ['Content', 'textarea', 65535, true], 'news_date' => ['News date', 'datetime-local', 19, true]]],
        'venues' => ['Venues', ['name' => ['Name', 'text', 150], 'city' => ['City', 'text', 100], 'country' => ['Country', 'text', 100]]],
        'concerts' => ['Concerts', ['venue_id' => ['Venue', 'relation', 'venues'], 'concert_date' => ['Concert date (WIB)', 'timestamp', 19, true], 'status' => ['Status', 'text', 30]]],
        'ticket_types' => ['Ticket types', ['concert_id' => ['Concert', 'relation', 'concerts'], 'name' => ['Name', 'text', 100], 'price' => ['Price', 'money', 12], 'quota' => ['Quota', 'integer', 2147483647]]],
        'categories' => ['Categories', ['name' => ['Name', 'text', 100]]],
        'products' => ['Products', ['category_id' => ['Category', 'relation', 'categories'], 'name' => ['Name', 'text', 150], 'price' => ['Price', 'money', 12], 'details' => ['Details', 'textarea', 65535, true]]],
        'product_variants' => ['Product variants', ['product_id' => ['Product', 'relation', 'products'], 'size' => ['Size', 'text', 30, true], 'color' => ['Color', 'text', 50, true], 'stock' => ['Stock', 'integer', 2147483647]]],
        'users' => ['Users', ['name' => ['Name', 'text', 100], 'email' => ['Email', 'email', 150], 'password_hash' => ['Password', 'password', 72], 'role' => ['Role', 'choice', ['user', 'admin']], 'time_stamp' => ['Recorded (UTC)', 'generated', 19], 'created_at' => ['Created at', 'generated', 19]]],
        'merch_orders' => ['Merch orders', ['user_id' => ['User', 'relation', 'users'], 'status' => ['Status', 'text', 30], 'total_amount' => ['Total amount', 'calculated', 12]]],
        'merch_order_items' => ['Merch order items', ['order_id' => ['Order', 'relation', 'merch_orders'], 'variant_id' => ['Product variant', 'relation', 'product_variants'], 'quantity' => ['Quantity', 'positive_integer', 2147483647], 'unit_price' => ['Unit price', 'money', 12], 'subtotal' => ['Subtotal', 'calculated', 12], 'address' => ['Address', 'textarea', 65535, true], 'time_stamp' => ['Recorded (UTC)', 'generated', 19], 'created_at' => ['Created at', 'generated', 19]]],
        'ticket_orders' => ['Ticket orders', ['user_id' => ['User', 'relation', 'users'], 'status' => ['Status', 'text', 30], 'total_amount' => ['Total amount', 'calculated', 12]]],
        'ticket_order_items' => ['Ticket order items', ['order_id' => ['Order', 'relation', 'ticket_orders'], 'ticket_type_id' => ['Ticket type', 'relation', 'ticket_types'], 'quantity' => ['Quantity', 'positive_integer', 2147483647], 'unit_price' => ['Unit price', 'money', 12], 'subtotal' => ['Subtotal', 'calculated', 12], 'order_code' => ['Order code', 'signed_integer', 2147483647, true], 'time_stamp' => ['Recorded (UTC)', 'generated', 19], 'created_at' => ['Created at', 'generated', 19]]],
    ];
}

function admin_session(): void
{
    $sessionDirectory = __DIR__ . '/sessions';
    if (!is_dir($sessionDirectory) && !@mkdir($sessionDirectory, 0700) && !is_dir($sessionDirectory)) {
        admin_fail(503, 'Session storage is unavailable.');
    }
    session_save_path($sessionDirectory);
    ini_set('session.use_strict_mode', '1');
    // Session is used only for form tokens, flash messages and edit snapshots.
    session_name('mcr_dashboard');
    session_set_cookie_params(['httponly' => true, 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'samesite' => 'Lax', 'path' => '/']);
    if (!session_start()) {
        admin_fail(503, 'Session storage is unavailable.');
    }
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
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

function admin_head(string $title): void
{
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="en" data-bs-theme="dark"><head><meta charset="utf-8"><meta name="color-scheme" content="dark"><meta name="viewport" content="width=device-width, initial-scale=1"><title>' . e($title) . ' | MCR Admin</title><link rel="stylesheet" href="adminlte.min.css"><link rel="stylesheet" href="template.css"></head><body>';
}

function admin_string(array $input, string $key, string $default = ''): string
{
    $value = $input[$key] ?? $default;
    if (!is_string($value) || !mb_check_encoding($value, 'UTF-8') || str_contains($value, "\0")) {
        throw new InvalidArgumentException('Invalid input: ' . $key . '.');
    }
    return trim($value);
}

function admin_id(string $value): int
{
    if (!preg_match('/\A[1-9][0-9]{0,9}\z/', $value) || (int) $value > 4294967295) {
        throw new InvalidArgumentException('Invalid record ID.');
    }
    return (int) $value;
}

function admin_record(PDO $db, string $table, int $id, bool $lock = false): array
{
    $stmt = $db->prepare("SELECT * FROM `$table` WHERE id = ?" . ($lock ? ' FOR UPDATE' : ''));
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) {
        throw new OutOfBoundsException('The record no longer exists.');
    }
    return $row;
}

function admin_version(array $record): string
{
    return hash_hmac('sha256', json_encode($record, JSON_THROW_ON_ERROR), $_SESSION['csrf']);
}

function admin_values(PDO $db, array $fields, array $input, array $current = []): array
{
    $values = [];
    foreach ($fields as $key => [$label, $type, $max]) {
        if (in_array($type, ['generated', 'calculated'], true)) {
            continue;
        }
        if ($type === 'password') {
            $password = $input[$key] ?? '';
            if (!is_string($password) || str_contains($password, "\0") || !mb_check_encoding($password, 'UTF-8')) {
                throw new InvalidArgumentException('Invalid password.');
            }
            if ($password === '' && $current) {
                $values[$key] = $current[$key];
                continue;
            }
            if (strlen($password) < 12 || strlen($password) > 72) {
                throw new InvalidArgumentException('Password must contain 12–72 bytes.');
            }
            $values[$key] = password_hash($password, PASSWORD_DEFAULT);
            continue;
        }
        $value = admin_string($input, $key);
        if ($type === 'image' && $value !== '') {
            throw new InvalidArgumentException('Use the file selector to upload an image.');
        }
        if ($value === '' && ($fields[$key][3] ?? false)) {
            $values[$key] = null;
            continue;
        }
        if ($value === '') {
            throw new InvalidArgumentException($label . ' is required.');
        }
        if ($type === 'choice') {
            if (!in_array($value, $max, true)) {
                throw new InvalidArgumentException('Invalid ' . strtolower($label) . '.');
            }
        } elseif ($type === 'relation') {
            $value = admin_id($value);
            admin_record($db, $max, $value);
        } elseif (in_array($type, ['integer', 'positive_integer', 'signed_integer'], true)) {
            $minimum = match ($type) { 'positive_integer' => 1, 'signed_integer' => -2147483648, default => 0 };
            if (!preg_match('/\A-?[0-9]{1,10}\z/', $value) || (int) $value < $minimum || (int) $value > $max) {
                throw new InvalidArgumentException($label . ' must be an integer from ' . $minimum . ' to ' . $max . '.');
            }
        } elseif ($type === 'money') {
            if (!preg_match('/\A[0-9]{1,10}(?:\.[0-9]{1,2})?\z/', $value)) {
                throw new InvalidArgumentException($label . ' must be non-negative with up to 10 digits and 2 decimal places.');
            }
        } elseif (in_array($type, ['date', 'datetime-local', 'timestamp'], true)) {
            $format = $type === 'date' ? 'Y-m-d' : 'Y-m-d\TH:i:s';
            if ($type !== 'date' && strlen($value) === 16) {
                $value .= ':00';
            }
            $date = DateTimeImmutable::createFromFormat('!' . $format, $value, new DateTimeZone('Asia/Jakarta'));
            if (!$date || $date->format($format) !== $value || (int) $date->format('Y') < 1000) {
                throw new InvalidArgumentException($label . ' is not a valid date.');
            }
            if ($type === 'timestamp') {
                $date = $date->setTimezone(new DateTimeZone('UTC'));
                if ($date->getTimestamp() < 1 || $date->getTimestamp() > 2147483647) {
                    throw new InvalidArgumentException($label . ' is outside the MySQL TIMESTAMP range.');
                }
            }
            $value = $date->format($type === 'date' ? 'Y-m-d' : 'Y-m-d H:i:s');
        } else {
            if (mb_strlen($value, 'UTF-8') > $max || strlen($value) > 65535) {
                throw new InvalidArgumentException($label . ' is too long.');
            }
            if ($type === 'slug' && !preg_match('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/', $value)) {
                throw new InvalidArgumentException('Slug must contain lowercase letters, numbers, and single hyphens.');
            }
            if ($type === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('Enter a valid email address.');
            }
        }
        $values[$key] = $value;
    }
    return $values;
}

function admin_save(PDO $db, string $table, array $fields, string $action, int $id): void
{
    $current = [];
    $newUploads = [];
    $commitStarted = false;
    $family = admin_order_family($table);
    $db->beginTransaction();
    try {
        $orderIds = admin_lock_orders($db, $table, $action, $id);
        $adminRows = $table === 'users'
            ? $db->query("SELECT id FROM users WHERE role = 'admin' ORDER BY id FOR UPDATE")->fetchAll()
            : [];
        if ($action !== 'create') {
            $current = admin_record($db, $table, $id, true);
            if (!hash_equals(admin_version($current), admin_string($_POST, 'version'))) {
                throw new DomainException('This record changed in another session. Reload before editing or deleting.');
            }
        }
        if ($action === 'delete') {
            if ($table === 'users' && $current['role'] === 'admin' && count($adminRows) < 2) {
                throw new DomainException('The last administrator account cannot be deleted.');
            }
            $stmt = $db->prepare("DELETE FROM `$table` WHERE id = ?");
            $stmt->execute([$id]);
        } else {
            $values = admin_values($db, $fields, $_POST, $current);
            if ($family !== null && $table === $family[1]) {
                $values['subtotal'] = admin_item_subtotal($values);
            }
            if ($table === 'users' && ($current['role'] ?? '') === 'admin' && $values['role'] !== 'admin' && count($adminRows) < 2) {
                throw new DomainException('The last administrator must keep the admin role.');
            }
            foreach ($fields as $column => $field) {
                if ($field[1] === 'image') {
                    $uploaded = admin_upload_image($column);
                    if ($uploaded !== null) {
                        $newUploads[] = $uploaded;
                    }
                    $values[$column] = $uploaded ?? ($current[$column] ?? null);
                }
            }
            $columns = array_keys($values);
            if ($action === 'create') {
                $sql = "INSERT INTO `$table` (`" . implode('`,`', $columns) . '`) VALUES (' . implode(',', array_fill(0, count($columns), '?')) . ')';
            } else {
                $sql = "UPDATE `$table` SET " . implode(',', array_map(fn ($column) => "`$column` = ?", $columns)) . ' WHERE id = ?';
                $values['id'] = $id;
            }
            $db->prepare($sql)->execute(array_values($values));
        }
        if ($family !== null && !($action === 'delete' && $table === $family[0])) {
            admin_update_order_totals($db, $table, $orderIds);
        }
        $commitStarted = true;
        $db->commit();
    } catch (Throwable $error) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        // A lost connection during COMMIT can have an unknown outcome; retain the file then.
        if (!$commitStarted) {
            foreach ($newUploads as $path) {
                admin_discard_upload($path);
            }
        }
        throw $error;
    }
}

function admin_input_value(mixed $value, string $type): string
{
    if ($type === 'password') {
        return '';
    }
    if ($value === null) {
        return '';
    }
    if ($type === 'timestamp') {
        return (new DateTimeImmutable((string) $value, new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Asia/Jakarta'))->format('Y-m-d\TH:i:s');
    }
    return $type === 'datetime-local' ? str_replace(' ', 'T', (string) $value) : (string) $value;
}

function admin_label(array $record): string
{
    $label = $record['title'] ?? $record['name'] ?? null;
    if ($label === null && array_key_exists('concert_date', $record)) {
        $label = $record['concert_date'] === null ? 'Date to be announced' : date_label($record['concert_date'], true);
    }
    if ($label === null && isset($record['user_id'])) {
        $label = 'User #' . $record['user_id'] . ' · ' . $record['status'] . ' · ' . $record['total_amount'];
    }
    if ($label === null && isset($record['product_id'])) {
        $label = 'Product #' . $record['product_id'] . ' · ' . trim(($record['size'] ?? '') . ' ' . ($record['color'] ?? ''));
    }
    $label ??= trim(($record['size'] ?? '') . ' ' . ($record['color'] ?? '')) ?: 'Record';
    return '#' . $record['id'] . ' · ' . $label;
}
