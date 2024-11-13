<?php
/**
 * Created by PhpStorm.
 * User: USER
 * Date: 11/11/2024
 * Time: 1:01 PM
 */

namespace App\Services;

class CurrencyService
{
    const RATES = [
        'usd' => [
            'eur' => 0.98
        ],
    ];

    public function convert(float $amount, string $currencyFrom, string $currencyTo): float
    {
        $rate = self::RATES[$currencyFrom][$currencyTo] ?? 0;

        return round($amount * $rate, 2);
    }
}
