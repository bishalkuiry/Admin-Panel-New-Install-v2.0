<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;
use Illuminate\Http\Request;

class SearchService
{
    /**
     * Search products with advanced filters
     */
    public function searchProducts(Request $request)
    {
        $query = $request->get('search', '');
        $filters = [];
        
        // Category filter
        if ($request->filled('category')) {
            $filters[] = 'category_id:' . $request->category;
        }
        
        // Status filter
        if ($request->filled('status')) {
            $filters[] = 'is_active:' . ($request->status === 'active' ? 'true' : 'false');
        }
        
        // Stock filter
        if ($request->filled('stock')) {
            switch ($request->stock) {
                case 'out':
                    $filters[] = 'quantity:0';
                    break;
                case 'low':
                    $filters[] = 'quantity:1 TO 5';
                    break;
                case 'in':
                    $filters[] = 'quantity:6 TO 999999';
                    break;
            }
        }
        
        // Price range filter
        if ($request->filled('min_price') || $request->filled('max_price')) {
            $minPrice = $request->get('min_price', 0);
            $maxPrice = $request->get('max_price', 999999);
            $filters[] = "price:{$minPrice} TO {$maxPrice}";
        }
        
        // Brand filter
        if ($request->filled('brand')) {
            $filters[] = 'brand:"' . $request->brand . '"';
        }
        
        // In-house filter (store_id IS NULL)
        if ($request->boolean('in_house')) {
            $filters[] = 'store_id IS NULL';
        }
        
        // Seller filter (store_id IS NOT NULL)
        if ($request->boolean('is_seller')) {
            $filters[] = 'store_id IS NOT NULL';
        }
        
        // Specific store filter
        if ($request->filled('store_id')) {
            $filters[] = 'store_id:' . $request->store_id;
        }
        
        $searchOptions = [
            'filters' => implode(' AND ', $filters),
            'hitsPerPage' => 15,
            'page' => $request->get('page', 1) - 1, // Algolia uses 0-based pagination
        ];
        
        if (empty($query)) {
            // If no search query, return all products with filters
            return Product::search('*')->options($searchOptions);
        }
        
        return Product::search($query)->options($searchOptions);
    }
    
    /**
     * Search categories
     */
    public function searchCategories(Request $request)
    {
        $query = $request->get('search', '');
        $filters = [];
        
        if ($request->filled('status')) {
            $filters[] = 'is_active:' . ($request->status === 'active' ? 'true' : 'false');
        }
        
        if ($request->filled('parent')) {
            if ($request->parent === 'none') {
                $filters[] = 'parent_id IS NULL';
            } else {
                $filters[] = 'parent_id:' . $request->parent;
            }
        }
        
        $searchOptions = [
            'filters' => implode(' AND ', $filters),
            'hitsPerPage' => 15,
            'page' => $request->get('page', 1) - 1,
        ];
        
        if (empty($query)) {
            return Category::search('*')->options($searchOptions);
        }
        
        return Category::search($query)->options($searchOptions);
    }
    
    /**
     * Search attributes
     */
    public function searchAttributes(Request $request)
    {
        $query = $request->get('search', '');
        $filters = [];
        
        if ($request->filled('type')) {
            $filters[] = 'type:"' . $request->type . '"';
        }
        
        if ($request->filled('filterable')) {
            $filters[] = 'is_filterable:' . ($request->filterable === 'yes' ? 'true' : 'false');
        }
        
        $searchOptions = [
            'filters' => implode(' AND ', $filters),
            'hitsPerPage' => 15,
            'page' => $request->get('page', 1) - 1,
        ];
        
        if (empty($query)) {
            return Attribute::search('*')->options($searchOptions);
        }
        
        return Attribute::search($query)->options($searchOptions);
    }
    
    /**
     * Get search suggestions for autocomplete
     */
    public function getSearchSuggestions(string $query, string $type = 'products', int $limit = 5): array
    {
        $searchOptions = [
            'hitsPerPage' => $limit,
            'attributesToRetrieve' => ['id', 'name', 'sku'],
        ];
        
        switch ($type) {
            case 'products':
                $results = Product::search($query)->options($searchOptions)->get();
                return $results->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'type' => 'product'
                    ];
                })->toArray();
                
            case 'categories':
                $results = Category::search($query)->options($searchOptions)->get();
                return $results->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'type' => 'category'
                    ];
                })->toArray();
                
            default:
                return [];
        }
    }
    
    /**
     * Get popular search terms (mock implementation)
     */
    public function getPopularSearchTerms(): array
    {
        return [
            'fruits',
            'vegetables',
            'dairy',
            'snacks',
            'beverages',
            'organic',
            'fresh',
            'frozen'
        ];
    }
}