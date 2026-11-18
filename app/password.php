<?php
declare(strict_types=1);

/** Character minimum; byte maximum prevents bcrypt truncation. */
function valid_account_password(mixed $password): bool
{
    return is_string($password)
        && !str_contains($password, "\0")
        && mb_check_encoding($password, 'UTF-8')
        && mb_strlen($password, 'UTF-8') >= 8
        && strlen($password) <= 72;
}
