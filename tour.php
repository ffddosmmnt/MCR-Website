<?php
declare(strict_types=1);
require_once __DIR__ . '/app/view.php';
$concertInput = $_GET['concert'] ?? null;
$concertId = $concertInput === null ? null : filter_var($concertInput, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 4294967295]]);
if ($concertId === false || is_array($concertInput)) {
    http_response_code(400);
    $data = null;
} else {
    $data = load_content(fn () => ['concerts' => tour_schedule(), 'selected' => $concertId === null ? null : (tour_schedule(1, $concertId)[0] ?? null)]);
    if ($data !== null && $concertId !== null && $data['selected'] === null) http_response_code(404);
}
page_header('Tour', 'tour', 'tour.css');
?>
<main class="page">
    <div class="container"><h1 class="page-title">Tour</h1></div>
    <section class="container tour-intro section-tight" aria-label="Tour introduction"><div class="tour-intro-copy"><span class="eyebrow">The live schedule</span><h2>Dates, places, and live moments.</h2><p class="lead">Browse the published concert schedule. All concert times are displayed in WIB (Asia/Jakarta, UTC+7).</p></div><figure class="image-frame tour-banner"><img src="assets/tour-arena.png" alt="Empty concert arena"></figure></section>
    <section class="container section" aria-labelledby="dates-title">
        <div class="section-heading-row"><h2 class="section-title" id="dates-title">Tour Schedule</h2><span class="meta">All published dates / WIB</span></div>
        <?php if ($data === null): ?>
        <?php content_notice(http_response_code() === 400 ? 'The concert ID must be a positive integer.' : 'Tour dates are temporarily unavailable. Please try again later.', true); ?>
        <?php else: ?>
        <div class="data-table" role="table" aria-label="Published tour dates">
            <div class="table-row header" role="row"><div class="table-cell date" role="columnheader">Date / WIB</div><div class="table-cell" role="columnheader">City</div><div class="table-cell" role="columnheader">Venue / Status</div><div class="table-cell action" role="columnheader">Details</div></div>
            <?php foreach ($data['concerts'] as $concert): ?>
            <article class="table-row" role="row"><div class="table-cell date" role="cell"><?= e(date_label($concert['concert_date'], true)) ?></div><div class="table-cell" role="cell"><?= e($concert['city']) ?>, <?= e($concert['country']) ?></div><div class="table-cell" role="cell"><?= e($concert['venue_name']) ?><br><span class="meta"><?= e($concert['status']) ?></span></div><div class="table-cell action" role="cell"><a class="button secondary small" href="tour.php?concert=<?= (int) $concert['id'] ?>#selected-show">Show Details</a></div></article>
            <?php endforeach; ?>
        </div>
        <?php if (!$data['concerts']) content_notice('No tour dates have been published yet.'); ?>
        <p class="content-note">Up to 100 dates are shown in chronological order. Undated events follow. Status labels come from the database; they do not confirm ticket availability.</p>
        <?php endif; ?>
    </section>
    <?php if ($data !== null && $concertId !== null): ?>
    <section class="container section" id="selected-show" aria-labelledby="selected-title">
        <div class="section-heading-row"><h2 class="section-title" id="selected-title">Selected Show</h2></div>
        <?php if ($data['selected'] === null): content_notice('The requested concert was not found.'); else: $selected = $data['selected']; ?>
        <h3><?= e($selected['venue_name']) ?></h3><p><?= e($selected['city']) ?>, <?= e($selected['country']) ?></p><p><?= e(date_label($selected['concert_date'], true)) ?> · <?= e($selected['status']) ?></p>
        <p>Ticket booking is not connected yet. This selection identifies concert #<?= (int) $selected['id'] ?> for the next integration stage.</p>
        <?php endif; ?>
    </section>
    <?php endif; ?>
        <section class="container section" aria-labelledby="vip-title">
            <div class="section-heading-row">
                <h2 class="section-title" id="vip-title">VIP Package Concepts</h2>
            </div>

            <p class="lead">Package concepts from the original design. These packages are not available to book.</p>
            <div class="card-grid">
                <article class="package-card">
                    <span class="package-label">Level 01</span>
                    <h3>Early Entry</h3>
                    <p>Enter before general admission, shop the merch stand first, and receive a printed commemorative
                        laminate.</p>
                    <span class="button disabled full">Concept Preview</span>
                </article>

                <article class="package-card featured">
                    <span class="package-label">Premium</span>
                    <h3>Side Stage Experience</h3>
                    <p>Watch selected songs from a reserved production-view area with a limited poster and priority
                        check-in.</p>
                    <span class="button disabled full">Concept Preview</span>
                </article>

                <article class="package-card">
                    <span class="package-label">Level 03</span>
                    <h3>Ultimate News</h3>
                    <p>Includes early entry, exclusive tour print, collectible ticket, and access to a curated digital
                        show News.</p>
                    <span class="button disabled full">Concept Preview</span>
                </article>
            </div>
        </section>
        <section class="container section" aria-labelledby="past-title">
            <div class="section-heading-row">
                <h2 class="section-title" id="past-title">Visual Archive</h2>
            </div>

            <div class="gallery-grid">
                <article class="gallery-card">
                    <figure class="image-frame gallery-image">
                        <img src="assets/hero-concert.png" alt="Concert crowd and stage lights in black and white">
                    </figure>
                    <h3 class="card-title">Stage Atmosphere</h3>
                    <a href="assets/hero-concert.png">View Artwork</a>
                </article>

                <article class="gallery-card">
                    <figure class="image-frame gallery-image">
                        <img src="assets/archive-news.png" alt="Film contact sheets from a rock show News">
                    </figure>
                    <h3 class="card-title">The News Archive</h3>
                    <a href="assets/archive-news.png">View Artwork</a>
                </article>

                <article class="gallery-card">
                    <figure class="image-frame gallery-image">
                        <img src="assets/tour-arena.png" alt="Arena stage viewed from the floor before doors open">
                    </figure>
                    <h3 class="card-title">Arena Architecture</h3>
                    <a href="assets/tour-arena.png">View Artwork</a>
                </article>

                <article class="gallery-card">
                    <figure class="image-frame gallery-image">
                        <img src="assets/Blackparade.png" alt="Monochrome theatrical album art detail">
                    </figure>
                    <h3 class="card-title">The Parade</h3>
                    <a href="assets/Blackparade.png">View Artwork</a>
                </article>
            </div>
        </section>
    <section class="container section split-section" aria-label="Venue information and tour archive"><article class="venue-panel"><div class="section-heading-row"><h2 class="section-title">Before You Go</h2></div><p>Check the venue's official information for entry, accessibility, parking, and bag policies. No reservation or ticket payment is processed on this page.</p></article><figure class="image-frame tour-banner"><img src="assets/hero-concert.png" alt="Black and white concert archive artwork" loading="lazy"></figure></section>
</main>
<?php page_footer(); ?>
