<?php
declare(strict_types=1);
require_once __DIR__ . '/app/view.php';
$slug = $_GET['slug'] ?? '';
$data = null;
$inputInvalid = !is_string($slug) || !mb_check_encoding($slug, 'UTF-8') || mb_strlen($slug, 'UTF-8') > 220;
if ($inputInvalid) {
    http_response_code(400);
} elseif ($slug === '') {
    http_response_code(404);
} else {
    $data = load_content(fn () => ['article' => article_by_slug($slug), 'related' => news_articles(4)]);
    if ($data !== null && $data['article'] === null) http_response_code(404);
}
$article = $data['article'] ?? null;
page_header($article['title'] ?? 'News Detail', 'news', 'news-detail.css');
?>
<main class="page detail-page">
    <?php if ($article === null): ?>
    <section class="container section">
        <h1 class="section-title"><?= $inputInvalid ? 'Invalid article request' : (http_response_code() === 503 ? 'News unavailable' : 'Article not found') ?></h1>
        <?php content_notice(http_response_code() === 503 ? 'News is temporarily unavailable. Please try again later.' : 'Choose a published article from the news list.', http_response_code() === 503); ?>
        <a class="button secondary" href="news.php">View All News</a>
    </section>
    <?php else: ?>
    <div class="container detail-layout">
        <article class="article-shell">
            <nav class="breadcrumb" aria-label="Breadcrumb"><a href="index.php">Home</a><a href="news.php">News</a><strong><?= e($article['title']) ?></strong></nav>
            <span class="detail-badge">News</span><h1><?= e($article['title']) ?></h1>
            <div class="article-meta-row"><span><?= e(date_label($article['news_date'] === null ? null : substr($article['news_date'], 0, 10))) ?></span></div>
            <figure class="image-frame article-hero"><img src="assets/archive-news.png" alt="Editorial archive artwork"><figcaption>Project archive artwork</figcaption></figure>
            <div class="article-body"><?= e($article['content'] ?: 'The article content is not available yet.') ?></div>
        </article>
        <aside class="detail-sidebar" aria-label="Article sidebar">
            <section class="sidebar-box related-box" aria-labelledby="related-title"><h2 id="related-title">More News</h2>
                <?php $related = array_slice(array_values(array_filter($data['related'], fn ($item) => $item['id'] !== $article['id'])), 0, 3); ?>
                <?php if (!$related) content_notice('No other articles have been published yet.'); ?>
                <?php foreach ($related as $item): ?>
                <article><p class="meta"><?= e(date_label($item['news_date'] === null ? null : substr($item['news_date'], 0, 10))) ?></p><h3><a href="<?= e(article_url($item)) ?>"><?= e($item['title']) ?></a></h3></article>
                <?php endforeach; ?>
                <a class="button secondary full" href="news.php">View All News</a>
            </section>
            <section class="sidebar-box newsletter-panel" id="detail-newsletter" aria-labelledby="join-title"><h2 id="join-title">Stay Updated</h2><p>Newsletter registration is not connected yet.</p><a class="button full" href="news.php#newsletter">Newsletter Information</a></section>
        </aside>
    </div>
    <?php endif; ?>
</main>
<?php page_footer(); ?>
