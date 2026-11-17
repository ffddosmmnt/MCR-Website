<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/public-api.php';
public_api(function (): array {
    $id = filter_var($_GET['concert'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 4294967295]]);
    if ($id === false || $id === null) throw new InvalidArgumentException('Invalid concert.');
    $concert = tour_schedule(1, $id)[0] ?? null;
    if ($concert === null) throw new DomainException('Concert not found.');
    $query = database()->prepare('SELECT id, name, price, quota FROM ticket_types WHERE concert_id = ? ORDER BY price, id LIMIT 100');
    $query->execute([$id]);
    $tickets = $query->fetchAll();
    foreach ($tickets as &$ticket) { $ticket['id'] = (int) $ticket['id']; $ticket['quota'] = (int) $ticket['quota']; }
    return ['concert' => public_concert($concert), 'tickets' => $tickets, 'currency' => 'IDR'];
});
