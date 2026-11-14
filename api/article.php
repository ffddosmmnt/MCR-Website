<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/public-api.php';
public_api(function () {
    $slug = query_text('slug', 220);
    if ($slug === '' || ($article = article_by_slug($slug)) === null) {
        throw new DomainException('Article not found.');
    }
    $related = array_slice(array_values(array_filter(news_articles(4), fn ($item) => (int) $item['id'] !== (int) $article['id'])), 0, 3);
    return ['article' => public_article($article), 'related' => array_map('public_article', $related)];
});
