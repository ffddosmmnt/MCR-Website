<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/public-api.php';
public_api(function (): array {
    $catalog = merch_catalog(4);
    foreach ($catalog['products'] as &$product) {
        $product['image_path'] = local_image($product['image_path']);
    }
    unset($product);
    return $catalog;
});
