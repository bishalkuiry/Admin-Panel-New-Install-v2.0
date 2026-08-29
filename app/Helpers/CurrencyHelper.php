<?php

namespace App\Helpers;

use App\Models\Setting;
use App\Services\CurrencyExchangeService;

class CurrencyHelper
{
    /**
     * Format currency amount
     */
    public static function format(float $amount, ?string $currency = null): string
    {
        $service = app(CurrencyExchangeService::class);
        return $service->formatCurrency($amount, $currency);
    }

    /**
     * Convert amount between currencies
     */
    public static function convert(float $amount, string $from, string $to): ?float
    {
        $service = app(CurrencyExchangeService::class);
        return $service->convert($amount, $from, $to);
    }

    /**
     * Get default currency
     */
    public static function getDefault(): string
    {
        return Setting::get('default_currency', 'INR');
    }

    /**
     * Get currency symbol
     */
    public static function getSymbol(?string $currency = null): string
    {
        $currency = $currency ?? self::getDefault();
        $service = app(CurrencyExchangeService::class);
        $currencies = $service->getSupportedCurrencies();
        
        return $currencies[$currency]['symbol'] ?? $currency;
    }

    public static function symbol(?string $currency = null): string
    {
        return static::getSymbol($currency);
    }
}
