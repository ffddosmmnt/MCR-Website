<?php
declare(strict_types=1);
$query = $_SERVER['QUERY_STRING'] ?? '';
header('Location: news.html' . ($query === '' ? '' : '?' . $query), true, 308);
