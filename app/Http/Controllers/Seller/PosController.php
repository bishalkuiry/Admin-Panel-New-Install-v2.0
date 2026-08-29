<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $seller = auth()->user();
        $store = $seller->store ?? $seller->stores()->first();

        if (!$store) {
            return redirect()->route('seller.dashboard')->with('error', 'No store assigned to seller account.');
        }

        $products = Product::with(['primaryImage'])
            ->where('store_id', $store->id)
            ->where('is_active', true)
            ->limit(50)
            ->get();

        $customers = User::where('is_active', true)->limit(20)->get();

        return view('seller.pos.index', compact('store', 'products', 'customers'));
    }

    public function searchProducts(Request $request)
    {
        $seller = auth()->user();
        $store = $seller->store ?? $seller->stores()->first();
        $query = $request->input('q');

        $products = Product::with(['primaryImage'])
            ->where('store_id', $store?->id)
            ->where('is_active', true)
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
        $seller = auth()->user();
        $store = $seller->store ?? $seller->stores()->first();

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
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

            $notes = 'Completed via Seller POS Terminal';
            if ($validated['payment_method'] === 'split') {
                $cash = $validated['split_cash_amount'] ?? 0;
                $card = $validated['split_card_amount'] ?? 0;
                $notes .= " (Split Payment: Cash ₹{$cash}, Card ₹{$card})";
            }

            $order = Order::create([
                'order_number' => 'SPOS-' . strtoupper(uniqid()),
                'user_id' => $validated['customer_id'],
                'store_id' => $store->id,
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
                'message' => 'Seller POS Order completed!',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'receipt_url' => route('admin.pos.receipt', $order->id),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Seller POS Order failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Seller POS Checkout failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
