<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/shop-api.php';
shop_api(function (string $method, array $input): array {
    if ($method === 'GET') return ['user' => shop_user(false), 'csrf' => $_SESSION['csrf']];
    $action = shop_text($input, 'action', 12);
    if ($action === 'logout') {
        // Signing out must not bypass an active failed-login cooldown.
        $_SESSION = array_intersect_key($_SESSION, array_flip(['login_failures', 'login_blocked_until']));
        session_regenerate_id(true);
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        return ['user' => null, 'csrf' => $_SESSION['csrf']];
    }
    if (!in_array($action, ['login', 'register'], true)) throw new ShopError(422, 'Invalid account action.');
    if (($_SESSION['login_blocked_until'] ?? 0) > time()) throw new ShopError(429, 'Too many attempts. Try again in one minute.');
    $email = shop_text($input, 'email', 150);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new ShopError(422, 'Enter a valid email address.');
    $password = $input['password'] ?? null;
    if (!is_string($password) || strlen($password) < 12 || strlen($password) > 72 || str_contains($password, "\0")) throw new ShopError(422, 'Password must contain 12–72 bytes.');
    if ($action === 'register') {
        $name = shop_text($input, 'name', 100);
        $query = database()->prepare("INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,'user')");
        try { $query->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]); }
        catch (PDOException $error) {
            if (($error->errorInfo[1] ?? null) === 1062) throw new ShopError(409, 'This email is already registered.');
            throw $error;
        }
    }
    $query = database()->prepare('SELECT id, name, email, role, password_hash FROM users WHERE email = ?');
    $query->execute([$email]);
    $user = $query->fetch();
    $valid = password_verify($password, $user['password_hash'] ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.');
    if (!$user || !$valid) {
        $_SESSION['login_failures'] = ($_SESSION['login_failures'] ?? 0) + 1;
        if ($_SESSION['login_failures'] >= 5) {
            $_SESSION['login_blocked_until'] = time() + 60;
            $_SESSION['login_failures'] = 0;
        }
        throw new ShopError(401, 'Email or password is incorrect.');
    }
    if (!in_array($user['role'], ['user', 'admin'], true)) throw new ShopError(403, 'Account access is unavailable.');
    shop_login($user);
    return ['user' => shop_user(), 'csrf' => $_SESSION['csrf']];
});
