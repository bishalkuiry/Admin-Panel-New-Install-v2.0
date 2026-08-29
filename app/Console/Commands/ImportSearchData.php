<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;

class ImportSearchData extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'search:import {--model=all : The model to import (products, categories, attributes, or all)}';

    /**
     * The console command description.
     */
    protected $description = 'Import existing data to search engine (Algolia)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $model = $this->option('model');
        
        $this->info('Starting search data import...');
        
        try {
            if ($model === 'all' || $model === 'products') {
                $this->importProducts();
            }
            
            if ($model === 'all' || $model === 'categories') {
                $this->importCategories();
            }
            
            if ($model === 'all' || $model === 'attributes') {
                $this->importAttributes();
            }
            
            $this->info('Search data import completed successfully!');
            
        } catch (\Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());
            $this->info('Make sure your Algolia credentials are configured in .env file');
            return 1;
        }
        
        return 0;
    }
    
    private function importProducts()
    {
        $this->info('Importing products...');
        
        $count = Product::count();
        $bar = $this->output->createProgressBar($count);
        
        Product::with('category')->chunk(100, function ($products) use ($bar) {
            foreach ($products as $product) {
                $product->searchable();
                $bar->advance();
            }
        });
        
        $bar->finish();
        $this->newLine();
        $this->info("Imported {$count} products");
    }
    
    private function importCategories()
    {
        $this->info('Importing categories...');
        
        $count = Category::count();
        $bar = $this->output->createProgressBar($count);
        
        Category::chunk(100, function ($categories) use ($bar) {
            foreach ($categories as $category) {
                $category->searchable();
                $bar->advance();
            }
        });
        
        $bar->finish();
        $this->newLine();
        $this->info("Imported {$count} categories");
    }
    
    private function importAttributes()
    {
        $this->info('Importing attributes...');
        
        $count = Attribute::count();
        $bar = $this->output->createProgressBar($count);
        
        Attribute::chunk(100, function ($attributes) use ($bar) {
            foreach ($attributes as $attribute) {
                $attribute->searchable();
                $bar->advance();
            }
        });
        
        $bar->finish();
        $this->newLine();
        $this->info("Imported {$count} attributes");
    }
}