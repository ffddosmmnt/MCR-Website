<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';

function content_rows(string $sql, int $limit): array
{
    if ($limit < 1 || $limit > 100) {
        throw new InvalidArgumentException('Content limit must be between 1 and 100.');
    }
    $query = database()->prepare($sql);
    $query->bindValue(':limit', $limit, PDO::PARAM_INT);
    $query->execute();
    return $query->fetchAll();
}

function albums(int $limit = 100): array
{
    return content_rows(
        'SELECT id, title, release_date, cover_image FROM albums ORDER BY release_date DESC, id DESC LIMIT :limit',
        $limit,
    );
}

function songs(): array
{
    return content_rows(
        'SELECT s.id, s.title, s.lyrics, a.title AS album_title, a.id AS album_id, a.cover_image AS album_cover
         FROM songs s JOIN albums a ON a.id = s.album_id ORDER BY a.title, s.title, s.id LIMIT :limit',
        100,
    );
}

/** Prices stay DECIMAL strings; variant IDs identify bag items, not product names. */
function merch_catalog(int $limit = 100): array
{
    $categories = content_rows('SELECT id, name FROM categories ORDER BY name, id LIMIT :limit', 100);
    $products = content_rows(
        'SELECT p.id, p.category_id, c.name AS category_name, p.name, p.price, p.details, p.image_path
         FROM products p JOIN categories c ON c.id = p.category_id ORDER BY p.id LIMIT :limit', $limit,
    );
    $byProduct = [];
    if ($products !== []) {
        $ids = array_column($products, 'id');
        $marks = implode(',', array_fill(0, count($ids), '?'));
        $query = database()->prepare("SELECT id, product_id, size, color, stock FROM product_variants WHERE product_id IN ($marks) ORDER BY id");
        $query->execute($ids);
        foreach ($query->fetchAll() as $variant) {
            $variant['id'] = (int) $variant['id'];
            $variant['product_id'] = (int) $variant['product_id'];
            $variant['stock'] = (int) $variant['stock'];
            $byProduct[$variant['product_id']][] = $variant;
        }
    }
    $categoryMap = [];
    foreach ($categories as $category) {
        $category['id'] = (int) $category['id'];
        $categoryMap[$category['id']] = $category;
    }
    foreach ($products as &$product) {
        $product['id'] = (int) $product['id'];
        $product['category_id'] = (int) $product['category_id'];
        $product['variants'] = $byProduct[$product['id']] ?? [];
        $categoryMap[$product['category_id']] = ['id' => $product['category_id'], 'name' => $product['category_name']];
    }
    unset($product);
    return ['categories' => array_values($categoryMap), 'products' => $products];
}

function fan_messages(): array
{
    return content_rows('SELECT id, display_name, message FROM fan_messages ORDER BY id DESC LIMIT :limit', 100);
}

function news_articles(int $limit = 100): array
{
    return content_rows(
        'SELECT id, title, slug, content, image_path, news_date FROM news_articles ORDER BY news_date DESC, id DESC LIMIT :limit',
        $limit,
    );
}

function article_by_slug(string $slug): ?array
{
    $query = database()->prepare('SELECT id, title, slug, content, image_path, news_date FROM news_articles WHERE slug = :slug');
    $query->execute(['slug' => $slug]);
    return $query->fetch() ?: null;
}

function tour_schedule(int $limit = 100, ?int $concertId = null): array
{
    if ($limit < 1 || $limit > 100) {
        throw new InvalidArgumentException('Content limit must be between 1 and 100.');
    }
    $sql = 'SELECT c.id, c.concert_date, c.status, v.name AS venue_name, v.city, v.country
            FROM concerts c JOIN venues v ON v.id = c.venue_id';
    if ($concertId !== null) {
        $sql .= ' WHERE c.id = :concert_id';
    }
    $sql .= ' ORDER BY c.concert_date IS NULL, c.concert_date, c.id LIMIT :limit';
    $query = database()->prepare($sql);
    if ($concertId !== null) {
        $query->bindValue(':concert_id', $concertId, PDO::PARAM_INT);
    }
    $query->bindValue(':limit', $limit, PDO::PARAM_INT);
    $query->execute();
    return $query->fetchAll();
}
