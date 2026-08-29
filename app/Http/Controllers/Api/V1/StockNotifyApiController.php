<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Services\FirebaseCloudMessagingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockNotifyApiController extends Controller
{
    public function __construct(private FirebaseCloudMessagingService $fcmService) {}

    /**
     * Toggle "Notify Me when in Stock" subscription for a product
     */
    public function toggleNotifyMe(Request $request, Product $product)
    {
        $user = $request->user();

        $existing = DB::table('product_notify_subscribers')
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            DB::table('product_notify_subscribers')
                ->where('user_id', $user->id)
                ->where('product_id', $product->id)
                ->delete();

            return response()->json([
                'success' => true,
                'is_subscribed' => false,
                'message' => 'You will no longer be notified when this item is back in stock.',
            ]);
        } else {
            DB::table('product_notify_subscribers')->insert([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'is_subscribed' => true,
                'message' => 'You will be notified as soon as this item is back in stock!',
            ]);
        }
    }

    /**
     * Check subscription status for a product
     */
    public function checkStatus(Request $request, Product $product)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => true, 'is_subscribed' => false]);
        }

        $isSubscribed = DB::table('product_notify_subscribers')
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->where('status', 'pending')
            ->exists();

        return response()->json([
            'success' => true,
            'is_subscribed' => $isSubscribed,
        ]);
    }

    /**
     * Trigger stock alerts when product stock is updated
     */
    public static function checkAndNotifySubscribers(Product $product)
    {
        if ($product->stock <= 0) {
            return;
        }

        $subscribers = DB::table('product_notify_subscribers')
            ->where('product_id', $product->id)
            ->where('status', 'pending')
            ->get();

        if ($subscribers->isEmpty()) {
            return;
        }

        $fcm = app(FirebaseCloudMessagingService::class);

        foreach ($subscribers as $sub) {
            $user = User::find($sub->user_id);
            if ($user && $user->fcm_token) {
                $fcm->sendToUser(
                    $user,
                    "📦 Back in Stock!",
                    "Good news! '{$product->name}' is now back in stock. Order before it runs out!",
                    ['type' => 'product_restocked', 'product_id' => $product->id]
                );
            }
        }

        DB::table('product_notify_subscribers')
            ->where('product_id', $product->id)
            ->where('status', 'pending')
            ->update(['status' => 'notified', 'updated_at' => now()]);
    }
}
