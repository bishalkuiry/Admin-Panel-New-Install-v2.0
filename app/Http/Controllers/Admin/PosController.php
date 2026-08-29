<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $stores = Store::where('status', 'approved')->orderBy('name')->get();
        $selectedStoreId = $request->query('store_id', $stores->first()?->id);

        $products = Product::with(['primaryImage'])
            ->where('is_active', true)
            ->when($selectedStoreId, function ($q) use ($selectedStoreId) {
                return $q->where('store_id', $selectedStoreId);
            })
            ->limit(50)
            ->get();

        $customers = User::where('is_active', true)->limit(20)->get();

        return view('admin.pos.index', compact('stores', 'selectedStoreId', 'products', 'customers'));
    }

    public function searchProducts(Request $request)
    {
        $query = $request->input('q');
        $storeId = $request->input('store_id');

        $products = Product::with(['primaryImage'])
            ->where('is_active', true)
            ->when($storeId, function ($q) use ($storeId) {
                return $q->where('store_id', $storeId);
            })
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%")
                  ->orWhere('barcode', 'like', "%{$query}%");
            })
            ->limit(25)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    public function placeOrder(Request $request)
    {
        $storeId = $request->input('store_id') 
            ?: Store::where('status', 'approved')->value('id') 
            ?: Store::value('id');

        $customerId = $request->input('customer_id');

        if (empty($customerId) || $customerId === 'guest') {
            $guestUser = User::firstOrCreate(
                ['email' => 'guest@inallcart.com'],
                [
                    'name' => 'Guest Customer',
                    'phone' => '0000000000',
                    'password' => bcrypt('guestpos123'),
                ]
            );
            $customerId = $guestUser->id;
        }

        $request->merge([
            'store_id' => $storeId,
            'customer_id' => $customerId,
        ]);

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'customer_id' => 'required|exists:users,id',
            'store_id' => 'required|exists:stores,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'payment_method' => 'required|string|in:cash,card,wallet,split',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
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

            $order = Order::create([
                'order_number' => 'POS-' . strtoupper(uniqid()),
                'user_id' => $validated['customer_id'],
                'store_id' => $validated['store_id'],
                'status' => \App\Enums\OrderStatus::DELIVERED, // POS orders are delivered on spot
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'paid',
                'notes' => 'Completed via Admin Point-of-Sale (POS)',
                'confirmed_at' => now(),
                'delivered_at' => now(),
            ]);

            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku ?? ('SKU-' . $product->id),
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['price'] * $item['quantity'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'POS Order created successfully!',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'receipt_url' => route('admin.pos.receipt', $order->id),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('POS Order creation failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'POS Checkout failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function receipt(Order $order)
    {
        $order->load(['store', 'user', 'items.product']);
        return view('admin.pos.receipt', compact('order'));
    }
}
