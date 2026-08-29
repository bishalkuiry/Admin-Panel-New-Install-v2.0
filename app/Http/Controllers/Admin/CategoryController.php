<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\SearchService;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Setting;
use App\Services\CloudflareService;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    protected $searchService;
    protected $cloudflareService;
    protected StorageService $storage;

    public function __construct(SearchService $searchService, CloudflareService $cloudflareService, StorageService $storage)
    {
        $this->searchService = $searchService;
        $this->cloudflareService = $cloudflareService;
        $this->storage = $storage;
    }

    public function index(Request $request)
    {
        // Use Scout search if search query is provided
        if ($request->filled('search') || $request->filled('status')) {
            try {
                $searchResults = $this->searchService->searchCategories($request);
                $categories = $searchResults->paginate(15);
            } catch (\Exception $e) {
                // Fallback to database search if Algolia fails
                $categories = $this->fallbackSearch($request);
            }
        } else {
            // Default listing without search
            $categories = Category::with('parent', 'children')
                ->withCount('products')
                ->orderBy('sort_order')
                ->paginate(15);
        }

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Fallback search using database queries
     */
    private function fallbackSearch(Request $request)
    {
        $query = Category::with('parent', 'children')->withCount('products');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        return $query->orderBy('sort_order')->paginate(15);
    }

    /**
     * Helper to purge Cloudflare Cache
     */
    protected function purgeCloudflareCache()
    {
        try {
            $email = Setting::where('key', 'cloudflare_email')->value('value');
            $apiKey = Setting::where('key', 'cloudflare_api_key')->value('value');
            $zoneId = Setting::where('key', 'cloudflare_zone_id')->value('value');

            if ($email && $apiKey && $zoneId) {
                $this->cloudflareService->purgeCache($email, $apiKey, $zoneId);
            }
        } catch (\Exception $e) {
            Log::error('Failed to purge Cloudflare cache: ' . $e->getMessage());
        }
    }

    public function create()
    {
        $parentCategories = Category::parentCategories()->active()->get();
        return view('admin.categories.create', compact('parentCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'image' => 'nullable|image|max:2048',
        ]);

        $slug = Str::slug($validated['name']);
        if (Category::where('slug', $slug)->exists()) {
            return back()->withErrors(['name' => 'A category with this name already exists.'])->withInput();
        }
        $validated['slug'] = $slug;

        if ($request->hasFile('image')) {
            $validated['image'] = $this->storage->store($request->file('image'), 'categories');
        }

        Category::create($validated);
        
        $this->purgeCloudflareCache();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully!');
    }

    public function edit(Category $category)
    {
        $parentCategories = Category::parentCategories()
            ->where('id', '!=', $category->id)
            ->active()
            ->get();

        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'image' => 'nullable|image|max:2048',
        ]);

        $slug = Str::slug($validated['name']);
        if (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
            return back()->withErrors(['name' => 'A category with this name already exists.'])->withInput();
        }
        $validated['slug'] = $slug;

        if ($request->hasFile('image')) {
            $validated['image'] = $this->storage->store($request->file('image'), 'categories');
        }

        $category->update($validated);
        
        $this->purgeCloudflareCache();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully!');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        
        $this->purgeCloudflareCache();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully!');
    }

    public function toggleStatus(Category $category)
    {
        $category->update(['is_active' => !$category->is_active]);
        
        $this->purgeCloudflareCache();
        
        return response()->json([
            'success' => true,
            'is_active' => $category->is_active
        ]);
    }

    /**
     * Get subcategories for dynamic dropdowns
     */
    public function getSubcategories(Category $category)
    {
        return response()->json(
            $category->children()->active()->get(['id', 'name'])->map(function($c) {
                return ['id' => (string)$c->id, 'name' => $c->name];
            })
        );
    }

    /**
     * Get root categories
     */
    public function getRootCategories()
    {
        return response()->json(
            Category::rootCategories()->active()->get(['id', 'name'])->map(function($c) {
                return ['id' => (string)$c->id, 'name' => $c->name];
            })
        );
    }

    /**
     * Get the full chain of categories for editing
     */
    public function getCategoryChain(Category $category)
    {
        $chain = [];
        $ancestors = $category->ancestors();
        
        $currentParentId = null;
        
        // Handle ancestors
        foreach ($ancestors as $ancestor) {
            $list = Category::where('parent_id', $currentParentId)->get(['id', 'name'])->map(function($c) {
                return ['id' => (string)$c->id, 'name' => $c->name];
            });
            $chain[] = [
                'selected' => (string)$ancestor->id,
                'list' => $list
            ];
            $currentParentId = $ancestor->id;
        }

        // Add current category's level
        $chain[] = [
            'selected' => (string)$category->id,
            'list' => Category::where('parent_id', $currentParentId)->get(['id', 'name'])->map(function($c) {
                return ['id' => (string)$c->id, 'name' => $c->name];
            })
        ];

        // Add next level if exists
        $children = $category->children()->active()->get(['id', 'name'])->map(function($c) {
            return ['id' => (string)$c->id, 'name' => $c->name];
        });
        if ($children->count() > 0) {
            $chain[] = [
                'selected' => '',
                'list' => $children
            ];
        }

        return response()->json($chain);
    }
}
