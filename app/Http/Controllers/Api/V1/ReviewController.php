<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use App\Enums\OrderStatus;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    public function __construct(private StorageService $storage) {}

    /**
     * Submit reviews for delivered order items.
     * Accepts multipart/form-data with:
     *   delivery_rating (optional int 1-5)
     *   reviews[0][product_id], reviews[0][rating], reviews[0][comment]
     *   images[{product_id}][]  — up to 5 images per product
     */
    public function store(Request $request, int $orderId)
    {
        $user = Auth::user();

        $order = Order::where('id', $orderId)
            ->where('user_id', $user->id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        $statusStr = is_object($order->status) ? $order->status->value : (string)$order->status;
        $allowedStatuses = ['delivered', 'completed', OrderStatus::DELIVERED->value];
        if (!in_array(strtolower($statusStr), array_map('strtolower', $allowedStatuses))) {
            return response()->json([
                'success' => false,
                'message' => 'Reviews can only be submitted for completed or delivered orders.',
            ], 400);
        }

        if (!$request->has('reviews') && !$request->has('delivery_rating')) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide a star rating to submit your review.',
            ], 422);
        }

        $request->validate([
            'reviews'                => 'nullable|array',
            'reviews.*.product_id'   => 'required_with:reviews|integer|exists:products,id',
            'reviews.*.rating'       => 'required_with:reviews|integer|min:1|max:5',
            'reviews.*.comment'      => 'nullable|string|max:1000',
            'delivery_rating'        => 'nullable|integer|min:1|max:5',
        ]);

        $submitted = [];
        $allFiles = $request->allFiles();

        if ($request->has('reviews') && is_array($request->reviews)) {
            foreach ($request->reviews as $item) {
                $productId = $item['product_id'];
                $imagePaths = [];

                if (isset($allFiles['images'][$productId])) {
                    $files = $allFiles['images'][$productId];
                    if (!is_array($files)) {
                        $files = [$files];
                    }
                    foreach ($files as $file) {
                        if ($file && $file->isValid()) {
                            try {
                                $path = $this->storage->store($file, "reviews/{$order->id}");
                                $imagePaths[] = $path;
                            } catch (\Exception $e) {
                                Log::error("Failed to store review image: " . $e->getMessage());
                            }
                        }
                    }
                }

                $review = Review::updateOrCreate(
                    [
                        'user_id'    => $user->id,
                        'product_id' => $productId,
                        'order_id'   => $order->id,
                    ],
                    [
                        'rating'               => $item['rating'],
                        'comment'              => $item['comment'] ?? null,
                        'images'               => !empty($imagePaths) ? $imagePaths : null,
                        'delivery_rating'      => $request->input('delivery_rating'),
                        'is_verified_purchase' => true,
                        'is_approved'          => true,
                    ]
                );
                $submitted[] = (int) $review->product_id;
            }
        }

        return response()->json([
            'success'              => true,
            'message'              => 'Thank you for your review!',
            'reviewed_product_ids' => $submitted,
        ]);
    }

    /**
     * Returns which products in this order have already been reviewed.
     */
    public function reviewedProducts(int $orderId)
    {
        $user = Auth::user();

        $order = Order::where('id', $orderId)
            ->where('user_id', $user->id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        $reviewedIds = Review::where('user_id', $user->id)
            ->where('order_id', $order->id)
            ->pluck('product_id')
            ->map(fn($id) => (int)$id);

        return response()->json([
            'success'              => true,
            'reviewed_product_ids' => $reviewedIds,
        ]);
    }
}
