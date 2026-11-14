<?php

declare(strict_types=1);

require_once __DIR__ . '/app/auth.php';

auth_session();
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        auth_check_csrf();
        $attempts = $_SESSION['login_attempts'] ?? ['count' => 0, 'started' => time()];
        if (!is_array($attempts) || time() - (int) ($attempts['started'] ?? 0) > 60) {
            $attempts = ['count' => 0, 'started' => time()];
        }
        if ((int) $attempts['count'] >= 5) {
            throw new RuntimeException('Too many login attempts. Please wait one minute.');
        }
        $email = auth_input('email', 150);
        $password = $_POST['password'] ?? '';
        if (!is_string($password) || $password === '' || strlen($password) > 72) {
            throw new InvalidArgumentException('Enter a valid email and password.');
        }
        $query = database()->prepare('SELECT id, password_hash FROM users WHERE email = :email LIMIT 1');
        $query->execute(['email' => $email]);
        $user = $query->fetch();
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $_SESSION['login_attempts'] = ['count' => (int) $attempts['count'] + 1, 'started' => $attempts['started']];
            throw new InvalidArgumentException('The email or password is incorrect.');
        }
        unset($_SESSION['login_attempts']);
        auth_login((int) $user['id']);
        header('Location: ' . auth_redirect_target(), true, 303);
        exit;
    } catch (Throwable $exception) {
        $error = $exception instanceof InvalidArgumentException
            || ($exception instanceof RuntimeException && !($exception instanceof PDOException))
            ? $exception->getMessage()
            : 'Login is temporarily unavailable.';
        if (!($exception instanceof InvalidArgumentException)) {
            error_log('[mcr] Login failed (' . get_class($exception) . ').');
        }
    }
}
$csrf = e(auth_csrf());
$redirect = e(auth_redirect_target());
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>MCR | Login</title><link rel="stylesheet" href="base.css"></head>
<body>
<header class="site-header"><div class="container header-inner"><a class="logo" href="index.html">MCR</a><nav class="main-nav" aria-label="Main navigation"><a href="index.html">Home</a><a href="register.php">Register</a><a href="login.php?redirect=%2Fdashboard%2F">Admin login</a></nav></div></header>
<main class="page"><div class="container"><h1 class="page-title">Login</h1>
<?php if ($error !== null): ?><p class="content-notice" role="alert"><?= e($error) ?></p><?php endif; ?>
<form class="checkout-form" method="post" action="login.php"><input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="redirect" value="<?= $redirect ?>">
<section class="checkout-panel"><label>Email<input type="email" name="email" maxlength="150" required></label><label>Password<input type="password" name="password" maxlength="72" required></label><button type="submit">Sign in</button></section>
</form></div></main>
</body></html>
