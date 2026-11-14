<?php

declare(strict_types=1);

require_once __DIR__ . '/app/auth.php';

auth_session();
$error = null;
$success = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        auth_check_csrf();
        $name = auth_input('name', 100);
        $email = auth_input('email', 150);
        $password = $_POST['password'] ?? '';
        $confirmation = $_POST['password_confirmation'] ?? '';
        if (!is_string($password) || !is_string($confirmation)
            || strlen($password) < 12 || strlen($password) > 72
            || !hash_equals($password, $confirmation)) {
            throw new InvalidArgumentException('Password must be 12–72 bytes and both fields must match.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Enter a valid email address.');
        }
        database()->prepare(
            'INSERT INTO users (name, email, password_hash, role)
             VALUES (:name, :email, :password_hash, "user")',
        )->execute(['name' => $name, 'email' => $email, 'password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
        $success = 'Registration complete. You can now sign in.';
    } catch (PDOException $exception) {
        $error = (($exception->errorInfo[1] ?? null) === 1062)
            ? 'That email address is already registered.'
            : 'Registration is temporarily unavailable.';
        if (($exception->errorInfo[1] ?? null) !== 1062) {
            error_log('[mcr] Registration failed (PDOException).');
        }
    } catch (Throwable $exception) {
        $error = $exception instanceof InvalidArgumentException
            ? $exception->getMessage()
            : 'Registration is temporarily unavailable.';
        if (!($exception instanceof InvalidArgumentException)) {
            error_log('[mcr] Registration failed (' . get_class($exception) . ').');
        }
    }
}
$csrf = e(auth_csrf());
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>MCR | Register</title><link rel="stylesheet" href="base.css"></head>
<body>
<header class="site-header"><div class="container header-inner"><a class="logo" href="index.html">MCR</a><nav class="main-nav" aria-label="Main navigation"><a href="index.html">Home</a><a href="login.php">Login</a></nav></div></header>
<main class="page"><div class="container"><h1 class="page-title">Register</h1>
<?php if ($error !== null): ?><p class="content-notice" role="alert"><?= e($error) ?></p><?php endif; ?>
<?php if ($success !== null): ?><p class="content-notice" role="status"><?= e($success) ?></p><?php endif; ?>
<form class="checkout-form" method="post" action="register.php"><input type="hidden" name="csrf" value="<?= $csrf ?>">
<section class="checkout-panel"><label>Name<input name="name" maxlength="100" required></label><label>Email<input type="email" name="email" maxlength="150" required></label><label>Password<input type="password" name="password" minlength="12" maxlength="72" required></label><label>Confirm password<input type="password" name="password_confirmation" minlength="12" maxlength="72" required></label><button type="submit">Create account</button></section>
</form></div></main>
</body></html>
