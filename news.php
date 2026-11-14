<?php

declare(strict_types=1);

require_once __DIR__ . '/app/engagement.php';

engagement_session();
$message = null;
$status = 200;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        engagement_check_csrf();
        $type = $_POST['form_type'] ?? '';
        if ($type === 'newsletter') {
            $email = engagement_string('email', 150);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('Enter a valid email address.');
            }
            $delivered = save_newsletter($email);
            $message = $delivered === true
                ? 'You are subscribed. A notification was sent to the configured mailbox.'
                : ($delivered === null
                    ? 'You are subscribed. Email notification is not configured on this server.'
                    : 'You are subscribed, but the email notification could not be delivered.');
        } elseif ($type === 'contact') {
            $name = engagement_string('name', 100);
            $email = engagement_string('contact_email', 150);
            $subject = engagement_string('subject', 150);
            $body = engagement_string('message', 5000);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('Enter a valid email address.');
            }
            $delivered = save_contact($name, $email, $subject, $body);
            $message = $delivered === true
                ? 'Your message was saved and sent to the configured mailbox.'
                : ($delivered === null
                    ? 'Your message was saved. Email notification is not configured on this server.'
                    : 'Your message was saved, but the email notification could not be delivered.');
        } else {
            throw new InvalidArgumentException('Unknown form.');
        }
    } catch (Throwable $exception) {
        $status = $exception instanceof InvalidArgumentException ? 422 : 503;
        if ($status === 503) {
            error_log('[mcr] Engagement form failed (' . get_class($exception) . ').');
        }
        $message = $exception instanceof InvalidArgumentException
            ? $exception->getMessage()
            : 'The form could not be processed right now.';
    }
}

$token = e(engagement_csrf());
$template = file_get_contents(__DIR__ . '/news.html');
if ($template === false) {
    http_response_code(503);
    echo 'News is temporarily unavailable.';
    return;
}
http_response_code($status);
$feedback = $message === null ? '' : '<div class="container"><p class="content-notice" role="' . ($status >= 400 ? 'alert' : 'status') . '">' . e($message) . '</p></div>';
$template = str_replace('<!--CSRF_TOKEN-->', '<input type="hidden" name="csrf" value="' . $token . '">', $template);
$template = str_replace('</main>', $feedback . '</main>', $template);
header('Content-Type: text/html; charset=utf-8');
echo $template;
