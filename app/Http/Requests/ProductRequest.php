<?php

namespace App\Http\Requests;

use App\Enums\ProductUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->ignore($productId),
            ],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'compare_price' => ['nullable', 'numeric', 'min:0', 'gt:price'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:65535'],
            'quantity' => ['required', 'integer', 'min:0', 'max:999999'],
            'low_stock_threshold' => ['required', 'integer', 'min:0', 'max:1000'],
            'unit' => ['required', 'string', new Enum(ProductUnit::class)],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'brand' => ['nullable', 'string', 'max:100'],
            'barcode' => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'compare_price.gt' => 'Compare price must be greater than the selling price.',
            'images.max' => 'You can upload a maximum of 10 images.',
            'images.*.max' => 'Each image must be less than 2MB.',
            'unit.Illuminate\Validation\Rules\Enum' => 'The selected unit is invalid. Valid options: piece, kg, g, liter, ml, pack.',
        ];
    }
}
