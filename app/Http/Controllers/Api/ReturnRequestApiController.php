<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductReturn;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReturnRequestApiController extends Controller
{
    /**
     * Customer: Submit product-wise return/replacement request.
     */
    public function submitCustomerReturn(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id'      => 'required|exists:orders,id',
            'order_item_id' => 'required|exists:order_items,id',
            'type'          => 'nullable|in:return,replacement',
            'reason'        => 'required|string|max:255',
            'comments'      => 'nullable|string|max:1000',
            'attachments'   => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $order = Order::where('id', $request->order_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found or unauthorized'], 404);
        }

        $orderItem = OrderItem::where('id', $request->order_item_id)
            ->where('order_id', $order->id)
            ->first();

        if (!$orderItem) {
            return response()->json(['success' => false, 'message' => 'Order item not found'], 404);
        }

        // Check if return request already exists for this item
        $existingReturn = ProductReturn::where('order_item_id', $orderItem->id)
            ->whereNotIn('status', ['rejected'])
            ->first();

        if ($existingReturn) {
            return response()->json([
                'success' => false,
                'message' => 'A return/replacement request already exists for this product item.',
                'data'    => $existingReturn,
            ], 400);
        }

        $refundAmount = $orderItem->total_price ?? ($orderItem->price * ($orderItem->quantity ?? 1));

        $productReturn = ProductReturn::create([
            'order_id'      => $order->id,
            'order_item_id' => $orderItem->id,
            'user_id'       => $user->id,
            'store_id'      => $order->store_id,
            'type'          => $request->input('type', 'return'),
            'reason'        => $request->reason,
            'comments'      => $request->comments,
            'attachments'   => $request->attachments ?? [],
            'status'        => 'pending',
            'refund_amount' => $refundAmount,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Return request submitted successfully.',
            'data'    => $productReturn->load(['order', 'store']),
        ], 201);
    }

    /**
     * Customer: View my return requests.
     */
    public function getCustomerReturns(Request $request): JsonResponse
    {
        $user = $request->user();
        $returns = ProductReturn::with(['order', 'store'])
            ->where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => $returns,
        ]);
    }

    /**
     * Store/Vendor: View return requests for my store.
     */
    public function getVendorReturns(Request $request): JsonResponse
    {
        $user = $request->user();
        $storeId = $user->store_id ?? $request->input('store_id');

        $returns = ProductReturn::with(['order', 'user'])
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->orderBy('id', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => $returns,
        ]);
    }

    /**
     * Store/Vendor: Update return status (approve/reject).
     */
    public function updateVendorReturnStatus(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status'      => 'required|in:approved,rejected',
            'admin_notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $productReturn = ProductReturn::findOrFail($id);
        $productReturn->status = $request->status;
        if ($request->filled('admin_notes')) {
            $productReturn->admin_notes = $request->admin_notes;
        }
        $productReturn->save();

        return response()->json([
            'success' => true,
            'message' => "Return request has been {$request->status}.",
            'data'    => $productReturn,
        ]);
    }

    /**
     * Driver: View return pickup assignments.
     */
    public function getDriverReturnPickups(Request $request): JsonResponse
    {
        $returns = ProductReturn::with(['order', 'user', 'store'])
            ->whereIn('status', ['approved', 'pickup_scheduled'])
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $returns,
        ]);
    }

    /**
     * Driver: Update return pickup status.
     */
    public function updateDriverReturnPickup(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:picked_up,completed,refunded',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $productReturn = ProductReturn::findOrFail($id);
        $productReturn->status = $request->status;
        $productReturn->save();

        // If returned & completed, issue wallet refund to user automatically
        if ($request->status === 'refunded' || $request->status === 'completed') {
            DB::transaction(function () use ($productReturn) {
                $user = User::find($productReturn->user_id);
                if ($user && $productReturn->refund_amount > 0) {
                    $user->wallet_balance = ($user->wallet_balance ?? 0) + $productReturn->refund_amount;
                    $user->save();

                    WalletTransaction::create([
                        'user_id'          => $user->id,
                        'type'             => 'credit',
                        'amount'           => $productReturn->refund_amount,
                        'description'      => "Refund for Return Request #{$productReturn->return_number}",
                        'transaction_type' => 'refund',
                        'status'           => 'completed',
                    ]);
                }
            });
        }

        return response()->json([
            'success' => true,
            'message' => 'Return pickup status updated successfully.',
            'data'    => $productReturn,
        ]);
    }
}
