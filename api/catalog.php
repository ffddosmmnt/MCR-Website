<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/public-api.php';
public_api(fn () => merch_catalog());
