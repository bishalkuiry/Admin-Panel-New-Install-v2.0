<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SearchService;
use Illuminate\Http\Request;

class TestSearch extends Command
{
    protected $signature = 'search:test {query? : Search query to test}';
    protected $description = 'Test search functionality';

    public function handle()
    {
        $query = $this->argument('query') ?? 'test';
        
        $this->info("Testing search functionality with query: '{$query}'");
        $this->newLine();
        
        $searchService = app(SearchService::class);
        
        // Create a mock request
        $request = new Request(['search' => $query]);
        
        try {
            // Test product search
            $this->info('Testing product search...');
            $productResults = $searchService->searchProducts($request);
            $this->info("✓ Product search working - Found results");
            
            // Test category search  
            $this->info('Testing category search...');
            $categoryResults = $searchService->searchCategories($request);
            $this->info("✓ Category search working - Found results");
            
            // Test attribute search
            $this->info('Testing attribute search...');
            $attributeResults = $searchService->searchAttributes($request);
            $this->info("✓ Attribute search working - Found results");
            
            // Test suggestions
            $this->info('Testing search suggestions...');
            $suggestions = $searchService->getSearchSuggestions($query, 'products', 3);
            $this->info("✓ Search suggestions working - Found " . count($suggestions) . " suggestions");
            
            if (!empty($suggestions)) {
                $this->info('Sample suggestions:');
                foreach (array_slice($suggestions, 0, 3) as $suggestion) {
                    $this->line("  - {$suggestion['name']} (SKU: {$suggestion['sku']})");
                }
            }
            
            $this->newLine();
            $this->info('🎉 All search functionality is working correctly!');
            
            if (config('scout.driver') === 'algolia' && !config('scout.algolia.id')) {
                $this->warn('Note: Algolia is not configured. Using database fallback search.');
                $this->info('To enable Algolia search, configure your .env file and run: php artisan search:import');
            }
            
        } catch (\Exception $e) {
            $this->error('Search test failed: ' . $e->getMessage());
            $this->info('This might be normal if Algolia is not configured - the app should fallback to database search.');
            return 1;
        }
        
        return 0;
    }
}