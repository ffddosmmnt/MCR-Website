<?php

declare(strict_types=1);

require_once __DIR__ . '/app/tickets.php';
require_once __DIR__ . '/app/engagement.php';

engagement_session();
$error = null;
$success = null;
$types = [];
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        engagement_check_csrf();
        $userId = ticket_session_user_id();
        if ($userId === null) {
            http_response_code(401);
            throw new RuntimeException('Please sign in before placing a ticket order.');
        }
        $ticketTypeId = filter_var($_POST['ticket_type_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $quantity = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 6]]);
        if ($ticketTypeId === false || $quantity === false) {
            throw new InvalidArgumentException('Choose a ticket type and a quantity from 1 to 6.');
        }
        $orderId = create_ticket_order($userId, $ticketTypeId, $quantity);
        $success = 'Order #' . $orderId . ' was placed. Payment is not collected by this demo.';
    }
    $types = ticket_types();
} catch (Throwable $exception) {
    if (http_response_code() < 400) {
        http_response_code($exception instanceof DomainException ? 409 : 422);
    }
    $error = $exception instanceof RuntimeException && $exception->getMessage() !== 'Please sign in before placing a ticket order.'
        ? 'Ticket service is temporarily unavailable.'
        : $exception->getMessage();
    if (!($exception instanceof InvalidArgumentException || $exception instanceof DomainException || $exception instanceof RuntimeException)) {
        error_log('[mcr] Ticket order failed (' . get_class($exception) . ').');
        $error = 'Ticket service is temporarily unavailable.';
    }
}
if ($types === []) {
    try { $types = ticket_types(); } catch (Throwable $exception) { $error ??= 'Ticket schedule is temporarily unavailable.'; }
}
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>MCR | Tickets</title><link rel="stylesheet" href="base.css"><link rel="stylesheet" href="ticket-transaction.css"></head>
<body>
<header class="site-header checkout-header"><div class="container header-inner"><a class="logo" href="index.html">MCR</a><nav class="main-nav" aria-label="Main navigation"><a href="index.html">Home</a><a href="about.html">About</a><a href="music.php">Music</a><a class="active" href="tour.php">Tour</a><a href="showcase.php">Store</a><a href="news.html">News</a></nav><div class="header-icons"><a href="news.html#newsletter">Newsletter</a></div></div></header>
<main class="transaction-page"><div class="container"><h1 class="page-title">Ticket Booking</h1><p class="content-notice">Ticket orders require an authenticated user session. No card or CVV data is collected.</p>
<?php if ($error !== null): ?><p class="content-notice" role="alert"><?= e($error) ?></p><?php endif; ?>
<?php if ($success !== null): ?><p class="content-notice" role="status"><?= e($success) ?></p><?php endif; ?>
<form class="checkout-form" method="post" action="ticket-transaction.php">
    <input type="hidden" name="csrf" value="<?= e(engagement_csrf()) ?>">
    <section class="checkout-panel"><h2>Choose a ticket</h2><div class="field-grid">
        <label class="wide">Ticket type<select name="ticket_type_id" required><option value="">Select a ticket</option><?php foreach ($types as $type): ?><option value="<?= (int) $type['id'] ?>" <?= (int) $type['quota'] < 1 ? 'disabled' : '' ?>><?= e($type['name'] . ' · ' . $type['venue_name'] . ' · $' . $type['price'] . ' · ' . $type['quota'] . ' left') ?></option><?php endforeach; ?></select></label>
        <label>Quantity<input type="number" name="quantity" min="1" max="6" value="1" required></label>
    </div></section>
    <section class="checkout-panel"><h2>Order policy</h2><p>Prices and quota are read from MySQL at submit time. The order and quota update are committed together; payment remains outside this demo.</p><button type="submit">Place Ticket Order</button></section>
</form></div></main>
<footer class="site-footer"><div class="container footer-inner"><div><strong>My Chemical Romance</strong><span>Secure server-side ticket booking.</span></div><a href="news.html#newsletter">Contact support</a></div></footer>
<script src="script.js"></script>
</body></html>
