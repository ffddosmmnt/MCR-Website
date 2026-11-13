<?php
declare(strict_types=1);
require_once __DIR__ . '/app/view.php';
$data = load_content(fn () => ['concerts' => tour_schedule(3), 'news' => news_articles(3)]);
page_header('Home', 'home', 'home.css');
?>
<main>
    <section class="container hero" aria-labelledby="home-title">
        <div class="hero-copy">
            <span class="eyebrow">Music / tour / news</span>
            <h1 id="home-title">My Chemical Romance</h1>
            <p>Explore the music, browse tour dates, and read the latest updates from our band website project.</p>
            <div class="button-row"><a class="button" href="music.php">Explore Music</a><a class="button secondary" href="tour.php">View Tour</a></div>
        </div>
        <figure class="image-frame hero-frame"><img src="assets/hero-concert.png" alt="Black and white concert stage with lights and crowd"><figcaption>Live document series</figcaption></figure>
    </section>
    <?php if ($data === null): ?>
    <section class="container section"><?php content_notice('Tour dates and news are temporarily unavailable. Please try again later.', true); ?></section>
    <?php else: ?>
    <section class="container section" aria-labelledby="shows-title">
        <div class="section-heading-row"><h2 class="section-title" id="shows-title">Tour Dates</h2><a class="small-link" href="tour.php">All Dates</a></div>
        <div class="show-list">
            <?php if (!$data['concerts']) content_notice('No tour dates have been published yet.'); ?>
            <?php foreach ($data['concerts'] as $concert): ?>
            <article class="show-row">
                <div class="show-date"><?= e(date_label($concert['concert_date'], true)) ?></div>
                <h3 class="show-city"><?= e($concert['city']) ?></h3>
                <div class="show-venue"><?= e($concert['venue_name']) ?> · <?= e($concert['status']) ?></div>
                <div class="show-action"><a class="button secondary small" href="tour.php?concert=<?= (int) $concert['id'] ?>#selected-show">Show Details</a></div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
    <section class="container section home-news" aria-labelledby="home-news-title">
        <div class="section-heading-row"><h2 class="section-title" id="home-news-title">Latest News</h2><a class="small-link" href="news.php">Read News</a></div>
        <div class="card-grid">
            <?php if (!$data['news']) content_notice('No news has been published yet.'); ?>
            <?php foreach ($data['news'] as $article): ?>
            <article class="news-card">
                <figure class="image-frame card-image"><img src="assets/archive-news.png" alt="Editorial archive artwork"></figure>
                <div class="news-card-body">
                    <p class="meta"><?= e(date_label($article['news_date'] === null ? null : substr($article['news_date'], 0, 10))) ?></p>
                    <h3 class="card-title"><a href="<?= e(article_url($article)) ?>"><?= e($article['title']) ?></a></h3>
                    <p class="card-copy"><?= e(excerpt($article['content'])) ?></p>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
    <section class="container stream" aria-labelledby="stream-title">
        <figure class="image-frame stream-media"><img src="assets/Blackparade.png" alt="Monochrome marching band inspired artwork"></figure>
        <div class="stream-copy"><span class="eyebrow">The catalog</span><h2 class="section-title" id="stream-title">Explore the Music</h2><p class="lead">Browse albums, explore the shared song list, and read lyrics from the catalog.</p><a class="button" href="music.php#playlist">View Shared Playlist</a></div>
    </section>
</main>
<?php page_footer(); ?>
