<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductRepository
{
    public function __construct(
        private Product $model
    ) {}

    public function getAllWithFilters(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = $this->model->with(['category', 'primaryImage'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('sku', 'like', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (!empty($filters['stock']) && $filters['stock'] === 'low') {
            $query->whereColumn('quantity', '<=', 'low_stock_threshold');
        }

        if (!empty($filters['stock']) && $filters['stock'] === 'out') {
            $query->where('quantity', 0);
        }

        return $query->latest()->paginate($perPage);
    }

    public function findById(int $id): ?Product
    {
        return $this->model->with(['category', 'images', 'attributes'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->find($id);
    }

    public function create(array $data): Product
    {
        return $this->model->create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        return $product->fresh();
    }

    public function delete(Product $product): bool
    {
        return $product->delete();
    }

    public function getLowStockCount(): int
    {
        return $this->model->whereColumn('quantity', '<=', 'low_stock_threshold')->count();
    }

    public function getActiveCount(): int
    {
        return $this->model->where('is_active', true)->count();
    }
}
