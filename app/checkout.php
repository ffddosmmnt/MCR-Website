<?php
declare(strict_types=1);
require_once __DIR__ . '/shop-api.php';
require_once __DIR__ . '/money.php';
require_once __DIR__ . '/tickets.php';

function checkout_input(array $input): array
{
    $kind = shop_text($input, 'kind', 6);
    if (!in_array($kind, ['ticket', 'merch'], true)) throw new ShopError(422, 'Invalid order kind.');
    $items = $input['items'] ?? null;
    if (!is_array($items) || !array_is_list($items) || count($items) < 1 || count($items) > 20) throw new ShopError(422, 'Select 1–20 items.');
    $normalized = [];
    foreach ($items as $item) {
        if (!is_array($item)) throw new ShopError(422, 'Invalid item.');
        $id = shop_id($item['id'] ?? null);
        if (isset($normalized[$id])) throw new ShopError(422, 'Duplicate item.');
        $normalized[$id] = ['id' => $id, 'quantity' => shop_id($item['quantity'] ?? null, $kind === 'ticket' ? 2 : 2147483647)];
    }
    ksort($normalized, SORT_NUMERIC);
    if ($kind === 'ticket' && array_sum(array_column($normalized, 'quantity')) > 2) throw new ShopError(422, 'At most two tickets per account per concert.');
    return ['kind' => $kind, 'items' => array_values($normalized), 'address' => $kind === 'merch' ? shop_text($input, 'address', 2000) : ''];
}

/** Call within the transaction, after the account row is locked. */
function checkout_quote(PDO $db, array $user, array $input): array
{
    $items = [];
    $total = 0;
    $concertId = null;
    if ($input['kind'] === 'ticket') {
        $lookup = $db->prepare('SELECT concert_id FROM ticket_types WHERE id = ?');
        $lookup->execute([$input['items'][0]['id']]);
        $concertId = $lookup->fetchColumn();
        if ($concertId === false) throw new ShopError(404, 'Ticket type not found.');
        $query = $db->prepare('SELECT id, status, concert_date FROM concerts WHERE id = ? FOR UPDATE');
        $query->execute([$concertId]);
        $concert = $query->fetch();
        if (!$concert || !concert_bookable($concert)) throw new ShopError(409, 'This concert is not open for booking.');
        $query = $db->prepare('SELECT i.quantity FROM ticket_order_items i JOIN ticket_orders o ON o.id = i.order_id JOIN ticket_types t ON t.id = i.ticket_type_id WHERE o.user_id = ? AND t.concert_id = ? FOR UPDATE');
        $query->execute([$user['id'], $concertId]);
        $already = array_sum($query->fetchAll(PDO::FETCH_COLUMN));
        if ($already + array_sum(array_column($input['items'], 'quantity')) > 2) throw new ShopError(409, 'This account has reached the two-ticket limit for this concert.');
    }
    foreach ($input['items'] as $item) {
        if ($input['kind'] === 'ticket') {
            $query = $db->prepare('SELECT id, concert_id, name, price, quota AS available FROM ticket_types WHERE id = ? FOR UPDATE');
        } else {
            // The joined locking read protects the price and variant stock together.
            $query = $db->prepare('SELECT v.id, p.name, p.price, v.size, v.color, v.stock AS available FROM product_variants v JOIN products p ON p.id = v.product_id WHERE v.id = ? FOR UPDATE');
        }
        $query->execute([$item['id']]);
        $record = $query->fetch();
        if (!$record) throw new ShopError(404, 'Selected item not found.');
        if ($input['kind'] === 'ticket' && (int) $record['concert_id'] !== (int) $concertId) throw new ShopError(422, 'Select tickets for one concert per order.');
        if ($item['quantity'] > (int) $record['available']) throw new ShopError(409, 'The selected quantity is no longer available.');
        $price = money_cents($record['price']);
        if ($price > 0 && $item['quantity'] > intdiv(999999999999 - $total, $price)) throw new ShopError(422, 'The order total exceeds the supported amount.');
        $subtotal = $price * $item['quantity'];
        $total += $subtotal;
        $label = $record['name'];
        if ($input['kind'] === 'merch') $label .= ' / ' . implode(' / ', array_filter([$record['size'], $record['color']], fn ($part) => $part !== null && $part !== ''));
        $items[] = ['id' => $item['id'], 'name' => $label, 'quantity' => $item['quantity'], 'unit_price' => $record['price'], 'subtotal' => money_string($subtotal)];
    }
    $quote = ['kind' => $input['kind'], 'currency' => 'IDR', 'items' => $items, 'total_amount' => money_string($total), 'address' => $input['address']];
    $quote['quote_hash'] = hash('sha256', json_encode($quote, JSON_THROW_ON_ERROR));
    return $quote;
}

function checkout_order(PDO $db, int $userId, string $kind, int $id): array
{
    if (!in_array($kind, ['ticket', 'merch'], true)) throw new ShopError(422, 'Invalid order kind.');
    $table = $kind === 'ticket' ? 'ticket_orders' : 'merch_orders';
    $query = $db->prepare("SELECT id, total_amount, status FROM $table WHERE id = ? AND user_id = ?");
    $query->execute([$id, $userId]);
    $order = $query->fetch();
    if (!$order) throw new ShopError(404, 'Order not found.');
    $sql = $kind === 'ticket'
        ? 'SELECT i.ticket_type_id AS id, t.name, i.quantity, i.unit_price, i.subtotal FROM ticket_order_items i JOIN ticket_types t ON t.id = i.ticket_type_id WHERE i.order_id = ? ORDER BY i.id'
        : 'SELECT i.variant_id AS id, p.name, v.size, v.color, i.quantity, i.unit_price, i.subtotal, i.address FROM merch_order_items i JOIN product_variants v ON v.id = i.variant_id JOIN products p ON p.id = v.product_id WHERE i.order_id = ? ORDER BY i.id';
    $query = $db->prepare($sql);
    $query->execute([$id]);
    $order['id'] = (int) $order['id'];
    $order['kind'] = $kind;
    $order['currency'] = 'IDR';
    $order['items'] = $query->fetchAll();
    return $order;
}

function checkout_submit(array $user, array $raw): array
{
    $input = checkout_input($raw);
    $action = shop_text($raw, 'action', 8);
    if (!in_array($action, ['quote', 'create'], true)) throw new ShopError(422, 'Invalid checkout action.');
    $key = $action === 'create' ? shop_text($raw, 'request_key', 64) : '';
    if ($action === 'create' && !preg_match('/\A[a-f0-9]{64}\z/', $key)) throw new ShopError(422, 'Invalid request key.');
    $fingerprint = hash('sha256', json_encode($input, JSON_THROW_ON_ERROR));
    $db = database();
    $db->beginTransaction();
    try {
        // Serializes all checkouts for this account, including separate sessions.
        $query = $db->prepare('SELECT password_hash FROM users WHERE id = ? FOR UPDATE');
        $query->execute([$user['id']]);
        $hash = $query->fetchColumn();
        if (!$hash || !hash_equals($_SESSION['auth_version'] ?? '', hash('sha256', $hash))) throw new ShopError(401, 'Please sign in again.');
        if ($action === 'create') {
            // The locked account already serializes this user's request keys.
            // A locking miss here would gap-lock unrelated users' new keys.
            $query = $db->prepare('SELECT payload_hash, ticket_order_id, merch_order_id FROM checkout_requests WHERE user_id = ? AND request_key = ?');
            $query->execute([$user['id'], $key]);
            if ($previous = $query->fetch()) {
                if (!hash_equals($previous['payload_hash'], $fingerprint)) throw new ShopError(409, 'This request key was used for a different selection.');
                $id = (int) ($previous['ticket_order_id'] ?? $previous['merch_order_id']);
                $order = checkout_order($db, $user['id'], $input['kind'], $id);
                $db->commit();
                return ['order' => $order, 'replayed' => true];
            }
        }
        $quote = checkout_quote($db, $user, $input);
        if ($action === 'quote') { $db->commit(); return ['quote' => $quote]; }
        if (!is_string($raw['quote_hash'] ?? null) || !hash_equals($quote['quote_hash'], $raw['quote_hash'])) throw new ShopError(409, 'The price or selection has changed. Review the order again.');
        $ticket = $input['kind'] === 'ticket';
        $table = $ticket ? 'ticket_orders' : 'merch_orders';
        $query = $db->prepare("INSERT INTO $table (user_id,total_amount,status) VALUES (?,?,'pending')");
        $query->execute([$user['id'], $quote['total_amount']]);
        $orderId = (int) $db->lastInsertId();
        foreach ($quote['items'] as $item) {
            $query = $db->prepare($ticket ? 'UPDATE ticket_types SET quota = quota - ? WHERE id = ? AND quota >= ?' : 'UPDATE product_variants SET stock = stock - ? WHERE id = ? AND stock >= ?');
            $query->execute([$item['quantity'], $item['id'], $item['quantity']]);
            if ($query->rowCount() !== 1) throw new ShopError(409, 'The selected quantity is no longer available.');
            if ($ticket) {
                $query = $db->prepare('INSERT INTO ticket_order_items (order_id,ticket_type_id,quantity,unit_price,subtotal) VALUES (?,?,?,?,?)');
                $query->execute([$orderId, $item['id'], $item['quantity'], $item['unit_price'], $item['subtotal']]);
            } else {
                $query = $db->prepare('INSERT INTO merch_order_items (order_id,variant_id,quantity,unit_price,subtotal,address) VALUES (?,?,?,?,?,?)');
                $query->execute([$orderId, $item['id'], $item['quantity'], $item['unit_price'], $item['subtotal'], $input['address']]);
            }
        }
        $query = $db->prepare('INSERT INTO checkout_requests (user_id,request_key,payload_hash,ticket_order_id,merch_order_id) VALUES (?,?,?,?,?)');
        $query->execute([$user['id'], $key, $fingerprint, $ticket ? $orderId : null, $ticket ? null : $orderId]);
        $order = checkout_order($db, $user['id'], $input['kind'], $orderId);
        $db->commit();
        return ['order' => $order, 'replayed' => false];
    } catch (Throwable $error) {
        if ($db->inTransaction()) $db->rollBack();
        throw $error;
    }
}
