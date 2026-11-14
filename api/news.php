<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/public-api.php';
public_api(fn () => ['articles' => array_map('public_article', news_articles())]);
