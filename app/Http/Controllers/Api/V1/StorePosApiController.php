<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StorePosApiController extends Controller
{
    public function getProducts(Request $request)
    {
        $storeId = $request->user()?->store_id ?? $request->query('store_id');
        $query = $request->query('q');

        $products = Product::with(['primaryImage'])
            ->where('is_active', true)
            ->when($storeId, function ($q) use ($storeId) {
                return $q->where('store_id', $storeId);
            })
            ->when($query, function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('name', 'like', "%{$query}%")
                        ->orWhere('sku', 'like', "%{$query}%")
                        ->orWhere('barcode', 'like', "%{$query}%");
                });
            })
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    public function checkout(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'store_id' => 'required|exists:stores,id',
            'customer_id' => 'required|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'payment_method' => 'required|string|in:cash,card,wallet,split',
            'split_cash_amount' => 'nullable|numeric|min:0',
            'split_card_amount' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $validated = $validator->validated();

        try {
            DB::beginTransaction();

            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += ($item['price'] * $item['quantity']);
            }

            $discount = $validated['discount'] ?? 0;
            $tax = $validated['tax'] ?? 0;
            $total = ($subtotal - $discount) + $tax;

            $notes = 'Store App POS Checkout';
            if ($validated['payment_method'] === 'split') {
                $cash = $validated['split_cash_amount'] ?? 0;
                $card = $validated['split_card_amount'] ?? 0;
                $notes .= " (Split Payment: Cash ₹{$cash}, Card ₹{$card})";
            }

            $order = Order::create([
                'order_number' => 'APPOS-' . strtoupper(uniqid()),
                'user_id' => $validated['customer_id'],
                'store_id' => $validated['store_id'],
                'status' => \App\Enums\OrderStatus::DELIVERED,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'paid',
                'notes' => $notes,
                'confirmed_at' => now(),
                'delivered_at' => now(),
            ]);

            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['price'] * $item['quantity'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Store App POS Order completed!',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'tax' => $tax,
                    'total' => $total,
                    'receipt_url' => route('admin.pos.receipt', $order->id),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Store App POS Checkout error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
