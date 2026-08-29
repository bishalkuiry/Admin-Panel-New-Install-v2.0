<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Product;
use App\Models\ProductVariant;

class OrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.variant_id' => ['nullable', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'address_id' => ['required', 'exists:addresses,id'],
            'payment_method' => ['required', 'string', 'in:cod,razorpay,stripe,paystack,flutterwave,phonepe,paytm,wallet'],
            'notes' => ['nullable', 'string', 'max:500'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'distance' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'driver_tip' => ['nullable', 'numeric', 'min:0'],
            'tip' => ['nullable', 'numeric', 'min:0'],
            'delivery_man_tips' => ['nullable', 'numeric', 'min:0'],
            'use_wallet' => ['nullable', 'boolean'],
            // Client-generated idempotency key — prevents duplicate orders on
            // network retry or double-tap. UUID v4 recommended from the client.
            'idempotency_key' => ['nullable', 'string', 'max:64'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $items = $this->input('items', []);
            foreach ($items as $index => $item) {
                $productId = $item['product_id'] ?? null;
                $variantId = $item['variant_id'] ?? null;
                $quantity = $item['quantity'] ?? 0;

                if ($productId) {
                    $product = Product::find($productId);
                    if ($product) {
                        if ($variantId) {
                            $variant = ProductVariant::where('product_id', $productId)->find($variantId);
                            if ($variant && $variant->quantity < $quantity) {
                                $validator->errors()->add("items.$index.quantity", "Insufficient stock for {$product->name} - {$variant->display_name}. Only {$variant->quantity} left.");
                            }
                        } elseif ($product->quantity < $quantity) {
                            $validator->errors()->add("items.$index.quantity", "Insufficient stock for {$product->name}. Only {$product->quantity} left.");
                        }
                    }
                }
            }
        });
    }
}
