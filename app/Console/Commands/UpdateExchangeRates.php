<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Services\CurrencyExchangeService;
use Illuminate\Console\Command;

class UpdateExchangeRates extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'currency:update-rates';

    /**
     * The console command description.
     */
    protected $description = 'Update currency exchange rates from API';

    /**
     * Execute the console command.
     */
    public function handle(CurrencyExchangeService $currencyService): int
    {
        $this->info('Updating exchange rates...');
        
        // Check if auto update is enabled
        $autoUpdate = Setting::get('auto_exchange_rate_update', '1');
        
        if ($autoUpdate !== '1') {
            $this->warn('Automatic exchange rate updates are disabled.');
            return self::FAILURE;
        }
        
        // Get base currency
        $baseCurrency = Setting::get('default_currency', 'INR');
        
        $this->info("Fetching rates for base currency: {$baseCurrency}");
        
        // Update rates
        $success = $currencyService->forceUpdate($baseCurrency);
        
        if ($success) {
            $this->info('✓ Exchange rates updated successfully!');
            return self::SUCCESS;
        }
        
        $this->error('✗ Failed to update exchange rates.');
        return self::FAILURE;
    }
}
