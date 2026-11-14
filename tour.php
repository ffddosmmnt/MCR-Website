<?php
declare(strict_types=1);
$query = $_SERVER['QUERY_STRING'] ?? '';
header('Location: tour.html' . ($query === '' ? '' : '?' . $query), true, 308);
