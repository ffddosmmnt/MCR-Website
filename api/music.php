<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/public-api.php';
public_api(function () {
    $query = query_text('q', 200);
    return ['albums' => array_map('public_album', albums()), 'songs' => songs(), 'query' => $query];
});
