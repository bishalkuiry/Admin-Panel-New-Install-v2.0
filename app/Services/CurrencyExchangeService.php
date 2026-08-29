<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyExchangeService
{
    /**
     * Free ExchangeRate-API endpoint
     * No API key required for basic usage (1,500 requests/month)
     */
    private const API_URL = 'https://open.er-api.com/v6/latest/';

    /**
     * Get exchange rate from base currency to target currency
     */
    public function getExchangeRate(string $from, string $to): ?float
    {
        $rates = $this->getExchangeRates($from);
        
        return $rates[$to] ?? null;
    }

    /**
     * Get all exchange rates for a base currency
     */
    public function getExchangeRates(string $baseCurrency = 'INR'): array
    {
        $cacheKey = "exchange_rates_{$baseCurrency}";
        
        // Try to get from cache first
        $rates = Cache::get($cacheKey);
        
        if ($rates !== null) {
            return $rates;
        }
        
        // Fetch from API
        $rates = $this->fetchExchangeRates($baseCurrency);
        
        if (!empty($rates)) {
            // Cache for 24 hours
            Cache::put($cacheKey, $rates, now()->addHours(24));
            
            // Update last update timestamp
            Setting::set('exchange_rate_last_update', now()->toDateTimeString(), 'mobile_app');
        }
        
        return $rates;
    }

    /**
     * Fetch exchange rates from API
     */
    private function fetchExchangeRates(string $baseCurrency): array
    {
        try {
            $response = Http::timeout(10)->get(self::API_URL . $baseCurrency);
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['rates']) && is_array($data['rates'])) {
                    return $data['rates'];
                }
            }
            
            Log::warning('Failed to fetch exchange rates', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Exchange rate API error: ' . $e->getMessage());
        }
        
        return [];
    }

    /**
     * Convert amount from one currency to another
     */
    public function convert(float $amount, string $from, string $to): ?float
    {
        if ($from === $to) {
            return $amount;
        }
        
        $rate = $this->getExchangeRate($from, $to);
        
        if ($rate === null) {
            return null;
        }
        
        return $amount * $rate;
    }

    /**
     * Force update exchange rates
     */
    public function forceUpdate(string $baseCurrency = 'INR'): bool
    {
        // Clear cache
        Cache::forget("exchange_rates_{$baseCurrency}");
        
        // Fetch new rates
        $rates = $this->fetchExchangeRates($baseCurrency);
        
        if (!empty($rates)) {
            // Cache for 24 hours
            Cache::put("exchange_rates_{$baseCurrency}", $rates, now()->addHours(24));
            
            // Update last update timestamp
            Setting::set('exchange_rate_last_update', now()->toDateTimeString(), 'mobile_app');
            
            return true;
        }
        
        return false;
    }

    /**
     * Get supported currencies
     */
    public function getSupportedCurrencies(): array
    {
        return [
            'INR' => ['name' => 'Indian Rupee', 'symbol' => '₹'],
            'USD' => ['name' => 'US Dollar', 'symbol' => '$'],
            'EUR' => ['name' => 'Euro', 'symbol' => '€'],
            'GBP' => ['name' => 'British Pound', 'symbol' => '£'],
            'AUD' => ['name' => 'Australian Dollar', 'symbol' => 'A$'],
            'CAD' => ['name' => 'Canadian Dollar', 'symbol' => 'C$'],
            'SGD' => ['name' => 'Singapore Dollar', 'symbol' => 'S$'],
            'AED' => ['name' => 'UAE Dirham', 'symbol' => 'د.إ'],
            'SAR' => ['name' => 'Saudi Riyal', 'symbol' => '﷼'],
            'ZAR' => ['name' => 'South African Rand', 'symbol' => 'R'],
            'NGN' => ['name' => 'Nigerian Naira', 'symbol' => '₦'],
            'KES' => ['name' => 'Kenyan Shilling', 'symbol' => 'KSh'],
            'BDT' => ['name' => 'Bangladeshi Taka', 'symbol' => '৳'],
            'PKR' => ['name' => 'Pakistani Rupee', 'symbol' => '₨'],
            'LKR' => ['name' => 'Sri Lankan Rupee', 'symbol' => 'Rs'],
            'MYR' => ['name' => 'Malaysian Ringgit', 'symbol' => 'RM'],
            'IDR' => ['name' => 'Indonesian Rupiah', 'symbol' => 'Rp'],
            'PHP' => ['name' => 'Philippine Peso', 'symbol' => '₱'],
            'THB' => ['name' => 'Thai Baht', 'symbol' => '฿'],
            'VND' => ['name' => 'Vietnamese Dong', 'symbol' => '₫'],
        ];
    }

    /**
     * Format currency amount
     */
    public function formatCurrency(float $amount, ?string $currency = null): string
    {
        $currency = $currency ?? Setting::get('default_currency', 'INR');
        $symbolPosition = Setting::get('currency_symbol_position', 'left');
        $decimalPlaces = (int) Setting::get('currency_decimal_places', 0);
        $thousandSeparator = Setting::get('currency_thousand_separator', ',');
        $decimalSeparator = $thousandSeparator === ',' ? '.' : ',';
        
        $currencies = $this->getSupportedCurrencies();
        $symbol = $currencies[$currency]['symbol'] ?? $currency;
        
        $formattedAmount = number_format($amount, $decimalPlaces, $decimalSeparator, $thousandSeparator);
        
        if ($symbolPosition === 'left') {
            return $symbol . $formattedAmount;
        }
        
        return $formattedAmount . $symbol;
    }
}
