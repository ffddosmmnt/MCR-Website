<?php
declare(strict_types=1);
admin_session();
$error = '';
if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'HEAD', 'POST'], true)) {
    header('Allow: GET, HEAD, POST');
    admin_fail(405, 'Method not allowed.');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_SESSION['user_id'])) {
    try {
        $user = shop_user(false);
        if ($user !== null && $user['role'] === 'admin') {
            admin_redirect();
        }
    } catch (Throwable $error) {
        admin_fail(503, 'Sign in is temporarily unavailable.');
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf();
    try {
        if (time() < ($_SESSION['login_blocked_until'] ?? 0)) {
            admin_fail(429, 'Too many attempts. Try again in a minute.');
        }
        $email = admin_string($_POST, 'email');
        $password = $_POST['password'] ?? null;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150 || !is_string($password) || strlen($password) > 72 || str_contains($password, "\0")) {
            throw new InvalidArgumentException('Invalid email or password.');
        }
        $stmt = database()->prepare('SELECT id,password_hash,role FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        $valid = password_verify($password, $user['password_hash'] ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.');
        if (!$user || !$valid || $user['role'] !== 'admin') {
            throw new InvalidArgumentException('Invalid email or password.');
        }
        shop_login($user);
        admin_redirect();
    } catch (InvalidArgumentException $exception) {
        $_SESSION['login_failures'] = ($_SESSION['login_failures'] ?? 0) + 1;
        if ($_SESSION['login_failures'] >= 5) {
            $_SESSION['login_blocked_until'] = time() + 60;
            $_SESSION['login_failures'] = 0;
        }
        http_response_code(401);
        $error = 'Invalid email or password.';
    } catch (Throwable $exception) {
        error_log('[mcr] Admin login unavailable (' . get_class($exception) . ').');
        http_response_code(503);
        $error = 'Sign in is temporarily unavailable.';
    }
}
admin_head('Sign in');
?>
<main class="login-page"><div class="login-box"><h1 class="login-logo">MCR Admin</h1><div class="card"><div class="login-card-body">
<p class="login-box-msg">Sign in to manage band data</p>
<?php if ($error): ?><div class="alert alert-danger" role="alert"><?= e($error) ?></div><?php endif ?>
<form method="post" action="login.php">
<input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
<div class="mb-3"><label for="email" class="form-label">Email</label><input class="form-control" id="email" name="email" type="email" maxlength="150" autocomplete="username" required></div>
<div class="mb-3"><label for="password" class="form-label">Password</label><input class="form-control" id="password" name="password" type="password" maxlength="72" autocomplete="current-password" required></div>
<button class="btn btn-primary w-100" type="submit">Sign in</button>
</form><p class="mt-3 mb-0"><a href="../index.php">View website</a></p>
</div></div></div></main></body></html>
