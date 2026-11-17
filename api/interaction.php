<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/interaction.php';
shop_api(function (string $method, array $input): array {
    if ($method !== 'POST') {
        header('Allow: POST');
        throw new ShopError(405, 'Use POST for this resource.');
    }
    return interaction_submit($input);
});
