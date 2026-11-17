<?php
declare(strict_types=1);
require_once __DIR__ . '/internal/admin.php';
admin_session();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    admin_fail(405, 'Method not allowed.');
}
admin_csrf();
$_SESSION = [];
session_destroy();
setcookie(session_name(), '', ['expires' => time() - 3600, 'path' => '/', 'httponly' => true, 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'samesite' => 'Lax']);
admin_redirect('login.php');
