<?php

declare(strict_types=1);

require_once __DIR__ . '/view.php';

function ticket_session_user_id(): ?int
{
    $userId = $_SESSION['mcr_user_id'] ?? null;
    return is_int($userId) && $userId > 0 ? $userId : null;
}

function ticket_types(): array
{
    $query = database()->query(
        'SELECT tt.id, tt.name, tt.price, tt.quota, tt.concert_id,
                c.concert_date, c.status, v.name AS venue_name, v.city, v.country
         FROM ticket_types tt
         JOIN concerts c ON c.id = tt.concert_id
         JOIN venues v ON v.id = c.venue_id
         ORDER BY c.concert_date IS NULL, c.concert_date, tt.id
         LIMIT 100',
    );
    return $query->fetchAll();
}

function create_ticket_order(int $userId, int $ticketTypeId, int $quantity): int
{
    $db = database();
    $db->beginTransaction();
    try {
        $stmt = $db->prepare(
            'SELECT tt.id, tt.price, tt.quota, c.status
             FROM ticket_types tt JOIN concerts c ON c.id = tt.concert_id
             WHERE tt.id = :id FOR UPDATE',
        );
        $stmt->execute(['id' => $ticketTypeId]);
        $ticket = $stmt->fetch();
        if (!$ticket || $ticket['status'] !== 'scheduled' || (int) $ticket['quota'] < $quantity) {
            throw new DomainException('The selected ticket is unavailable.');
        }
        if (!preg_match('/\A([0-9]{1,10})(?:\.([0-9]{1,2}))?\z/', (string) $ticket['price'], $parts)) {
            throw new RuntimeException('Ticket price is invalid.');
        }
        $unitCents = (int) $parts[1] * 100 + (int) str_pad($parts[2] ?? '', 2, '0');
        $totalCents = $unitCents * $quantity;
        $db->prepare(
            'INSERT INTO ticket_orders (user_id, total_amount, status) VALUES (:user_id, :total, "pending")',
        )->execute(['user_id' => $userId, 'total' => number_format($totalCents / 100, 2, '.', '')]);
        $orderId = (int) $db->lastInsertId();
        $db->prepare(
            'INSERT INTO ticket_order_items
             (order_id, ticket_type_id, quantity, unit_price, subtotal, order_code)
             VALUES (:order_id, :ticket_type_id, :quantity, :unit_price, :subtotal, :order_code)',
        )->execute([
            'order_id' => $orderId,
            'ticket_type_id' => $ticketTypeId,
            'quantity' => $quantity,
            'unit_price' => $ticket['price'],
            'subtotal' => number_format($totalCents / 100, 2, '.', ''),
            'order_code' => random_int(10000000, 99999999),
        ]);
        $db->prepare('UPDATE ticket_types SET quota = quota - :quantity WHERE id = :id')
            ->execute(['quantity' => $quantity, 'id' => $ticketTypeId]);
        $db->commit();
        return $orderId;
    } catch (Throwable $error) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $error;
    }
}
