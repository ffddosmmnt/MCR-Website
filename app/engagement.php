<?php

declare(strict_types=1);

require_once __DIR__ . '/view.php';

function engagement_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_name('mcr_engagement');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
    $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

function engagement_csrf(): string
{
    engagement_session();
    return (string) $_SESSION['csrf'];
}

function engagement_string(string $key, int $max): string
{
    $value = $_POST[$key] ?? '';
    if (!is_string($value) || !mb_check_encoding($value, 'UTF-8') || str_contains($value, "\0")) {
        throw new InvalidArgumentException('Invalid form input.');
    }
    $value = trim($value);
    if ($value === '' || mb_strlen($value, 'UTF-8') > $max) {
        throw new InvalidArgumentException('Please complete all fields within the allowed length.');
    }
    return $value;
}

function engagement_check_csrf(): void
{
    engagement_session();
    if (!is_string($_POST['csrf'] ?? null) || !hash_equals((string) $_SESSION['csrf'], $_POST['csrf'])) {
        throw new RuntimeException('Your form session expired. Please try again.');
    }
}

function engagement_notify(string $subject, string $body): ?bool
{
    $recipient = getenv('MAIL_TO');
    if ($recipient === false || !filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
        return null;
    }
    $safeSubject = preg_replace('/[\r\n]+/', ' ', $subject) ?? 'MCR website message';
    return mail($recipient, $safeSubject, $body, "Content-Type: text/plain; charset=UTF-8\r\n");
}

function save_newsletter(string $email): ?bool
{
    $db = database();
    $stmt = $db->prepare(
        'INSERT INTO newsletter_subscribers (email) VALUES (:email)
         ON DUPLICATE KEY UPDATE status = \'active\'',
    );
    $stmt->execute(['email' => $email]);
    return engagement_notify('MCR newsletter subscription', "New newsletter subscriber: {$email}");
}

function save_contact(string $name, string $email, string $subject, string $message): ?bool
{
    database()->prepare(
        'INSERT INTO contact_messages (name, email, subject, message)
         VALUES (:name, :email, :subject, :message)',
    )->execute(compact('name', 'email', 'subject', 'message'));
    return engagement_notify(
        'MCR contact message: ' . $subject,
        "From: {$name} <{$email}>\n\n{$message}",
    );
}
