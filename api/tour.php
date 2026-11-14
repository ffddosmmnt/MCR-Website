<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/public-api.php';
public_api(function () {
    $id = null;
    if (isset($_GET['concert'])) {
        $id = filter_var($_GET['concert'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 4294967295]]);
        if ($id === false || is_array($_GET['concert'])) throw new InvalidArgumentException('Invalid concert ID.');
    }
    $selected = $id === null ? null : (tour_schedule(1, $id)[0] ?? null);
    if ($id !== null && $selected === null) throw new DomainException('Concert not found.');
    return ['concerts' => array_map('public_concert', tour_schedule()), 'selected' => $selected === null ? null : public_concert($selected)];
});
