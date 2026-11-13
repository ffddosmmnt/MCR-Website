<?php

declare(strict_types=1);

/** Administrative records only: item edits do not reserve stock or ticket quota. */
function admin_order_family(string $table): ?array
{
    return match ($table) {
        'merch_orders', 'merch_order_items' => ['merch_orders', 'merch_order_items'],
        'ticket_orders', 'ticket_order_items' => ['ticket_orders', 'ticket_order_items'],
        default => null,
    };
}

/** Lock parents before items, including both parents when an item is moved. */
function admin_lock_orders(PDO $db, string $table, string $action, int $id): array
{
    $family = admin_order_family($table);
    if ($family === null) {
        return [];
    }
    [$orders, $items] = $family;
    $ids = [];
    if ($table === $items) {
        if ($action !== 'create') {
            $ids[] = (int) admin_record($db, $items, $id)['order_id'];
        }
        if ($action !== 'delete') {
            $ids[] = admin_id(admin_string($_POST, 'order_id'));
        }
    } elseif ($action !== 'create') {
        $ids[] = $id;
    }
    $ids = array_values(array_unique($ids));
    sort($ids, SORT_NUMERIC);
    foreach ($ids as $orderId) {
        admin_record($db, $orders, $orderId, true);
    }
    return $ids;
}

/** DECIMAL(12,2), expressed as integer cents; never calculate money with floats. */
function admin_money_cents(string $value): int
{
    if (!preg_match('/\A([0-9]{1,10})(?:\.([0-9]{1,2}))?\z/', $value, $parts)) {
        throw new InvalidArgumentException('Amount exceeds the supported DECIMAL(12,2) range.');
    }
    return (int) $parts[1] * 100 + (int) str_pad($parts[2] ?? '', 2, '0');
}

function admin_money_string(int $cents): string
{
    return intdiv($cents, 100) . '.' . str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
}

function admin_item_subtotal(array $values): string
{
    $price = admin_money_cents($values['unit_price']);
    $quantity = (int) $values['quantity'];
    if ($price > 0 && $quantity > intdiv(999999999999, $price)) {
        throw new InvalidArgumentException('Item subtotal exceeds the supported amount.');
    }
    return admin_money_string($price * $quantity);
}

function admin_update_order_totals(PDO $db, string $table, array $ids): void
{
    $family = admin_order_family($table);
    if ($family === null) {
        return;
    }
    [$orders, $items] = $family;
    foreach ($ids as $id) {
        // A locking read sees the latest rows after waiting for the parent lock.
        $stmt = $db->prepare("SELECT subtotal FROM `$items` WHERE order_id = ? ORDER BY id FOR UPDATE");
        $stmt->execute([$id]);
        $total = 0;
        foreach ($stmt as $item) {
            $subtotal = admin_money_cents($item['subtotal']);
            if ($subtotal > 999999999999 - $total) {
                throw new InvalidArgumentException('Order total exceeds the supported amount.');
            }
            $total += $subtotal;
        }
        $db->prepare("UPDATE `$orders` SET total_amount = ? WHERE id = ?")->execute([admin_money_string($total), $id]);
    }
}
