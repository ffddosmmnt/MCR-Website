<?php
declare(strict_types=1);
require_once __DIR__ . '/app/view.php';
$search = $_GET['q'] ?? '';
$invalidSearch = !is_string($search) || !mb_check_encoding($search, 'UTF-8') || mb_strlen($search, 'UTF-8') > 200;
if ($invalidSearch) {
    http_response_code(400);
    $data = null;
    $search = '';
} else {
    $search = trim($search);
    $data = load_content(fn () => ['albums' => albums(), 'songs' => songs()]);
}
$searchRows = [];
$visibleSongCount = 0;
foreach ($data['songs'] ?? [] as $song) {
    $song['search_text'] = $song['title'] . ' ' . $song['album_title'] . ' ' . ($song['lyrics'] ?? '');
    $song['matches'] = $search === '' || str_contains(mb_strtolower($song['search_text'], 'UTF-8'), mb_strtolower($search, 'UTF-8'));
    if ($song['matches']) $visibleSongCount++;
    $searchRows[] = $song;
}
page_header('Music', 'music', 'music.css');
?>
<main class="page">
    <div class="container"><h1 class="page-title">Music</h1></div>
    <?php if ($data === null): ?>
    <section class="container section"><?php content_notice($invalidSearch ? 'Search must be text of at most 200 characters.' : 'The music catalog is temporarily unavailable. Please try again later.', true); ?></section>
    <?php else: ?>
    <?php if ($data['albums']): $featured = $data['albums'][0]; ?>
    <section class="container section featured-release" id="store" aria-labelledby="release-title">
        <figure class="image-frame featured-cover"><img src="<?= e(album_cover($featured['cover_image'])) ?>" alt="Artwork for <?= e($featured['title']) ?>"></figure>
        <div class="featured-info"><span class="eyebrow">Latest catalog release</span><h2 id="release-title"><?= e($featured['title']) ?></h2><p class="release-date">Released: <?= e(date_label($featured['release_date'])) ?></p><a class="button" href="#lyrics">Explore Songs</a></div>
    </section>
    <?php endif; ?>
    <section class="container section" id="album-grid" aria-labelledby="album-title">
        <div class="section-heading-row"><h2 class="section-title" id="album-title">Albums</h2></div>
        <?php if (!$data['albums']) content_notice('No albums have been published yet.'); ?>
        <div class="album-grid">
            <?php foreach ($data['albums'] as $album): ?>
            <article class="album-card" id="album-<?= (int) $album['id'] ?>">
                <figure class="image-frame album-image"><img src="<?= e(album_cover($album['cover_image'])) ?>" alt="Artwork for <?= e($album['title']) ?>" loading="lazy"></figure>
                <div class="album-card-body"><h3><?= e($album['title']) ?></h3><p class="album-year"><?= e(date_label($album['release_date'])) ?></p><a class="button secondary small full" href="#lyrics">Browse Lyrics</a></div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
    <section class="container section" id="lyrics" aria-labelledby="lyrics-title">
        <div class="section-heading-row"><h2 class="section-title" id="lyrics-title">Searchable Lyrics</h2></div>
        <form class="search-form" action="music.php#lyrics" method="get">
            <label class="field-label" for="lyrics-search">Search song titles, albums, or lyrics in the loaded catalog</label>
            <input id="lyrics-search" type="search" name="q" placeholder="Enter keywords..." maxlength="200" value="<?= e($search) ?>">
            <button type="submit">Search</button>
        </form>
        <p class="lyrics-status" data-lyrics-status role="status" aria-live="polite"><?= $visibleSongCount ?> song<?= $visibleSongCount === 1 ? '' : 's' ?> found.</p>
        <div class="lyrics-table" role="table" aria-label="Searchable lyrics list">
            <div class="lyrics-row header" role="row"><div class="lyrics-cell title" role="columnheader">Song Title</div><div class="lyrics-cell" role="columnheader">Album</div><div class="lyrics-cell action" role="columnheader">Lyrics</div></div>
            <?php foreach ($searchRows as $song): ?>
            <article class="lyrics-row" role="row" data-search-text="<?= e($song['search_text']) ?>"<?= $song['matches'] ? '' : ' hidden' ?>>
                <div class="lyrics-cell title" role="cell"><?= e($song['title']) ?></div>
                <div class="lyrics-cell" role="cell"><?= e($song['album_title']) ?></div>
                <div class="lyrics-cell action" role="cell"><a href="#song-<?= (int) $song['id'] ?>">Read Lyrics</a></div>
            </article>
            <?php endforeach; ?>
        </div>
        <p class="lyrics-empty" data-lyrics-empty<?= $visibleSongCount > 0 ? ' hidden' : '' ?>>No songs match your search.</p>
        <?php if (!$data['songs']) content_notice('No songs have been published yet.'); ?>
    </section>
    <section class="container section" id="playlist" aria-labelledby="playlist-title">
        <div class="section-heading-row"><h2 class="section-title" id="playlist-title">Shared Playlist</h2></div>
        <p class="lead">The same catalog for every visitor, grouped by album and song title. This is a song list with lyrics; individual audio playback is not connected.</p>
        <?php if (!$data['songs']) content_notice('The shared playlist is empty.'); ?>
        <div class="song-library">
            <?php foreach ($data['songs'] as $song): ?>
            <details class="song-details" id="song-<?= (int) $song['id'] ?>">
                <summary><?= e($song['title']) ?> <span class="meta">/ <?= e($song['album_title']) ?></span></summary>
                <p class="song-lyrics"><?= e($song['lyrics'] ?: 'Lyrics are not available for this song yet.') ?></p>
            </details>
            <?php endforeach; ?>
        </div>
    </section>
    <p class="container content-note">Up to 100 albums and 100 songs are shown from the catalog.</p>
    <?php endif; ?>
        <section class="container section" aria-labelledby="behind-title">
            <div class="section-heading-row">
                <h2 class="section-title" id="behind-title">Behind the Music</h2>
            </div>

            <div class="card-grid">
                <article class="story-card">
                    <span class="package-label">Recording Story</span>
                    <h3>The Paramour Sessions</h3>
                    <p>The band shaped a haunted, live-room sound by recording in a mansion environment that pushed the
                        album into dramatic territory.</p>
                    <a class="button small" href="news.php">Read Story</a>
                </article>

                <article class="story-card">
                    <span class="package-label">Visual System</span>
                    <h3>Conceptual Framing</h3>
                    <p>Costume, contrast, typography, and theatrical stage direction create a visual identity that still
                        reads instantly.</p>
                    <a class="button small" href="news.php">Explore Influences</a>
                </article>
            </div>
        </section>
    <section class="container section" id="official-video" aria-labelledby="video-title">
        <div class="section-heading-row"><h2 class="section-title" id="video-title">Featured Video</h2></div>
        <div class="music-embed-panel">
            <div class="music-embed-copy"><span class="eyebrow">YouTube feature</span><h3>Welcome to the Black Parade</h3><p>A featured video, separate from the database song list.</p><a class="button secondary small" href="https://www.youtube.com/watch?v=G-I9csAflBs" target="_blank" rel="noopener noreferrer">Open on YouTube</a></div>
            <div class="music-embed-frame">
                <iframe data-youtube-player data-src="https://www.youtube.com/embed/G-I9csAflBs?rel=0" title="My Chemical Romance - Welcome to the Black Parade" width="560" height="315" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                <div class="music-embed-local-notice" data-youtube-local-notice hidden><strong>Run this project through the PHP server</strong><p>Use the documented PHP router to load the database catalog and video.</p><a href="https://www.youtube.com/watch?v=G-I9csAflBs" target="_blank" rel="noopener noreferrer">Play on YouTube</a></div>
            </div>
        </div>
    </section>
</main>
<?php page_footer(); ?>
