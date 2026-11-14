<?php

declare(strict_types=1);

require_once __DIR__ . '/view.php';

function store_catalog(): array
{
    $query = database()->query(
        'SELECT p.id, p.name, p.price, p.details, c.name AS category_name,
                pv.id AS variant_id, pv.size, pv.color, pv.stock
         FROM products p
         JOIN categories c ON c.id = p.category_id
         LEFT JOIN product_variants pv ON pv.product_id = p.id
         ORDER BY p.id, pv.id
         LIMIT 100',
    );
    $products = [];
    foreach ($query as $row) {
        $id = (int) $row['id'];
        if (!isset($products[$id])) {
            $products[$id] = [
                'id' => $id,
                'name' => (string) $row['name'],
                'price' => (string) $row['price'],
                'details' => $row['details'] === null ? '' : (string) $row['details'],
                'category_name' => (string) $row['category_name'],
                'variants' => [],
            ];
        }
        if ($row['variant_id'] !== null) {
            $products[$id]['variants'][] = [
                'id' => (int) $row['variant_id'],
                'size' => $row['size'] === null ? '' : (string) $row['size'],
                'color' => $row['color'] === null ? '' : (string) $row['color'],
                'stock' => (int) $row['stock'],
            ];
        }
    }
    return array_values($products);
}
