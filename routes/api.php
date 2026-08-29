<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\AddressController;
use App\Http\Controllers\Api\V1\RealtimeController;
use App\Http\Controllers\Api\V1\ConfigController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\Seller\AuthController as SellerAuthController;
use App\Http\Controllers\Api\V1\Seller\StoreController as SellerStoreController;
use App\Http\Controllers\Api\V1\Seller\ProductController as SellerProductController;
use App\Http\Controllers\Api\V1\Seller\OrderController as SellerOrderController;
use App\Http\Controllers\Api\V1\Seller\StaffController as SellerStaffController;
use App\Http\Controllers\Api\V1\WalletController;
use App\Http\Controllers\Api\V1\AiController;
use App\Http\Controllers\Api\V1\PointsController;
use App\Http\Controllers\Api\V1\CouponController;
use App\Http\Controllers\Api\V1\ReferralController;

/*
|--------------------------------------------------------------------------
| API Routes - Version 1
|--------------------------------------------------------------------------
|
| Enterprise-grade API with:
| - Rate limiting (10/60/120 requests per minute)
| - Sanctum authentication
| - Versioned endpoints
| - Real-time updates via SSE
| - Multi-vendor seller APIs
|
*/

Route::prefix('v1')->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | Authentication Routes (Rate Limited: 10 requests/minute)
    |--------------------------------------------------------------------------
    */
    Route::middleware('throttle:10,1')->prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        
        // OTP Authentication Routes
        Route::get('/config', [AuthController::class, 'getAuthConfig']);
        Route::post('/otp/send-phone', [AuthController::class, 'sendPhoneOtp']);
        Route::post('/otp/verify-phone', [AuthController::class, 'verifyPhoneOtp']);
        Route::post('/otp/send-email', [AuthController::class, 'sendEmailOtp']);
        Route::post('/otp/verify-email', [AuthController::class, 'verifyEmailOtp']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    });

    /*
    |--------------------------------------------------------------------------
    | Seller Authentication (Rate Limited: 10 requests/minute)
    |--------------------------------------------------------------------------
    */
    Route::middleware('throttle:10,1')->prefix('seller/auth')->group(function () {
        Route::post('/register', [SellerAuthController::class, 'register']);
        Route::post('/login', [SellerAuthController::class, 'login']);
        Route::post('/forgot-password', [SellerAuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [SellerAuthController::class, 'resetPassword']);
    });

    /*
    |--------------------------------------------------------------------------
    | Delivery Partner Authentication (Rate Limited: 10 requests/minute)
    |--------------------------------------------------------------------------
    */
    Route::middleware('throttle:10,1')->prefix('delivery-partner')->group(function () {
        Route::post('/login', [\App\Http\Controllers\Api\V1\DeliveryPartner\DeliveryPartnerController::class, 'login']);
        Route::post('/register', [\App\Http\Controllers\Api\V1\DeliveryPartner\DeliveryPartnerController::class, 'register']);
        Route::post('/forgot-password', [\App\Http\Controllers\Api\V1\DeliveryPartner\DeliveryPartnerController::class, 'forgotPassword']);
        Route::post('/reset-password', [\App\Http\Controllers\Api\V1\DeliveryPartner\DeliveryPartnerController::class, 'resetPassword']);
    });

    /*
    |--------------------------------------------------------------------------
    | eKYC Form Fields & Submission Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('kyc')->group(function () {
        Route::get('/fields', [\App\Http\Controllers\Api\V1\KycApiController::class, 'getFields']);
        Route::middleware('auth:sanctum')->post('/submit', [\App\Http\Controllers\Api\V1\KycApiController::class, 'submit']);
    });

    Route::get('/zones', [\App\Http\Controllers\Api\V1\ZoneApiController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | App Configuration (Public - for mobile app)
    |--------------------------------------------------------------------------
    */
    Route::prefix('config')->group(function () {
        Route::get('/app', [ConfigController::class, 'app']);
        Route::get('/onboarding', [ConfigController::class, 'onboarding']);
        Route::get('/popups', [\App\Http\Controllers\Api\V1\PopupApiController::class, 'index']);
        Route::get('/exchange-rates', [ConfigController::class, 'exchangeRates']);
        Route::get('/home-header', [App\Http\Controllers\Api\V1\HomeHeaderController::class, 'index']);
        Route::get('/app-content', [App\Http\Controllers\Api\V1\AppContentController::class, 'index']);
        Route::get('/pages', [ConfigController::class, 'pages']);
        Route::get('/category-screen-content', [App\Http\Controllers\Api\V1\CategoryScreenContentController::class, 'index']);
    });

    /*
    |--------------------------------------------------------------------------
    | Public Routes (Rate Limited: 60 requests/minute)
    |--------------------------------------------------------------------------
    */
    Route::middleware('throttle:60,1')->group(function () {
        
        // Products
        Route::prefix('products')->group(function () {
            Route::get('/', [ProductController::class, 'index']);
            Route::get('/featured', [ProductController::class, 'featured']);
            Route::get('/search', [ProductController::class, 'search']);
            Route::get('/suggest', [ProductController::class, 'suggest']);
            Route::get('/category/{categoryId}', [ProductController::class, 'byCategory']);
            Route::get('/{product}', [ProductController::class, 'show']);
            Route::get('/{product}/related', [ProductController::class, 'related']);
            Route::get('/{product}/reviews', [ProductController::class, 'reviews']);
            Route::post('/{product}/notify-stock', [ProductController::class, 'notifyStock']);
            Route::middleware('auth:sanctum')->post('/{product}/notify-me', [\App\Http\Controllers\Api\V1\StockNotifyApiController::class, 'toggleNotifyMe']);
            Route::middleware('auth:sanctum')->get('/{product}/notify-status', [\App\Http\Controllers\Api\V1\StockNotifyApiController::class, 'checkStatus']);
        });

        // Categories
        Route::prefix('categories')->group(function () {
            Route::get('/', [CategoryController::class, 'index']);
            Route::get('/featured', [CategoryController::class, 'featured']);
            Route::get('/{category}', [CategoryController::class, 'show']);
        });

        // AI Chat (Publicly accessible, auth handled manually in controller if needed)
        Route::prefix('ai')->group(function () {
            Route::post('/chat', [AiController::class, 'chat']);
        });

        // Coupons & Complaints Categories (public)
        Route::get('/coupons', [CouponController::class, 'index']);
        Route::get('/complaints/categories', [\App\Http\Controllers\Api\V1\CustomerComplaintApiController::class, 'categories']);
    });

    /*
    |--------------------------------------------------------------------------
    | Protected Routes (Rate Limited: 120 requests/minute)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function () {
        
        // Auth
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/logout-all', [AuthController::class, 'logoutAll']);
            Route::get('/user', [AuthController::class, 'user']);
            Route::put('/profile', [AuthController::class, 'updateProfile']);
            Route::delete('/profile', [AuthController::class, 'deleteAccount']);
            Route::put('/password', [AuthController::class, 'changePassword']);
        });

        // FCM Token Management
        Route::prefix('fcm')->group(function () {
            Route::post('/token', [\App\Http\Controllers\Api\V1\FcmTokenController::class, 'store']);
            Route::delete('/token', [\App\Http\Controllers\Api\V1\FcmTokenController::class, 'destroy']);
            Route::post('/test', [\App\Http\Controllers\Api\V1\FcmTokenController::class, 'test']);
        });

        // Cart
        Route::prefix('cart')->group(function () {
            Route::get('/', [CartController::class, 'index']);
            Route::post('/items', [CartController::class, 'addItem']);
            Route::put('/items/{itemId}', [CartController::class, 'updateItem']);
            Route::delete('/items/{itemId}', [CartController::class, 'removeItem']);
            Route::delete('/', [CartController::class, 'clear']);
            Route::post('/coupon', [CartController::class, 'applyCoupon']);
            Route::delete('/coupon', [CartController::class, 'removeCoupon']);
            // Batch sync (guest → authenticated migration) — single round-trip
            Route::post('/sync', [CartController::class, 'sync']);
            // Pre-checkout validation — stock & price drift check
            Route::get('/validate', [CartController::class, 'validate']);
        });

        // Addresses
        Route::prefix('addresses')->group(function () {
            Route::get('/', [AddressController::class, 'index']);
            Route::post('/', [AddressController::class, 'store']);
            Route::get('/{id}', [AddressController::class, 'show']);
            Route::put('/{id}', [AddressController::class, 'update']);
            Route::delete('/{id}', [AddressController::class, 'destroy']);
            Route::post('/{id}/set-default', [AddressController::class, 'setDefault']);
        });

        // Orders
        Route::prefix('orders')->group(function () {
            Route::get('/', [OrderController::class, 'index']);
            Route::post('/', [OrderController::class, 'store']);
            Route::get('/track/{orderNumber}', [OrderController::class, 'track']);
            Route::get('/{order}', [OrderController::class, 'show']);
            Route::post('/{order}/cancel', [OrderController::class, 'cancel']);
            Route::post('/{order}/reorder', [OrderController::class, 'reorder']);
            
            // Delivery Tracking (ETag-based, same as app content)
            Route::get('/{order}/tracking', [\App\Http\Controllers\Api\V1\DeliveryTrackingController::class, 'getTrackingData']);
            Route::get('/{order}/tracking/history', [\App\Http\Controllers\Api\V1\DeliveryTrackingController::class, 'getTrackingHistory']);
            
            // Order Chat
            Route::post('/{order}/chat/init', [\App\Http\Controllers\Api\V1\OrderChatController::class, 'initializeChat']);
            Route::get('/{order}/chat/{chatType}', [\App\Http\Controllers\Api\V1\OrderChatController::class, 'getChat']);
            Route::get('/{order}/chats', [\App\Http\Controllers\Api\V1\OrderChatController::class, 'getOrderChats']);
            Route::post('/{order}/chat/{chatType}/read', [\App\Http\Controllers\Api\V1\OrderChatController::class, 'markAsRead']);
            Route::post('/{order}/chat/{chatType}/notify', [\App\Http\Controllers\Api\V1\OrderChatController::class, 'sendNotification']);

            // Reviews
            Route::get('/{orderId}/reviews', [\App\Http\Controllers\Api\V1\ReviewController::class, 'reviewedProducts']);
            Route::post('/{orderId}/reviews', [\App\Http\Controllers\Api\V1\ReviewController::class, 'store']);
        });

        // Product Returns & Replacements
        Route::prefix('returns')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\V1\ProductReturnApiController::class, 'index']);
            Route::post('/', [\App\Http\Controllers\Api\V1\ProductReturnApiController::class, 'store']);
            Route::get('/{productReturn}', [\App\Http\Controllers\Api\V1\ProductReturnApiController::class, 'show']);
        });

        // Unified Product-Wise Return Requests API
        Route::prefix('customer/returns')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\ReturnRequestApiController::class, 'getCustomerReturns']);
            Route::post('/', [\App\Http\Controllers\Api\ReturnRequestApiController::class, 'submitCustomerReturn']);
        });
        Route::prefix('vendor/returns')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\ReturnRequestApiController::class, 'getVendorReturns']);
            Route::post('/{id}/status', [\App\Http\Controllers\Api\ReturnRequestApiController::class, 'updateVendorReturnStatus']);
        });
        Route::prefix('delivery/returns')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\ReturnRequestApiController::class, 'getDriverReturnPickups']);
            Route::post('/{id}/pickup', [\App\Http\Controllers\Api\ReturnRequestApiController::class, 'updateDriverReturnPickup']);
        });

        // Complaints Management API (User, Store, Driver)
        Route::prefix('complaints')->group(function () {
            Route::get('/categories', [\App\Http\Controllers\Api\V1\CustomerComplaintApiController::class, 'categories']);
            Route::get('/', [\App\Http\Controllers\Api\V1\CustomerComplaintApiController::class, 'index']);
            Route::post('/', [\App\Http\Controllers\Api\V1\CustomerComplaintApiController::class, 'store']);
        });
        Route::get('/seller/complaints', [\App\Http\Controllers\Api\V1\CustomerComplaintApiController::class, 'sellerComplaints']);
        Route::get('/delivery-partner/complaints', [\App\Http\Controllers\Api\V1\CustomerComplaintApiController::class, 'driverComplaints']);

        // Store App POS API
        Route::prefix('store/pos')->group(function () {
            Route::get('/products', [\App\Http\Controllers\Api\V1\StorePosApiController::class, 'getProducts']);
            Route::post('/checkout', [\App\Http\Controllers\Api\V1\StorePosApiController::class, 'checkout']);
        });

        // Customer Prescriptions API
        Route::prefix('prescriptions')->group(function () {
            Route::post('/upload', [\App\Http\Controllers\Api\V1\PrescriptionApiController::class, 'upload']);
            Route::get('/', [\App\Http\Controllers\Api\V1\PrescriptionApiController::class, 'myPrescriptions']);
            Route::post('/{prescription}/approve', [\App\Http\Controllers\Api\V1\PrescriptionApiController::class, 'approve']);
        });

        // AI Image & Voice Search
        Route::prefix('ai-search')->group(function () {
            Route::post('/image', [\App\Http\Controllers\Api\V1\AiSearchApiController::class, 'searchImage']);
            Route::post('/voice', [\App\Http\Controllers\Api\V1\AiSearchApiController::class, 'searchVoice']);
        });
        
        // User Chats
        Route::get('/chats', [\App\Http\Controllers\Api\V1\OrderChatController::class, 'getUserChats']);
        
        // Dedicated Delivery Partner App Routes (Protected)
        Route::prefix('delivery-partner')->group(function () {
             Route::middleware('auth:sanctum')->group(function () {
                Route::get('/profile', [\App\Http\Controllers\Api\V1\DeliveryPartner\DeliveryPartnerController::class, 'profile']);
                Route::put('/profile', [\App\Http\Controllers\Api\V1\DeliveryPartner\DeliveryPartnerController::class, 'updateProfile']);
                Route::post('/logout', [\App\Http\Controllers\Api\V1\DeliveryPartner\DeliveryPartnerController::class, 'logout']);
                Route::post('/change-password', [\App\Http\Controllers\Api\V1\DeliveryPartner\DeliveryPartnerController::class, 'changePassword']);
                Route::post('/avatar', [\App\Http\Controllers\Api\V1\DeliveryPartner\DeliveryPartnerController::class, 'uploadAvatar']);
                Route::get('/dashboard', [\App\Http\Controllers\Api\V1\DeliveryPartner\DeliveryPartnerController::class, 'dashboard']);
                Route::get('/kyc-status', [\App\Http\Controllers\Api\V1\DeliveryPartner\DeliveryPartnerController::class, 'kycStatus']);
                
                // FCM Token Management
                Route::post('/fcm-token', [\App\Http\Controllers\Api\V1\DeliveryPartner\DeliveryPartnerController::class, 'registerFcmToken']);
                Route::delete('/fcm-token', [\App\Http\Controllers\Api\V1\DeliveryPartner\DeliveryPartnerController::class, 'unregisterFcmToken']);

                // Bank Details
                Route::get('/bank-details', [\App\Http\Controllers\Api\V1\DeliveryPartner\DeliveryPartnerController::class, 'getBankDetails']);
                Route::post('/bank-details', [\App\Http\Controllers\Api\V1\DeliveryPartner\DeliveryPartnerController::class, 'saveBankDetails']);

                // Settings (notification preferences)
                Route::get('/settings', [\App\Http\Controllers\Api\V1\DeliveryPartner\DeliveryPartnerController::class, 'getSettings']);
                Route::put('/settings', [\App\Http\Controllers\Api\V1\DeliveryPartner\DeliveryPartnerController::class, 'saveSettings']);

                // Online/Offline status
                Route::post('/status', [\App\Http\Controllers\Api\V1\DeliveryPartner\DeliveryPartnerController::class, 'updateStatus']);
                
                Route::prefix('orders')->group(function () {
                    Route::get('/', [\App\Http\Controllers\Api\V1\DeliveryPartner\OrderController::class, 'index']);
                    Route::get('/{order}', [\App\Http\Controllers\Api\V1\DeliveryPartner\OrderController::class, 'show']);
                    Route::post('/{order}/accept', [\App\Http\Controllers\Api\V1\DeliveryPartner\OrderController::class, 'accept']);
                    Route::post('/{order}/reject', [\App\Http\Controllers\Api\V1\DeliveryPartner\OrderController::class, 'reject']);
                    Route::post('/{order}/cancel', [\App\Http\Controllers\Api\V1\DeliveryPartner\OrderController::class, 'cancel']);
                    Route::post('/{order}/pickup', [\App\Http\Controllers\Api\V1\DeliveryPartner\OrderController::class, 'pickup']);
                    Route::post('/{order}/out-for-delivery', [\App\Http\Controllers\Api\V1\DeliveryPartner\OrderController::class, 'outForDelivery']);
                    Route::post('/{order}/deliver', [\App\Http\Controllers\Api\V1\DeliveryPartner\OrderController::class, 'deliver']);
                    Route::post('/{order}/generate-upi-qr', [\App\Http\Controllers\Api\V1\DeliveryPartner\DeliveryPartnerController::class, 'generateUpiQr']);
                    Route::get('/{order}/check-qr-payment', [\App\Http\Controllers\Api\V1\DeliveryPartner\DeliveryPartnerController::class, 'checkQrPayment']);
                });

                // Financial Tracking & Account Services
                Route::get('/cash-collection', [\App\Http\Controllers\Api\V1\DeliveryPartner\DeliveryPartnerController::class, 'getCashCollection']);
                Route::post('/cash-collection/deposit', [\App\Http\Controllers\Api\V1\DeliveryPartner\DeliveryPartnerController::class, 'depositCash']);
                Route::get('/failed-deliveries', [\App\Http\Controllers\Api\V1\DeliveryPartner\DeliveryPartnerController::class, 'getFailedDeliveries']);
                Route::get('/feedback', [\App\Http\Controllers\Api\V1\DeliveryPartner\DeliveryPartnerController::class, 'getFeedback']);

                Route::prefix('wallet')->group(function () {
                    Route::get('/', [\App\Http\Controllers\Api\V1\WalletController::class, 'getBalance']);
                    Route::get('/transactions', [\App\Http\Controllers\Api\V1\WalletController::class, 'getTransactions']);
                    Route::post('/withdraw', [\App\Http\Controllers\Api\V1\WalletController::class, 'requestWithdrawal']);
                });

                Route::post('/location', [\App\Http\Controllers\Api\V1\DeliveryTrackingController::class, 'updateLocation']);
            });
        });

        // Payment
        Route::prefix('payment')->group(function () {
            Route::get('/methods', [PaymentController::class, 'getPaymentMethods']);
            Route::post('/initialize', [PaymentController::class, 'initializePayment']);
            Route::post('/verify', [PaymentController::class, 'verifyPayment']);
        });
        
        // Wallet
        Route::prefix('wallet')->group(function () {
            Route::get('/', [WalletController::class, 'getBalance']);
            Route::get('/transactions', [WalletController::class, 'getTransactions']);
            Route::post('/top-up', [WalletController::class, 'initiateTopUp']);
            Route::post('/withdrawal', [WalletController::class, 'requestWithdrawal']);
            Route::get('/withdrawals', [WalletController::class, 'getWithdrawals']);
        });

        // Points
        Route::prefix('points')->group(function () {
            Route::get('/', [PointsController::class, 'getBalance']);
            Route::get('/history', [PointsController::class, 'getHistory']);
            Route::post('/redeem', [PointsController::class, 'redeem']);
        });

        // Referral
        Route::prefix('referral')->group(function () {
            Route::get('/', [ReferralController::class, 'getStats']);
            Route::get('/invite-link', [ReferralController::class, 'inviteLink']);
        });

        // Support Tickets
        Route::prefix('support')->group(function () {
            Route::get('/categories', [\App\Http\Controllers\Api\V1\SupportController::class, 'categories']);
            Route::get('/tickets', [\App\Http\Controllers\Api\V1\SupportController::class, 'index']);
            Route::post('/tickets', [\App\Http\Controllers\Api\V1\SupportController::class, 'store']);
            Route::get('/tickets/{ticket}', [\App\Http\Controllers\Api\V1\SupportController::class, 'show']);
            Route::post('/tickets/{ticket}/messages', [\App\Http\Controllers\Api\V1\SupportController::class, 'sendMessage']);
            Route::post('/tickets/{ticket}/chat/init', [\App\Http\Controllers\Api\V1\SupportController::class, 'initChat']);
            Route::post('/tickets/{ticket}/close', [\App\Http\Controllers\Api\V1\SupportController::class, 'close']);
        });

    });

    /*
    |--------------------------------------------------------------------------
    | Payment Webhooks (Public - for gateway callbacks)
    |--------------------------------------------------------------------------
    */
    Route::prefix('payment')->group(function () {
        Route::post('/razorpay/webhook', [PaymentController::class, 'razorpayWebhook']);
        Route::post('/paystack/webhook', [PaymentController::class, 'paystackWebhook']);
        Route::post('/stripe/webhook', [PaymentController::class, 'stripeWebhook']);
        Route::post('/phonepe/callback', [PaymentController::class, 'phonePeCallback']);
        Route::post('/paytm/callback', [PaymentController::class, 'paytmCallback']);
    });

    /*
    |--------------------------------------------------------------------------
    | Wallet Webhooks (Public - for gateway callbacks)
    |--------------------------------------------------------------------------
    */
    Route::prefix('wallet')->group(function () {
        Route::post('/top-up/callback', [WalletController::class, 'topUpCallback']);
    });

    /*
    |--------------------------------------------------------------------------
    | Real-time Updates (Hybrid: Pusher/Redis/SSE)
    |--------------------------------------------------------------------------
    | Auto-detects best available driver
    */
    Route::prefix('realtime')->group(function () {
        Route::get('/stream', [RealtimeController::class, 'stream']);
        Route::get('/events', [RealtimeController::class, 'events']);
        Route::get('/channels', [RealtimeController::class, 'channels']);
        Route::post('/auth', [RealtimeController::class, 'pusherAuth']); // Pusher private channel auth
    });

    /*
    |--------------------------------------------------------------------------
    | Seller Panel Routes (Protected)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'throttle:120,1'])->prefix('seller')->group(function () {
        
        // Auth
        Route::get('/profile', [SellerAuthController::class, 'profile']);
        Route::post('/logout', [SellerAuthController::class, 'logout']);
        Route::get('/stores', [SellerAuthController::class, 'myStores']);
        Route::post('/switch-store', [SellerAuthController::class, 'switchStore']);
        Route::get('/categories', [CategoryController::class, 'index']);
        Route::get('/attributes', [App\Http\Controllers\Api\V1\Seller\AttributeController::class, 'index']);

        // Store Management
        Route::prefix('store')->group(function () {
            Route::get('/', [SellerStoreController::class, 'show']);
            Route::put('/', [SellerStoreController::class, 'update']);
            Route::post('/logo', [SellerStoreController::class, 'uploadLogo']);
            Route::post('/toggle-online', [SellerStoreController::class, 'toggleOnline']);
            Route::get('/stats', [SellerStoreController::class, 'stats']);
            Route::get('/activity', [SellerStoreController::class, 'activityLogs']);
            
            // KYC
            Route::get('/kyc', [SellerStoreController::class, 'kycStatus']);
            Route::post('/kyc', [SellerStoreController::class, 'submitKyc']);
            
            // Payouts
            Route::get('/payouts', [SellerStoreController::class, 'payouts']);
        });

        // Subscriptions & Seller Ad Plans
        Route::get('/subscription/plans', [\App\Http\Controllers\Api\V1\Seller\SellerSubscriptionApiController::class, 'getPlans']);
        Route::post('/subscription/plans/{plan}/subscribe', [\App\Http\Controllers\Api\V1\Seller\SellerSubscriptionApiController::class, 'subscribe']);
        Route::get('/ad-plans', [\App\Http\Controllers\Api\V1\Seller\SellerAdPlanApiController::class, 'index']);
        Route::post('/ad-plans/{plan}/purchase', [\App\Http\Controllers\Api\V1\Seller\SellerAdPlanApiController::class, 'purchase']);

        // Products
        Route::prefix('products')->group(function () {
            Route::get('/', [SellerProductController::class, 'index']);
            Route::post('/', [SellerProductController::class, 'store']);
            Route::get('/low-stock', [SellerProductController::class, 'lowStock']);
            Route::get('/{productId}', [SellerProductController::class, 'show']);
            Route::put('/{productId}', [SellerProductController::class, 'update']);
            Route::delete('/{productId}', [SellerProductController::class, 'destroy']);
            Route::post('/{productId}/stock', [SellerProductController::class, 'updateStock']);
            Route::post('/{productId}/toggle', [SellerProductController::class, 'toggleStatus']);
            Route::post('/{productId}/image', [SellerProductController::class, 'uploadImage']);
            Route::delete('/{productId}/images/{imageId}', [SellerProductController::class, 'deleteImage']);
        });

        // Orders
        Route::prefix('orders')->group(function () {
            Route::get('/', [SellerOrderController::class, 'index']);
            Route::get('/pending-count', [SellerOrderController::class, 'pendingCount']);
            Route::get('/today', [SellerOrderController::class, 'todaySummary']);
            Route::get('/drivers', [SellerOrderController::class, 'availableDrivers']);
            Route::get('/{orderId}', [SellerOrderController::class, 'show']);
            Route::put('/{orderId}/status', [SellerOrderController::class, 'updateStatus']);
            Route::post('/{orderId}/accept', [SellerOrderController::class, 'accept']);
            Route::post('/{orderId}/reject', [SellerOrderController::class, 'reject']);
            Route::post('/{orderId}/assign-driver', [SellerOrderController::class, 'assignDriver']);
            Route::delete('/{orderId}/items/{itemId}', [SellerOrderController::class, 'removeItem']);
            Route::put('/{orderId}/items/{itemId}', [SellerOrderController::class, 'updateItemQuantity']);
        });

        // Staff
        Route::prefix('staff')->group(function () {
            Route::get('/', [SellerStaffController::class, 'index']);
            Route::post('/', [SellerStaffController::class, 'store']);
            Route::put('/{staffId}', [SellerStaffController::class, 'update']);
            Route::delete('/{staffId}', [SellerStaffController::class, 'destroy']);
            Route::post('/{staffId}/toggle', [SellerStaffController::class, 'toggleActive']);
        });

        // Subscriptions
        Route::prefix('subscriptions')->group(function () {
            Route::get('/plans', [\App\Http\Controllers\Api\V1\Seller\SellerSubscriptionApiController::class, 'getPlans']);
            Route::post('/plans/{plan}/subscribe', [\App\Http\Controllers\Api\V1\Seller\SellerSubscriptionApiController::class, 'subscribe']);
        });

        // Advertisements
        Route::prefix('advertisements')->group(function () {
            Route::get('/plans', [\App\Http\Controllers\Api\V1\Seller\SellerAdApiController::class, 'getPlans']);
            Route::post('/plans/{plan}/purchase', [\App\Http\Controllers\Api\V1\Seller\SellerAdApiController::class, 'purchase']);
        });

        // AI System
        Route::prefix('ai')->group(function () {
            Route::post('/generate-product', [AiController::class, 'generateProduct']);
        });
    });

    // User VIP Memberships (Customer) & Route Aliases
    $membershipController = \App\Http\Controllers\Api\V1\UserMembershipApiController::class;
    foreach (['user/membership', 'membership', 'memembership', 'user/memembership'] as $prefix) {
        Route::prefix($prefix)->group(function () use ($membershipController) {
            Route::get('/plans', [$membershipController, 'plans']);
            Route::get('/plan', [$membershipController, 'plans']);
            Route::get('/plab', [$membershipController, 'plans']);
            Route::get('/page', [$membershipController, 'pageLayout']);
            Route::match(['get', 'post'], '/subscribe', [$membershipController, 'subscribe']);
            Route::match(['get', 'post'], '/my-status', [$membershipController, 'myStatus']);
            Route::match(['get', 'post'], '/cancel-auto-renew', [$membershipController, 'cancelAutoRenew']);
            Route::middleware('auth:sanctum')->group(function () use ($membershipController) {
                Route::get('/my-status', [$membershipController, 'myStatus']);
                Route::post('/subscribe', [$membershipController, 'subscribe']);
                Route::post('/cancel-auto-renew', [$membershipController, 'cancelAutoRenew']);
            });
        });
    }

    // Public Dynamic Splash Screen Config Endpoint
    Route::get('splash-screen', [\App\Http\Controllers\Api\V1\SplashScreenApiController::class, 'getSplashConfig']);
});

/*
|--------------------------------------------------------------------------
| Health Check
|--------------------------------------------------------------------------
*/
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => config('app.version', '1.0.0'),
    ]);
});
