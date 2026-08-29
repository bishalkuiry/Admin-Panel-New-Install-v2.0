<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoryRepository
{
    public function __construct(
        private Category $model
    ) {}

    public function getAllWithFilters(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = $this->model->with(['parent', 'children'])->withCount('products');

        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderBy('sort_order')->paginate($perPage);
    }

    public function getParentCategories(): Collection
    {
        return $this->model->whereNull('parent_id')->where('is_active', true)->get();
    }

    public function getActiveCategories(): Collection
    {
        return $this->model->where('is_active', true)->orderBy('sort_order')->get();
    }

    public function findById(int $id): ?Category
    {
        return $this->model->with(['parent', 'children', 'products'])->find($id);
    }

    public function create(array $data): Category
    {
        return $this->model->create($data);
    }

    public function update(Category $category, array $data): Category
    {
        $category->update($data);
        return $category->fresh();
    }

    public function delete(Category $category): bool
    {
        return $category->delete();
    }
}
