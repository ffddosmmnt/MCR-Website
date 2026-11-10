<?php
declare(strict_types=1);
require_once __DIR__ . '/app/view.php';
$data = load_content(fn () => ['articles' => news_articles()]);
page_header('News', 'news', 'news.css');
?>
<main class="page">
    <div class="container"><h1 class="page-title">News</h1></div>
    <?php if ($data === null): ?>
    <section class="container section"><?php content_notice('News is temporarily unavailable. Please try again later.', true); ?></section>
    <?php elseif (!$data['articles']): ?>
    <section class="container section"><?php content_notice('No news has been published yet.'); ?></section>
    <?php else: $featured = $data['articles'][0]; ?>
    <section class="container section news-feature" aria-labelledby="featured-news-title">
        <figure class="image-frame news-feature-media"><img src="assets/archive-news.png" alt="Editorial archive artwork"></figure>
        <article class="news-feature-copy">
            <span class="meta"><?= e(date_label($featured['news_date'] === null ? null : substr($featured['news_date'], 0, 10))) ?></span>
            <h2 id="featured-news-title"><?= e($featured['title']) ?></h2><p class="lead"><?= e(excerpt($featured['content'])) ?></p><a class="button" href="<?= e(article_url($featured)) ?>">Read More</a>
        </article>
    </section>
    <section class="container section" id="latest-updates" aria-labelledby="latest-title">
        <div class="section-heading-row"><h2 class="section-title" id="latest-title">Latest Updates</h2></div>
        <div class="latest-grid">
            <?php foreach (array_slice($data['articles'], 1) as $article): ?>
            <article class="latest-card"><figure class="image-frame latest-image"><img src="assets/archive-news.png" alt="Editorial archive artwork" loading="lazy"></figure><div class="latest-card-body"><p class="meta"><?= e(date_label($article['news_date'] === null ? null : substr($article['news_date'], 0, 10))) ?></p><h3 class="card-title"><a href="<?= e(article_url($article)) ?>"><?= e($article['title']) ?></a></h3><p class="card-copy"><?= e(excerpt($article['content'])) ?></p></div></article>
            <?php endforeach; ?>
        </div>
        <p class="content-note">Up to 100 articles are shown, newest dated articles first. Undated articles follow.</p>
    </section>
    <?php endif; ?>
    <section class="container section announcement" aria-labelledby="announcement-title"><div><span class="eyebrow">On the road</span><h2 id="announcement-title">Explore Tour Dates</h2><p class="lead">Find venue details and the published concert schedule.</p></div><a class="button" href="tour.php">View Tour</a></section>
        <section class="container section fan-story" aria-labelledby="fan-stories-title">
            <article>
                <span class="eyebrow">Fan Stories</span>
                <h2 class="section-title" id="fan-stories-title">The News Lives Through the Audience</h2>
                <p class="lead">Fans continue to document memories, ticket stubs, recordings, and essays that trace the
                    emotional history of the band.</p>
                <a class="button secondary" href="#newsletter">Newsletter Information</a>
            </article>

            <figure class="image-frame fan-story-media">
                <img src="assets/hero-concert.png" alt="Concert audience and stage lights in black and white">
            </figure>
        </section>
    <section class="container newsletter" id="newsletter" aria-labelledby="newsletter-title">
        <div><h2 id="newsletter-title">Stay Updated</h2><p class="lead">Newsletter registration will be connected in a later project stage.</p></div>
        <form class="newsletter-form" action="news.php#newsletter" method="get"><label class="field-label" for="email">Email Address</label><div class="input-row"><input id="email" type="email" name="email" placeholder="user@domain.com" disabled><button type="submit" disabled>Coming Soon</button></div></form>
    </section>
</main>
<?php page_footer(); ?>
