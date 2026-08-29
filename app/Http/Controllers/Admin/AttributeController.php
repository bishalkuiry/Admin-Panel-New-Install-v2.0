<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Services\SearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttributeController extends Controller
{
    protected $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    public function index(Request $request)
    {
        // Use Scout search if search query is provided
        if ($request->hasAny(['search', 'type', 'filterable'])) {
            try {
                $searchResults = $this->searchService->searchAttributes($request);
                $attributes = $searchResults->paginate(15);
            } catch (\Exception $e) {
                // Fallback to database search if Algolia fails
                $attributes = $this->fallbackSearch($request);
            }
        } else {
            // Default listing without search
            $attributes = Attribute::with('values')
                ->withCount('values')
                ->orderBy('sort_order')
                ->paginate(15);
        }

        return view('admin.attributes.index', compact('attributes'));
    }

    /**
     * Fallback search using database queries
     */
    private function fallbackSearch(Request $request)
    {
        $query = Attribute::with('values')->withCount('values');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('filterable')) {
            $query->where('is_filterable', $request->filterable === 'yes');
        }

        return $query->orderBy('sort_order')->paginate(15);
    }

    public function create()
    {
        return view('admin.attributes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:attributes,name',
            'type' => 'required|in:select,color,size,text',
            'is_filterable' => 'boolean',
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
            'values' => 'array',
            'values.*.value' => 'required|string',
            'values.*.color_code' => 'nullable|string',
        ]);

        $slug = Str::slug($validated['name']);
        
        // Check for slug uniqueness manually as it's not a direct request field
        if (Attribute::where('slug', $slug)->exists()) {
            return redirect()->back()->withErrors(['name' => 'An attribute with this name or similar already exists.'])->withInput();
        }

        $attribute = Attribute::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'type' => $validated['type'],
            'is_filterable' => $validated['is_filterable'] ?? true,
            'is_visible' => $validated['is_visible'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        if (!empty($validated['values'])) {
            foreach ($validated['values'] as $index => $value) {
                AttributeValue::create([
                    'attribute_id' => $attribute->id,
                    'value' => $value['value'],
                    'slug' => Str::slug($value['value']),
                    'color_code' => $value['color_code'] ?? null,
                    'sort_order' => $index,
                ]);
            }
        }

        return redirect()->route('admin.attributes.index')
            ->with('success', 'Attribute created successfully!');
    }

    public function edit(Attribute $attribute)
    {
        $attribute->load('values');
        return view('admin.attributes.edit', compact('attribute'));
    }

    public function update(Request $request, Attribute $attribute)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:attributes,name,' . $attribute->id,
            'type' => 'required|in:select,color,size,text',
            'is_filterable' => 'boolean',
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
            'values' => 'array',
            'values.*.id' => 'nullable|exists:attribute_values,id',
            'values.*.value' => 'required|string',
            'values.*.color_code' => 'nullable|string',
        ]);

        $slug = Str::slug($validated['name']);

        if (Attribute::where('slug', $slug)->where('id', '!=', $attribute->id)->exists()) {
            return redirect()->back()->withErrors(['name' => 'Another attribute with a similar name/slug already exists.'])->withInput();
        }

        $attribute->update([
            'name' => $validated['name'],
            'slug' => $slug,
            'type' => $validated['type'],
            'is_filterable' => $validated['is_filterable'] ?? true,
            'is_visible' => $validated['is_visible'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        // Handle values
        $existingIds = [];
        if (!empty($validated['values'])) {
            foreach ($validated['values'] as $index => $value) {
                if (!empty($value['id'])) {
                    $attrValue = AttributeValue::find($value['id']);
                    $attrValue->update([
                        'value' => $value['value'],
                        'slug' => Str::slug($value['value']),
                        'color_code' => $value['color_code'] ?? null,
                        'sort_order' => $index,
                    ]);
                    $existingIds[] = $value['id'];
                } else {
                    $newValue = AttributeValue::create([
                        'attribute_id' => $attribute->id,
                        'value' => $value['value'],
                        'slug' => Str::slug($value['value']),
                        'color_code' => $value['color_code'] ?? null,
                        'sort_order' => $index,
                    ]);
                    $existingIds[] = $newValue->id;
                }
            }
        }

        // Delete removed values
        $attribute->values()->whereNotIn('id', $existingIds)->delete();

        return redirect()->route('admin.attributes.index')
            ->with('success', 'Attribute updated successfully!');
    }

    public function destroy(Attribute $attribute)
    {
        $attribute->delete();

        return redirect()->route('admin.attributes.index')
            ->with('success', 'Attribute deleted successfully!');
    }

    public function toggleFilterable(Attribute $attribute)
    {
        $attribute->update(['is_filterable' => !$attribute->is_filterable]);
        
        return response()->json([
            'success' => true,
            'is_filterable' => $attribute->is_filterable
        ]);
    }

    public function toggleVisible(Attribute $attribute)
    {
        $attribute->update(['is_visible' => !$attribute->is_visible]);
        
        return response()->json([
            'success' => true,
            'is_visible' => $attribute->is_visible
        ]);
    }
}
