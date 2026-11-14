<?php

declare(strict_types=1);

require_once __DIR__ . '/app/store.php';

$error = null;
$products = [];
try {
    $products = store_catalog();
} catch (Throwable $exception) {
    $error = 'The store is temporarily unavailable.';
    error_log('[mcr] Store read failed (' . get_class($exception) . ').');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MCR | Store</title>
    <link rel="stylesheet" href="base.css">
    <link rel="stylesheet" href="showcase.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a class="logo" href="index.html">MCR</a>
        <nav class="main-nav" aria-label="Main navigation">
            <a href="index.html">Home</a><a href="about.html">About</a><a href="music.html">Music</a>
            <a href="tour.html">Tour</a><a class="active" href="showcase.php" aria-current="page">Store</a><a href="news.html">News</a>
        </nav>
        <div class="header-icons" aria-label="Utility navigation"><a href="#products">Shop</a><a href="#bag">Bag</a></div>
    </div>
</header>
<main class="page store-page">
    <section class="container store-hero" aria-labelledby="store-title">
        <div class="store-hero-copy">
            <span class="eyebrow">Database catalog / live stock</span>
            <h1 class="page-title" id="store-title">Store</h1>
            <p class="lead">Browse merchandise and available variants from the catalog.</p>
            <div class="button-row"><a class="button" href="#products">Shop Collection</a><a class="button secondary" href="#bag">View Bag</a></div>
        </div>
        <figure class="image-frame store-hero-media"><img src="assets/Blackparade.png" alt="Monochrome merchandise artwork"><figcaption>Catalog showcase</figcaption></figure>
    </section>
    <?php if ($error !== null): ?>
        <div class="container"><p class="content-notice" role="alert"><?= e($error) ?></p></div>
    <?php endif; ?>
    <section class="container section" id="products" aria-labelledby="products-title">
        <div class="section-heading-row">
            <h2 class="section-title" id="products-title">Merch Showcase</h2>
            <form class="store-filter" aria-label="Filter merchandise">
                <label for="category-filter">Filter</label>
                <select id="category-filter" data-store-filter>
                    <option value="all">All Items</option>
                    <?php
                    $categories = array_values(array_unique(array_map(static fn (array $product): string => $product['category_name'], $products)));
                    foreach ($categories as $category):
                        ?>
                        <option value="<?= e(strtolower($category)) ?>"><?= e($category) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <div class="product-grid" data-product-grid>
            <?php foreach ($products as $product): ?>
                <?php
                $category = strtolower($product['category_name']);
                $available = array_sum(array_map(static fn (array $variant): int => $variant['stock'], $product['variants']));
                ?>
                <article class="product-card" data-category="<?= e($category) ?>" data-name="<?= e($product['name']) ?>" data-price="<?= e($product['price']) ?>">
                    <figure class="image-frame product-image"><img src="assets/Blackparade.png" alt="Merchandise artwork for <?= e($product['name']) ?>"></figure>
                    <div class="product-body">
                        <p class="meta"><?= e($product['category_name']) ?> / <?= $available > 0 ? 'In stock' : 'Sold out' ?></p>
                        <h3><?= e($product['name']) ?></h3>
                        <p><?= e($product['details']) ?></p>
                        <div class="product-actions">
                            <strong>$<?= e($product['price']) ?></strong>
                            <button type="button" class="button small" data-add-to-bag <?= $available < 1 ? 'disabled' : '' ?>>Add to Bag</button>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <p class="store-empty" data-store-empty <?= $products === [] ? '' : 'hidden' ?>>No merchandise found in this category.</p>
    </section>
    <section class="container section store-split" id="bag" aria-label="Merchandise bag">
        <article class="lookbook-panel"><span class="eyebrow">Catalog note</span><h2 class="section-title">Wear It Loud</h2><p class="lead">The bag is a browser preview. Orders require an authenticated checkout flow.</p></article>
        <aside class="bag-panel" aria-labelledby="bag-title">
            <span class="eyebrow">Fan Bag</span><h2 id="bag-title">Selected Merch</h2>
            <div class="bag-items" data-bag-items><p>Your bag is empty. Add a product from the showcase.</p></div>
            <div class="bag-total"><span>Total</span><strong data-bag-total>$0</strong></div>
            <a class="button full" href="ticket-transaction.php">Continue to Tickets</a>
        </aside>
    </section>
</main>
<footer class="site-footer"><div class="container footer-inner"><div><strong>My Chemical Romance</strong><span>Database-backed catalog.</span></div><nav class="footer-links" aria-label="Footer navigation"><a href="index.html">Home</a><a href="tour.html">Tour</a><a href="showcase.php">Store</a><a href="news.html">News</a></nav></div></footer>
<script src="script.js"></script>
</body>
</html>
