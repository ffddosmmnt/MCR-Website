<?php
declare(strict_types=1);

/** Exact DECIMAL(12,2) arithmetic, in hundredths of one rupiah. */
function money_cents(string $value): int
{
    if (!preg_match('/\A([0-9]{1,10})(?:\.([0-9]{1,2}))?\z/', $value, $parts)) {
        throw new InvalidArgumentException('Amount exceeds DECIMAL(12,2).');
    }
    return (int) $parts[1] * 100 + (int) str_pad($parts[2] ?? '', 2, '0');
}

function money_string(int $cents): string
{
    return intdiv($cents, 100) . '.' . str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
}
