<?php

declare(strict_types=1);

require_once __DIR__ . '/app/auth.php';

auth_session();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo 'Use POST to sign out.';
    exit;
}
try {
    auth_check_csrf();
} catch (Throwable $exception) {
    http_response_code(403);
    echo 'Invalid logout request.';
    exit;
}
auth_logout();
header('Location: index.html', true, 303);
