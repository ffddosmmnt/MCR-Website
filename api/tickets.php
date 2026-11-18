<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/public-api.php';
public_api(function (): array {
    $id = filter_var($_GET['concert'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 4294967295]]);
    if ($id === false || $id === null) throw new InvalidArgumentException('Invalid concert.');
    $concert = tour_schedule(1, $id)[0] ?? null;
    if ($concert === null) throw new DomainException('Concert not found.');
    $tickets = concert_ticket_types($id);
    return ['concert' => public_concert($concert), 'tickets' => $tickets, 'currency' => 'IDR'];
});
