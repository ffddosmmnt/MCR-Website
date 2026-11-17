<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/checkout.php';
shop_api(function (string $method, array $input): array {
    $user = shop_user();
    if ($method === 'GET') {
        return ['order' => checkout_order(database(), $user['id'], shop_text($_GET, 'kind', 6), shop_id($_GET['id'] ?? null))];
    }
    return checkout_submit($user, $input);
});
