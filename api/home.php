<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/public-api.php';
public_api(fn () => [
    'news' => array_map('public_article', news_articles(3)),
    'concerts' => array_map('public_concert', tour_schedule(3)),
]);
