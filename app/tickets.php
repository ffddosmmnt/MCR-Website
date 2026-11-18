<?php
declare(strict_types=1);
require_once __DIR__ . '/database.php';

/** Event eligibility only; quota and account limits are rechecked under checkout locks. */
function concert_bookable(array $concert): bool
{
    return $concert['status'] === 'scheduled'
        && $concert['concert_date'] !== null
        && strtotime($concert['concert_date'] . ' UTC') > time();
}

function concert_ticket_types(int $concertId): array
{
    $query = database()->prepare('SELECT id, name, price, quota FROM ticket_types WHERE concert_id = ? ORDER BY price, id LIMIT 100');
    $query->execute([$concertId]);
    $tickets = $query->fetchAll();
    foreach ($tickets as &$ticket) {
        $ticket['id'] = (int) $ticket['id'];
        $ticket['quota'] = (int) $ticket['quota'];
    }
    unset($ticket);
    return $tickets;
}
