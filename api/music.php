<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/public-api.php';
public_api(function () {
    $query = query_text('q', 200);
    $songs = songs();
    foreach ($songs as &$song) {
        $song['album_cover'] = album_cover($song['album_cover']);
    }
    unset($song);
    return ['albums' => array_map('public_album', albums()), 'songs' => $songs, 'query' => $query];
});
