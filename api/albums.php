<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/public-api.php';
public_api(function () {
    $items = albums();
    foreach ($items as &$item) $item['id'] = (int) $item['id'];
    unset($item);
    return $items;
});
