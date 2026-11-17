<?php
declare(strict_types=1);
require_once __DIR__ . '/shop-api.php';

function interaction_rate(string $kind): void
{
    $window = $_SESSION['interaction_rate'][$kind] ?? ['start' => time(), 'count' => 0];
    if (time() - $window['start'] >= 60) $window = ['start' => time(), 'count' => 0];
    if ($window['count'] >= ($kind === 'contact' ? 5 : 10)) throw new ShopError(429, 'Too many submissions. Please try again in a minute.');
    $window['count']++;
    $_SESSION['interaction_rate'][$kind] = $window;
}

function interaction_submit(array $input): array
{
    $kind = shop_text($input, 'kind', 12);
    if (!in_array($kind, ['contact', 'subscribe'], true)) throw new ShopError(422, 'Invalid form.');
    $email = shop_text($input, 'email', 150);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new ShopError(422, 'Enter a valid email address.');
    $db = database();
    if ($kind === 'subscribe') {
        if (($input['consent'] ?? false) !== true) throw new ShopError(422, 'Please agree to join the newsletter.');
        interaction_rate($kind);
        $query = $db->prepare('INSERT INTO newsletter_subscriptions (email) VALUES (?)');
        try { $query->execute([$email]); }
        catch (PDOException $error) {
            if (($error->errorInfo[1] ?? null) !== 1062) throw $error;
        }
        // Do not disclose whether an address was previously subscribed.
        return ['message' => 'Your newsletter subscription has been recorded.'];
    }
    $name = shop_text($input, 'name', 100);
    $subject = shop_text($input, 'subject', 200);
    $message = shop_text($input, 'message', 5000);
    $key = shop_text($input, 'request_key', 64);
    if (!preg_match('/\A[a-f0-9]{64}\z/', $key)) throw new ShopError(422, 'Invalid request key.');
    $hash = hash('sha256', json_encode([$name, $email, $subject, $message], JSON_THROW_ON_ERROR));
    $lookup = $db->prepare('SELECT payload_hash FROM contact_messages WHERE request_key = ?');
    $lookup->execute([$key]);
    $previous = $lookup->fetchColumn();
    if ($previous !== false) {
        if (!hash_equals($previous, $hash)) throw new ShopError(409, 'This request was already used for another message.');
        return ['message' => 'Your message has been received.'];
    }
    interaction_rate($kind);
    $query = $db->prepare('INSERT INTO contact_messages (name,email,subject,message,request_key,payload_hash) VALUES (?,?,?,?,?,?)');
    try { $query->execute([$name, $email, $subject, $message, $key, $hash]); }
    catch (PDOException $error) {
        if (($error->errorInfo[1] ?? null) !== 1062) throw $error;
        // A simultaneous retry on another session may have won the unique key.
        $lookup->execute([$key]);
        if (!hash_equals((string) $lookup->fetchColumn(), $hash)) throw new ShopError(409, 'This request was already used for another message.');
    }
    return ['message' => 'Your message has been received.'];
}
