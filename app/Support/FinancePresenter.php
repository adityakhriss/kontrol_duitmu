<?php

namespace App\Support;

use Carbon\CarbonInterface;

class FinancePresenter
{
    public static function money(float|int|string $amount): string
    {
        return 'Rp'.number_format((float) $amount, 0, ',', '.');
    }

    public static function signedMoney(float|int|string $amount): string
    {
        $amount = (float) $amount;

        return ($amount >= 0 ? '+' : '-').self::money(abs($amount));
    }

    public static function shortDate(CarbonInterface|string|null $date, string $fallback = '-'): string
    {
        if (! $date) {
            return $fallback;
        }

        return ($date instanceof CarbonInterface ? $date : now()->parse($date))->translatedFormat('d M Y');
    }
}
