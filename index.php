<?php
declare(strict_types=1);
$query = $_SERVER['QUERY_STRING'] ?? '';
header('Location: index.html' . ($query === '' ? '' : '?' . $query), true, 308);
