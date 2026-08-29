<?php

namespace App\Imports;

use App\Models\Category;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Throwable;

class CategoriesImport implements ToCollection, WithHeadingRow, SkipsOnError
{
    public function onError(Throwable $e)
    {
        Log::warning('Category import error: ' . $e->getMessage());
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if (empty($row['name'])) continue;
            
            // Find existing category by slug or name
            $slug = Str::slug($row['name']);
            $category = Category::withTrashed()->where('slug', $slug)->first();
            
            if (!$category) {
                $category = Category::withTrashed()->where('name', $row['name'])->first();
            }
            
            if (!$category) {
                $category = new Category();
            }
            
            // Restore if soft-deleted
            if ($category->exists && $category->trashed()) {
                $category->restore();
            }
            
            $data = [
                'name' => $row['name'],
                'slug' => $slug,
                'is_active' => isset($row['is_active']) ? (bool)$row['is_active'] : true,
                'is_featured' => isset($row['is_featured']) ? (bool)$row['is_featured'] : false,
            ];
            
            // Set parent_id if provided
            if (!empty($row['parent_id']) && is_numeric($row['parent_id'])) {
                $parent = Category::find($row['parent_id']);
                if ($parent) {
                    $data['parent_id'] = $parent->id;
                }
            } else {
                $data['parent_id'] = null;  // Root category
            }
            
            // Handle image URL
            if (!empty($row['image'])) {
                $data['image'] = $this->processImagePath($row['image']);
            }
            
            $category->fill($data);
            $category->save();
        }
        
        Log::info('Categories imported successfully', ['count' => $rows->count()]);
    }
    
    private function processImagePath($url)
    {
        // Already a full URL with /storage/ — strip to relative path
        if (str_contains($url, '/storage/')) {
            return ltrim(str_replace(url('/storage'), '', $url), '/');
        }

        // Already has a folder prefix (e.g. "categories/uuid.png") — use as-is
        if (str_contains($url, '/')) {
            return $url;
        }

        // Bare filename (e.g. "uuid.png") — assume it was uploaded to categories/
        return 'categories/' . $url;
    }
}

