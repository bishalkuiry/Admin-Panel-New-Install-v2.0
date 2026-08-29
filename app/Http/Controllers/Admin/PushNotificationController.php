<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Store;
use App\Models\Product;
use App\Models\Brand;
use App\Models\User;
use App\Models\EmailTemplate;
use App\Enums\UserRole;
use App\Jobs\SendPromotionEmailJob;
use App\Services\FirebaseCloudMessagingService;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PushNotificationController extends Controller
{
    public function __construct(
        private FirebaseCloudMessagingService $fcmService,
        private StorageService $storage,
    ) {}

    /**
     * Show push notification form
     */
    public function index()
    {
        $categories = Category::active()->orderBy('name')->get();
        $stores = Store::active()->orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        
        return view('admin.push-notifications.index', compact('categories', 'stores', 'brands'));
    }

    /**
     * Search products for notification
     */
    public function searchProducts(Request $request)
    {
        $query = $request->query('q', '');
        
        $products = Product::where('name', 'like', "%{$query}%")
            ->orWhere('sku', 'like', "%{$query}%")
            ->limit(20)
            ->get(['id', 'name', 'sku', 'price']);
        
        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Send push notification
     */
    public function send(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'message' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
            'target_type' => 'required|in:home,category,store,product,brand,custom',
            'target_id' => 'required_unless:target_type,home,custom',
            'custom_link' => 'required_if:target_type,custom|nullable|string|max:500',
            'send_to' => 'required|in:all,topic',
            'topic' => 'required_if:send_to,topic|nullable|string|max:50',
        ]);

        if (!$this->fcmService->isConfigured()) {
            return back()->with('error', 'Firebase Cloud Messaging is not configured. Please configure it in Mobile App Settings.');
        }

        try {
            // Handle image upload
            $imageUrl = null;
            if ($request->hasFile('image')) {
                $path = $this->storage->store($request->file('image'), 'notifications');
                $imageUrl = $this->storage->url($path);
            }
            
            // Build notification data
            $notification = [
                'title' => $request->title,
                'body' => $request->message,
            ];

            if ($imageUrl) {
                $notification['image'] = $imageUrl;
            }

            // Build deep link data based on target type
            $data = $this->buildDeepLinkData($request);

            // Send notification
            $success = false;
            if ($request->send_to === 'all') {
                // Send to all users with FCM tokens
                $users = User::whereNotNull('fcm_token')->get();
                
                if ($users->isEmpty()) {
                    return back()->with('error', 'No users with FCM tokens found.');
                }
                
                // Convert Collection to array for the service
                $success = $this->fcmService->sendToUsers($users->all(), $notification, $data);
            } else {
                // Send to specific topic
                $success = $this->fcmService->sendToTopic($request->topic, $notification, $data);
            }

            if ($success) {
                // Determine if we should send email
                // For now, always try sending an email if it's a broadcast to 'all' or 'customer' topic
                if ($request->send_to === 'all' || ($request->send_to === 'topic' && $request->topic === 'customers')) {
                    // This is a promotion email
                    $this->sendPromotionEmail($request, $imageUrl);
                }

                Log::info('Push notification sent successfully', [
                    'title' => $request->title,
                    'target_type' => $request->target_type,
                    'send_to' => $request->send_to,
                ]);

                return back()->with('success', 'Push notification sent successfully!');
            } else {
                return back()->with('error', 'Failed to send push notification. Check logs for details.');
            }
        } catch (\Exception $e) {
            Log::error('Push notification error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Send promotion email
     */
    private function sendPromotionEmail(Request $request, ?string $imageUrl): void
    {
        try {
            // Find active template
            $template = EmailTemplate::where('type', 'promotion')
                ->where('is_active', true)
                ->first();

            if (!$template) {
                return;
            }
            
            // Get all active users with email
            $users = User::whereNotNull('email')
                ->where('is_active', true)
                ->where('role', UserRole::CUSTOMER)
                ->get();
            
            // Log count
            Log::info("Found " . $users->count() . " users for promotion email. Dispatching jobs...");

            foreach ($users as $user) {
                // Dispatch Job
                SendPromotionEmailJob::dispatch(
                    $user,
                    $request->title,
                    $request->message,
                    $imageUrl
                )->delay(now()->addSeconds(rand(0, 300))); // Stagger over 5 minutes to avoid huge spikes
            }
            
             Log::info("Dispatched promotion email jobs.");

        } catch (\Exception $e) {
            Log::error("Failed to dispatch promotion emails: " . $e->getMessage());
        }
    }

    /**
     * Build deep link data based on target type
     */
    private function buildDeepLinkData(Request $request): array
    {
        $scheme = strtolower(config('app.name', 'quixko'));
        $data = [
            'type' => 'custom_notification',
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        ];

        switch ($request->target_type) {
            case 'home':
                $data['deep_link'] = "{$scheme}://home";
                $data['screen'] = 'home';
                $data['params'] = json_encode([]);
                break;

            case 'category':
                $category = Category::find($request->target_id);
                if ($category) {
                    $data['deep_link'] = "{$scheme}://category/{$category->id}";
                    $data['screen'] = 'category';
                    $data['params'] = json_encode([
                        'categoryId' => $category->id,
                        'categoryName' => $category->name,
                    ]);
                }
                break;

            case 'store':
                $store = Store::find($request->target_id);
                if ($store) {
                    $data['deep_link'] = "{$scheme}://store/{$store->id}";
                    $data['screen'] = 'store';
                    $data['params'] = json_encode([
                        'storeId' => $store->id,
                        'storeName' => $store->name,
                    ]);
                }
                break;

            case 'product':
                $product = Product::find($request->target_id);
                if ($product) {
                    $data['deep_link'] = "{$scheme}://product/{$product->id}";
                    $data['screen'] = 'product_detail';
                    $data['params'] = json_encode([
                        'productId' => $product->id,
                        'productName' => $product->name,
                    ]);
                }
                break;

            case 'brand':
                $brand = Brand::find($request->target_id);
                if ($brand) {
                    $data['deep_link'] = "{$scheme}://brand/{$brand->id}";
                    $data['screen'] = 'brand';
                    $data['params'] = json_encode([
                        'brandId' => $brand->id,
                        'brandName' => $brand->name,
                    ]);
                }
                break;

            case 'custom':
                $data['deep_link'] = $request->custom_link;
                $data['screen'] = 'custom';
                $data['params'] = json_encode([
                    'url' => $request->custom_link,
                ]);
                break;
        }

        return $data;
    }

}

