<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductReturnApiController extends Controller
{
    /**
     * Get list of user's return & replacement requests
     */
    public function index(Request $request)
    {
        $returns = ProductReturn::with(['order'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $returns,
        ]);
    }

    /**
     * Submit a Return or Replacement Request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'order_item_id' => 'nullable|exists:order_items,id',
            'type' => 'required|in:return,replacement',
            'reason' => 'required|string|max:255',
            'comments' => 'nullable|string|max:1000',
            'attachments' => 'nullable|array',
            'attachments.*' => 'string', // File / Photo URLs
        ]);

        $order = Order::where('id', $validated['order_id'])
            ->where('user_id', Auth::id())
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or unauthorized',
            ], 404);
        }

        try {
            DB::beginTransaction();

            $returnRequest = ProductReturn::create([
                'order_id' => $order->id,
                'order_item_id' => $validated['order_item_id'] ?? null,
                'user_id' => Auth::id(),
                'store_id' => $order->store_id,
                'type' => $validated['type'],
                'reason' => $validated['reason'],
                'comments' => $validated['comments'] ?? null,
                'attachments' => $validated['attachments'] ?? [],
                'status' => 'pending',
                'refund_amount' => $order->total ?? 0.00,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Return request submitted successfully',
                'data' => $returnRequest,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API Return request creation failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit return request: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show single return request details
     */
    public function show(ProductReturn $productReturn)
    {
        if ($productReturn->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $productReturn->load(['order.items.product']);

        return response()->json([
            'success' => true,
            'data' => $productReturn,
        ]);
    }
}
