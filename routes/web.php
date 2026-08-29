<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\StaticPageController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\StoreController;
use App\Http\Controllers\Admin\ZoneController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\WalletController;
use App\Http\Controllers\Admin\SellerController;
use App\Http\Controllers\Admin\DeliveryPartnerController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\DatabaseCleanController;
use App\Http\Controllers\Admin\PopupController;
// Serve storage files directly (guarantees file availability across all environments)
Route::get('/storage/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);
    if (!file_exists($fullPath) || is_dir($fullPath)) {
        abort(404);
    }
    $mime = @mime_content_type($fullPath) ?: 'image/jpeg';
    return response()->file($fullPath, [
        'Content-Type' => $mime,
        'Cache-Control' => 'public, max-age=31536000, immutable',
    ]);
})->where('path', '.*')->name('storage.serve');

// Landing Page (Website Storefront plugin → Website Builder → Landing Page)
Route::get('/', function () {
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('plugins')) {
            // Priority 1: Full Website Storefront plugin (production-ready)
            $isWebsiteActive = \App\Models\Plugin::where('name', 'website')
                ->where('is_active', true)
                ->exists();
            if ($isWebsiteActive && class_exists(\Plugins\Website\Http\Controllers\WebsiteController::class)) {
                return app(\Plugins\Website\Http\Controllers\WebsiteController::class)->home();
            }

            // Priority 2: Website Builder plugin (basic frontend)
            $isBuilderActive = \App\Models\Plugin::where('name', 'quixko-website-builder')
                ->where('is_active', true)
                ->exists();
            if ($isBuilderActive && class_exists(\Plugins\QuixkoWebsiteBuilder\Controllers\Frontend\WebFrontendController::class)) {
                return app(\Plugins\QuixkoWebsiteBuilder\Controllers\Frontend\WebFrontendController::class)->home(request());
            }
        }
    } catch (\Throwable $e) {
        \Log::warning('Website route check failed: ' . $e->getMessage());
    }

    $content = \App\Models\Setting::get('landing_page_content');
    if ($content) {
        return response($content);
    }
    return view('landing');
})->name('landing');

// Fallback login route for middleware redirection
Route::get('/login-redirect', function() {
    return redirect()->route('admin.login');
})->name('login');

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login.alias');
Route::get('/store/login', [AuthController::class, 'showLogin'])->name('store.login');
Route::get('/seller/login', [AuthController::class, 'showLogin'])->name('seller.login');
Route::get('/employee/login', [AuthController::class, 'showLogin'])->name('employee.login');
Route::get('/staff/login', [AuthController::class, 'showLogin'])->name('staff.login');

Route::post('/login', [AuthController::class, 'login'])->name('admin.login.post')->middleware('throttle:10,1');
Route::post('/store/login', [AuthController::class, 'login'])->name('store.login.post')->middleware('throttle:10,1');
Route::post('/employee/login', [AuthController::class, 'login'])->name('employee.login.post')->middleware('throttle:10,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Shared internal APIs for dynamic UI elements (Admin & Seller)
Route::middleware(['auth'])->group(function () {
    Route::get('/internal-api/categories/roots', [\App\Http\Controllers\Admin\CategoryController::class, 'getRootCategories'])->name('api.categories.roots');
    Route::get('/internal-api/categories/{category}/subcategories', [\App\Http\Controllers\Admin\CategoryController::class, 'getSubcategories'])->name('api.categories.subcategories');
    Route::get('/internal-api/categories/chain/{category}', [\App\Http\Controllers\Admin\CategoryController::class, 'getCategoryChain'])->name('api.categories.chain');
});

// Seller Panel Routes
Route::prefix('seller')->name('seller.')->middleware(['auth', \App\Http\Middleware\EnsureStoreAccess::class])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Seller\DashboardController::class, 'index'])->name('dashboard');

    // Orders
    Route::get('/orders', [\App\Http\Controllers\Seller\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [\App\Http\Controllers\Seller\OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [\App\Http\Controllers\Seller\OrderController::class, 'updateStatus'])->name('orders.update-status');

    // Products
    Route::resource('products', \App\Http\Controllers\Seller\ProductController::class);
    Route::patch('products/{product}/toggle-status', [\App\Http\Controllers\Seller\ProductController::class, 'toggleStatus'])->name('products.toggle-status');

    // Store Settings
    Route::get('/settings', [\App\Http\Controllers\Seller\StoreController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [\App\Http\Controllers\Seller\StoreController::class, 'update'])->name('settings.update');
    Route::post('/switch-store/{store}', [\App\Http\Controllers\Seller\StoreController::class, 'switchStore'])->name('switch-store');

    // Analytics
    Route::get('/analytics', [\App\Http\Controllers\Seller\AnalyticsController::class, 'index'])->name('analytics.index');

    // Payouts
    Route::get('/payouts', [\App\Http\Controllers\Seller\PayoutController::class, 'index'])->name('payouts.index');
    Route::post('/payouts/request', [\App\Http\Controllers\Seller\PayoutController::class, 'requestPayout'])->name('payouts.request');
    Route::get('/payouts/export', [\App\Http\Controllers\Seller\PayoutController::class, 'export'])->name('payouts.export');
    Route::get('/payouts/{payout}', [\App\Http\Controllers\Seller\PayoutController::class, 'show'])->name('payouts.show');

    // Profile
    Route::get('/profile', [\App\Http\Controllers\Seller\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\Seller\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [\App\Http\Controllers\Seller\ProfileController::class, 'updatePassword'])->name('profile.password');

    // Reviews
    Route::get('/reviews', [\App\Http\Controllers\Seller\ReviewController::class, 'index'])->name('reviews.index')->middleware('role:reviews.view');

    // Staff Management
    Route::resource('staff', \App\Http\Controllers\Seller\StaffController::class)->middleware('role:store.staff.view');
    Route::patch('staff/{staff}/toggle-status', [\App\Http\Controllers\Seller\StaffController::class, 'toggleStatus'])->name('staff.toggle-status');

    // Subscriptions & Ads
    Route::get('/subscription', [\App\Http\Controllers\Seller\SubscriptionController::class, 'index'])->name('subscription.index');
    Route::post('/subscription/{plan}/subscribe', [\App\Http\Controllers\Seller\SubscriptionController::class, 'subscribe'])->name('subscription.subscribe');
    Route::get('/advertisements/plans', [\App\Http\Controllers\Seller\AdPlanController::class, 'index'])->name('ad-plans.index');
    Route::post('/advertisements/plans/{plan}/purchase', [\App\Http\Controllers\Seller\AdPlanController::class, 'purchase'])->name('ad-plans.purchase');
});

// Installation complete page (accessible after install)
Route::get('/install-complete', function () {
    return view('installation.step6');
})->name('install.complete');

// Public static pages
Route::get('/page/{slug}', [PageController::class, 'show'])->name('page.show');


Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:dashboard.view'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dispatch', [\App\Http\Controllers\Admin\DispatchController::class, 'index'])->name('dispatch.index');
    Route::get('/dashboard/report', [DashboardController::class, 'downloadReport'])->name('dashboard.report');

    // Module Switcher Route
    Route::post('/switch-module', function (\Illuminate\Http\Request $request) {
        $moduleId = $request->input('module_id');
        if ($moduleId === 'all' || empty($moduleId)) {
            session()->forget('admin_active_module');
        } else {
            session(['admin_active_module' => (int)$moduleId]);
        }
        return back()->with('success', 'Active module updated');
    })->name('switch-module');

    // Food Add-ons Management
    Route::resource('food-addons', \App\Http\Controllers\Admin\FoodAddonController::class);

    // Splash Screen Builder
    Route::get('splash-screen/builder', [\App\Http\Controllers\Admin\SplashScreenController::class, 'index'])->name('splash-builder');
    Route::post('splash-screen/builder', [\App\Http\Controllers\Admin\SplashScreenController::class, 'save'])->name('splash-builder.save');

    // Customer VIP User Memberships (Must be defined before resource('subscriptions'))
    Route::get('subscriptions/user', [\App\Http\Controllers\Admin\MembershipController::class, 'index'])->name('subscriptions.user-plans.index');
    Route::post('subscriptions/user/plans', [\App\Http\Controllers\Admin\MembershipController::class, 'storePlan'])->name('subscriptions.user-plans.store');
    Route::delete('subscriptions/user/plans/{plan}', [\App\Http\Controllers\Admin\MembershipController::class, 'destroyPlan'])->name('subscriptions.user-plans.destroy');
    Route::get('subscriptions/user/builder', [\App\Http\Controllers\Admin\MembershipController::class, 'builder'])->name('subscriptions.user-plans.builder');
    Route::post('subscriptions/user/builder', [\App\Http\Controllers\Admin\MembershipController::class, 'saveBuilder'])->name('subscriptions.user-plans.builder.save');

    // Store Subscription Plans Management
    Route::resource('subscriptions', \App\Http\Controllers\Admin\SubscriptionPlanController::class);

    // Paid Seller Advertisement Plans & Campaign Analytics
    Route::resource('ad-plans', \App\Http\Controllers\Admin\SellerAdPlanController::class);
    Route::get('ad-purchases', [\App\Http\Controllers\Admin\SellerAdPlanController::class, 'purchases'])->name('ad-plans.purchases');
    Route::patch('ad-purchases/{purchase}/status', [\App\Http\Controllers\Admin\SellerAdPlanController::class, 'updatePurchaseStatus'])->name('ad-plans.purchases.update-status');

    // Live Demo Reset Status Check (Real-time Popup Poll)
    Route::get('/demo-reset-status', function () {
        $lastResetAt = \App\Models\Setting::get('demo_last_reset_at');
        $timestamp = $lastResetAt ? strtotime($lastResetAt) : 0;
        return response()->json([
            'success' => true,
            'last_reset_timestamp' => $timestamp
        ]);
    })->name('demo.check-status');

    // Roles & Permissions
    Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class)->middleware('role:roles.view');

    Route::resource('categories', CategoryController::class)->middleware('role:categories.view');
    Route::patch('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status')->middleware('role:categories.update');

    // Category Import/Export
    Route::get('categories-import', [\App\Http\Controllers\Admin\CategoryImportExportController::class, 'index'])->name('categories.import');
    Route::post('categories-import', [\App\Http\Controllers\Admin\CategoryImportExportController::class, 'import'])->name('categories.import.post');
    Route::get('categories-export', [\App\Http\Controllers\Admin\CategoryImportExportController::class, 'export'])->name('categories.export');
    Route::get('categories-sample', [\App\Http\Controllers\Admin\CategoryImportExportController::class, 'downloadSample'])->name('categories.sample');


    Route::get('products/import', [\App\Http\Controllers\Admin\ProductImportExportController::class, 'index'])->name('products.import');
    Route::post('products/import', [\App\Http\Controllers\Admin\ProductImportExportController::class, 'import'])->name('products.import.post');
    Route::get('products/export', [\App\Http\Controllers\Admin\ProductImportExportController::class, 'export'])->name('products.export');
    Route::get('products/sample', [\App\Http\Controllers\Admin\ProductImportExportController::class, 'downloadSample'])->name('products.sample');

    Route::get('products/seller', [ProductController::class, 'sellerProducts'])->name('products.seller')->middleware('role:products.view');

    Route::resource('products', ProductController::class)->middleware('role:products.view');
    Route::patch('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
    Route::patch('products/{product}/toggle-featured', [ProductController::class, 'toggleFeatured'])->name('products.toggle-featured');
    Route::delete('products/{product}/images', [ProductController::class, 'deleteImage'])->name('products.delete-image');
    Route::post('products/{product}/images/delete', [ProductController::class, 'deleteImage'])->name('products.delete-image-post');
    Route::post('products/{product}/images/reorder', [ProductController::class, 'reorderImages'])->name('products.reorder-images');
    Route::post('products/{product}/images/set-primary', [ProductController::class, 'setPrimaryImage'])->name('products.set-primary-image');
    Route::post('products/{product}/clone', [ProductController::class, 'clone'])->name('products.clone')->middleware('role:products.create');

    // Product Returns & Replacements
    Route::get('returns', [\App\Http\Controllers\Admin\ProductReturnController::class, 'index'])->name('returns.index');
    Route::get('returns/{productReturn}', [\App\Http\Controllers\Admin\ProductReturnController::class, 'show'])->name('returns.show');
    Route::post('returns/{productReturn}/status', [\App\Http\Controllers\Admin\ProductReturnController::class, 'updateStatus'])->name('returns.update-status');

    // eKYC Form Builder
    Route::get('kyc', [\App\Http\Controllers\Admin\KycFormBuilderController::class, 'index'])->name('kyc.index');
    Route::post('kyc/fields', [\App\Http\Controllers\Admin\KycFormBuilderController::class, 'storeField'])->name('kyc.fields.store');
    Route::delete('kyc/fields/{field}', [\App\Http\Controllers\Admin\KycFormBuilderController::class, 'destroyField'])->name('kyc.fields.destroy');
    Route::post('kyc/submissions/{submission}/verify', [\App\Http\Controllers\Admin\KycFormBuilderController::class, 'verifySubmission'])->name('kyc.submissions.verify');

    // Customer Complaints
    Route::get('complaints', [\App\Http\Controllers\Admin\CustomerComplaintController::class, 'index'])->name('complaints.index');
    Route::get('complaints/settings', [\App\Http\Controllers\Admin\CustomerComplaintController::class, 'settings'])->name('complaints.settings');
    Route::post('complaints/settings', [\App\Http\Controllers\Admin\CustomerComplaintController::class, 'updateSettings'])->name('complaints.settings.update');
    Route::get('complaints/{complaint}', [\App\Http\Controllers\Admin\CustomerComplaintController::class, 'show'])->name('complaints.show');
    Route::post('complaints/{complaint}/resolve', [\App\Http\Controllers\Admin\CustomerComplaintController::class, 'resolve'])->name('complaints.resolve');

    // Product-Wise Return Requests
    Route::get('returns', [\App\Http\Controllers\Admin\ReturnRequestController::class, 'index'])->name('returns.index');
    Route::post('returns', [\App\Http\Controllers\Admin\ReturnRequestController::class, 'store'])->name('returns.store');
    Route::post('returns/{id}/status', [\App\Http\Controllers\Admin\ReturnRequestController::class, 'updateStatus'])->name('returns.update');

    // Rider Incentives & Customer Tips
    Route::get('incentives', [\App\Http\Controllers\Admin\RiderIncentiveController::class, 'index'])->name('incentives.index');
    Route::post('incentives/rules', [\App\Http\Controllers\Admin\RiderIncentiveController::class, 'storeRule'])->name('incentives.rules.store');
    Route::delete('incentives/rules/{rule}', [\App\Http\Controllers\Admin\RiderIncentiveController::class, 'destroyRule'])->name('incentives.rules.destroy');
    Route::post('incentives/tips', [\App\Http\Controllers\Admin\RiderIncentiveController::class, 'updateTipSettings'])->name('incentives.tips.update');

    // Ratings & Reviews Moderation
    Route::get('reviews', [\App\Http\Controllers\Admin\ReviewModerationController::class, 'index'])->name('reviews.index');
    Route::post('reviews/settings', [\App\Http\Controllers\Admin\ReviewModerationController::class, 'updateSettings'])->name('reviews.settings.update');
    Route::post('reviews/{review}/status', [\App\Http\Controllers\Admin\ReviewModerationController::class, 'updateStatus'])->name('reviews.update-status');
    Route::delete('reviews/{review}', [\App\Http\Controllers\Admin\ReviewModerationController::class, 'destroy'])->name('reviews.destroy');

    // Point of Sale (POS)
    Route::get('pos', [\App\Http\Controllers\Admin\PosController::class, 'index'])->name('pos.index');
    Route::get('pos/search', [\App\Http\Controllers\Admin\PosController::class, 'searchProducts'])->name('pos.search');
    Route::post('pos/checkout', [\App\Http\Controllers\Admin\PosController::class, 'placeOrder'])->name('pos.checkout');
    Route::get('pos/receipt/{order}', [\App\Http\Controllers\Admin\PosController::class, 'receipt'])->name('pos.receipt');

    // Seller POS Routes
    Route::get('seller/pos', [\App\Http\Controllers\Seller\PosController::class, 'index'])->name('seller.pos.index');
    Route::get('seller/pos/search', [\App\Http\Controllers\Seller\PosController::class, 'searchProducts'])->name('seller.pos.search');
    Route::post('seller/pos/checkout', [\App\Http\Controllers\Seller\PosController::class, 'placeOrder'])->name('seller.pos.checkout');

    // Paid Seller Advertisements
    Route::get('ads', [\App\Http\Controllers\Admin\AdvertisementController::class, 'index'])->name('ads.index');
    Route::post('ads', [\App\Http\Controllers\Admin\AdvertisementController::class, 'store'])->name('ads.store');
    Route::post('ads/plans', [\App\Http\Controllers\Admin\AdvertisementController::class, 'storePlan'])->name('ads.plans.store');
    Route::delete('ads/plans/{plan}', [\App\Http\Controllers\Admin\AdvertisementController::class, 'destroyPlan'])->name('ads.plans.destroy');
    Route::post('ads/{advertisement}/status', [\App\Http\Controllers\Admin\AdvertisementController::class, 'updateStatus'])->name('ads.update-status');

    // Seller Advertising Purchase
    Route::get('seller/ads', [\App\Http\Controllers\Seller\AdvertisementController::class, 'index'])->name('seller.ads.index');
    Route::post('seller/ads/buy/{plan}', [\App\Http\Controllers\Seller\AdvertisementController::class, 'buyPlan'])->name('seller.ads.buy');

    // Multi-Language Management & Direction Switch
    Route::get('languages', [\App\Http\Controllers\Admin\LanguageController::class, 'index'])->name('languages.index');
    Route::post('languages', [\App\Http\Controllers\Admin\LanguageController::class, 'store'])->name('languages.store');
    Route::post('languages/{language}/toggle-rtl', [\App\Http\Controllers\Admin\LanguageController::class, 'toggleRtl'])->name('languages.toggle-rtl');
    Route::post('languages/{language}/default', [\App\Http\Controllers\Admin\LanguageController::class, 'setDefault'])->name('languages.default');
    Route::delete('languages/{language}', [\App\Http\Controllers\Admin\LanguageController::class, 'destroy'])->name('languages.destroy');
    Route::get('lang/{code}', [\App\Http\Controllers\Admin\LanguageController::class, 'switchLanguage'])->name('lang.switch');

    // Prescriptions Workflow
    Route::get('prescriptions', [\App\Http\Controllers\Admin\PrescriptionController::class, 'index'])->name('prescriptions.index');
    Route::post('prescriptions/{prescription}/prepare', [\App\Http\Controllers\Admin\PrescriptionController::class, 'prepareMedicines'])->name('prescriptions.prepare');
    Route::post('prescriptions/{prescription}/status', [\App\Http\Controllers\Admin\PrescriptionController::class, 'updateStatus'])->name('prescriptions.update-status');

    // Live Support Chat (Admin <-> Customer / Seller / Driver)
    Route::get('support-chat', [\App\Http\Controllers\Admin\SupportChatController::class, 'index'])->name('support-chat.index');
    Route::post('support-chat/{chat}/send', [\App\Http\Controllers\Admin\SupportChatController::class, 'sendMessage'])->name('support-chat.send');

    // Subscriptions & VIP Memberships
    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        // Store Subscription Plans
        Route::get('store-plans', [\App\Http\Controllers\Admin\SubscriptionPlanController::class, 'index'])->name('store-plans.index');
        Route::get('store-plans/create', [\App\Http\Controllers\Admin\SubscriptionPlanController::class, 'create'])->name('store-plans.create');
        Route::post('store-plans', [\App\Http\Controllers\Admin\SubscriptionPlanController::class, 'store'])->name('store-plans.store');
        Route::get('store-plans/{subscription}/edit', [\App\Http\Controllers\Admin\SubscriptionPlanController::class, 'edit'])->name('store-plans.edit');
        Route::put('store-plans/{subscription}', [\App\Http\Controllers\Admin\SubscriptionPlanController::class, 'update'])->name('store-plans.update');
        Route::delete('store-plans/{subscription}', [\App\Http\Controllers\Admin\SubscriptionPlanController::class, 'destroy'])->name('store-plans.destroy');
    });

    // Admin Global Search
    Route::get('global-search', [\App\Http\Controllers\Admin\GlobalSearchController::class, 'search'])->name('global-search');

    // Advanced Analytical Reports
    Route::get('reports', [\App\Http\Controllers\Admin\ReportsController::class, 'index'])->name('reports.index');
    Route::get('reports/export', [\App\Http\Controllers\Admin\ReportsController::class, 'exportCsv'])->name('reports.export');

    Route::resource('attributes', AttributeController::class)->middleware('role:attributes.view');
    Route::patch('attributes/{attribute}/toggle-filterable', [AttributeController::class, 'toggleFilterable'])->name('attributes.toggle-filterable')->middleware('role:attributes.update');
    Route::patch('attributes/{attribute}/toggle-visible', [AttributeController::class, 'toggleVisible'])->name('attributes.toggle-visible')->middleware('role:attributes.update');

    // Stores (Multi-vendor)
    Route::prefix('stores')->name('stores.')->middleware('role:stores.view')->group(function () {
        Route::get('/', [StoreController::class, 'index'])->name('index');
        Route::get('/create', [StoreController::class, 'create'])->name('create')->middleware('role:stores.create');
        Route::post('/', [StoreController::class, 'store'])->name('store')->middleware('role:stores.create');
        Route::get('/pending', [StoreController::class, 'pendingApprovals'])->name('pending')->middleware('role:stores.approve');
        Route::get('/{store}', [StoreController::class, 'show'])->name('show');
        Route::get('/{store}/edit', [StoreController::class, 'edit'])->name('edit')->middleware('role:stores.update');
        Route::put('/{store}', [StoreController::class, 'update'])->name('update')->middleware('role:stores.update');
        Route::post('/{store}/approve', [StoreController::class, 'approve'])->name('approve')->middleware('role:stores.approve');
        Route::post('/{store}/reject', [StoreController::class, 'reject'])->name('reject')->middleware('role:stores.approve');
        Route::post('/{store}/suspend', [StoreController::class, 'suspend'])->name('suspend')->middleware('role:stores.update');
        Route::post('/{store}/reactivate', [StoreController::class, 'reactivate'])->name('reactivate')->middleware('role:stores.update');
        Route::post('/{store}/zones', [StoreController::class, 'assignZones'])->name('assign-zones');
        Route::post('/{store}/kyc/{documentId}', [StoreController::class, 'verifyKyc'])->name('verify-kyc');
        Route::get('/{store}/staff', [StoreController::class, 'staff'])->name('staff');
        Route::get('/{store}/orders', [StoreController::class, 'orders'])->name('orders');
        Route::get('/{store}/products', [StoreController::class, 'products'])->name('products');
        Route::get('/{store}/payouts', [StoreController::class, 'payouts'])->name('payouts');
        Route::post('/{store}/payouts', [StoreController::class, 'createPayout'])->name('create-payout');
        Route::post('/{store}/payouts/{payoutId}', [StoreController::class, 'processPayout'])->name('process-payout');
        Route::put('/{store}/commission', [StoreController::class, 'updateCommission'])->name('update-commission');
        Route::get('/{store}/activity', [StoreController::class, 'activityLogs'])->name('activity');
        Route::get('/{store}/reviews', [StoreController::class, 'reviews'])->name('reviews');
    });

    // Geocoding (for location search)
    Route::get('/geocoding/search', [\App\Http\Controllers\Admin\GeocodingController::class, 'search'])->name('geocoding.search');
    Route::get('/geocoding/reverse', [\App\Http\Controllers\Admin\GeocodingController::class, 'reverse'])->name('geocoding.reverse');

    // Sellers (Store Owners)
    Route::prefix('sellers')->name('sellers.')->middleware('role:sellers.view')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SellerController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\SellerController::class, 'create'])->name('create')->middleware('role:sellers.create');
        Route::post('/', [\App\Http\Controllers\Admin\SellerController::class, 'store'])->name('store')->middleware('role:sellers.create');
        Route::get('/{seller}', [\App\Http\Controllers\Admin\SellerController::class, 'show'])->name('show');
        Route::put('/{seller}', [\App\Http\Controllers\Admin\SellerController::class, 'update'])->name('update')->middleware('role:sellers.update');
        Route::post('/{seller}/assign-store', [\App\Http\Controllers\Admin\SellerController::class, 'assignStore'])->name('assign-store')->middleware('role:sellers.update');
        Route::post('/{seller}/unassign-store', [\App\Http\Controllers\Admin\SellerController::class, 'unassignStore'])->name('unassign-store')->middleware('role:sellers.update');
        Route::post('/{seller}/toggle-status', [\App\Http\Controllers\Admin\SellerController::class, 'toggleStatus'])->name('toggle-status')->middleware('role:sellers.update');
        Route::delete('/{seller}', [\App\Http\Controllers\Admin\SellerController::class, 'destroy'])->name('destroy')->middleware('role:sellers.delete');
    });

    // Delivery Partners
    Route::prefix('delivery-partners')->name('delivery-partners.')->middleware('role:delivery_partners.view')->group(function () {
        Route::get('/', [DeliveryPartnerController::class, 'index'])->name('index');
        Route::get('/create', [DeliveryPartnerController::class, 'create'])->name('create')->middleware('role:delivery_partners.create');
        Route::post('/', [DeliveryPartnerController::class, 'store'])->name('store')->middleware('role:delivery_partners.create');
        Route::get('/{deliveryPartner}', [DeliveryPartnerController::class, 'show'])->name('show');
        Route::get('/{deliveryPartner}/edit', [DeliveryPartnerController::class, 'edit'])->name('edit')->middleware('role:delivery_partners.update');
        Route::get('/{deliveryPartner}/deliveries', [DeliveryPartnerController::class, 'deliveries'])->name('deliveries');
        Route::get('/{deliveryPartner}/payouts', [DeliveryPartnerController::class, 'payouts'])->name('payouts');
        Route::post('/{deliveryPartner}/payouts', [DeliveryPartnerController::class, 'processPayout'])->name('payouts.process')->middleware('role:delivery_partners.payouts');
        Route::put('/{deliveryPartner}', [DeliveryPartnerController::class, 'update'])->name('update')->middleware('role:delivery_partners.update');
        Route::patch('/{deliveryPartner}/toggle-status', [DeliveryPartnerController::class, 'toggleStatus'])->name('toggle-status')->middleware('role:delivery_partners.update');
        Route::post('/{deliveryPartner}/approve', [DeliveryPartnerController::class, 'approve'])->name('approve');
        Route::post('/{deliveryPartner}/reject', [DeliveryPartnerController::class, 'reject'])->name('reject');
        Route::delete('/{deliveryPartner}', [DeliveryPartnerController::class, 'destroy'])->name('destroy')->middleware('role:delivery_partners.delete');
    });

    // Zones
    Route::get('zones/search', [ZoneController::class, 'search'])->name('zones.search');
    Route::resource('zones', ZoneController::class)->middleware('role:zones.view');
    Route::patch('zones/{zone}/toggle', [ZoneController::class, 'toggleStatus'])->name('zones.toggle');
    Route::post('zones/reorder', [ZoneController::class, 'reorder'])->name('zones.reorder');
    Route::get('zones/{zone}/stores', [ZoneController::class, 'stores'])->name('zones.stores');

    // Orders
    Route::prefix('orders')->name('orders.')->middleware('role:orders.view')->group(function () {
        Route::get('/check-new', [\App\Http\Controllers\Admin\OrderNotificationController::class, 'checkNewOrders'])->name('check-new');
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::patch('/{order}/status', [OrderController::class, 'updateStatus'])->name('update-status');
        Route::post('/{order}/assign-delivery-partner', [OrderController::class, 'assignDeliveryPartner'])->name('assign-delivery-partner');
        Route::delete('/{order}/unassign-delivery-partner', [OrderController::class, 'unassignDeliveryPartner'])->name('unassign-delivery-partner');
        Route::get('/{order}/chat/{chatType}', [\App\Http\Controllers\Admin\OrderChatController::class, 'show'])->name('chat');
        Route::post('/{order}/chat/{chatType}/notify', [\App\Http\Controllers\Admin\OrderChatController::class, 'sendNotification'])->name('chat.notify');
        Route::delete('/{order}/items/{item}', [OrderController::class, 'removeItem'])->name('items.remove');
        Route::post('/{order}/generate-invoice', [\App\Http\Controllers\Admin\InvoiceController::class, 'createFromOrder'])->name('generate-invoice');
        // Real-time status sync
        Route::get('/{order}/stream', [OrderController::class, 'stream'])->name('stream');       // SSE
        Route::get('/{order}/status', [OrderController::class, 'statusCheck'])->name('status'); // Polling fallback
    });

    // Invoices
    Route::prefix('invoices')->name('invoices.')->middleware('role:orders.view')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\InvoiceController::class, 'index'])->name('index');
        Route::post('/settings', [\App\Http\Controllers\Admin\InvoiceController::class, 'updateSettings'])->name('update-settings');
        Route::get('/{invoice}', [\App\Http\Controllers\Admin\InvoiceController::class, 'show'])->name('show');
        Route::get('/{invoice}/edit', [\App\Http\Controllers\Admin\InvoiceController::class, 'edit'])->name('edit');
        Route::put('/{invoice}', [\App\Http\Controllers\Admin\InvoiceController::class, 'update'])->name('update');
        Route::get('/{invoice}/print', [\App\Http\Controllers\Admin\InvoiceController::class, 'print'])->name('print');
        Route::delete('/{invoice}', [\App\Http\Controllers\Admin\InvoiceController::class, 'destroy'])->name('destroy');
    });

    // Users
    Route::resource('users', UserController::class)->middleware('role:users.view');
    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

    // Staff
    Route::resource('staff', \App\Http\Controllers\Admin\StaffController::class)->middleware('role:staff.view');


    // Brands
    Route::resource('brands', BrandController::class)->middleware('role:brands.view');
    Route::patch('brands/{brand}/toggle-status', [BrandController::class, 'toggleStatus'])->name('brands.toggle-status')->middleware('role:brands.update');
    Route::patch('brands/{brand}/toggle-featured', [BrandController::class, 'toggleFeatured'])->name('brands.toggle-featured')->middleware('role:brands.update');

    // Coupons
    Route::resource('coupons', CouponController::class)->middleware('role:coupons.view');
    Route::patch('coupons/{coupon}/toggle-status', [CouponController::class, 'toggleStatus'])->name('coupons.toggle-status')->middleware('role:coupons.update');

    // Reviews
    Route::prefix('reviews')->name('reviews.')->middleware('role:reviews.view')->group(function () {
        Route::get('/', [ReviewController::class, 'index'])->name('index');
        Route::post('/{review}/approve', [ReviewController::class, 'approve'])->name('approve')->middleware('role:reviews.update');
        Route::post('/{review}/reject', [ReviewController::class, 'reject'])->name('reject')->middleware('role:reviews.update');
        Route::delete('/{review}', [ReviewController::class, 'destroy'])->name('destroy')->middleware('role:reviews.delete');
        Route::post('/bulk-approve', [ReviewController::class, 'bulkApprove'])->name('bulk-approve');
    });

    // Static Pages
    Route::middleware('role:pages.view')->group(function () {
        Route::get('/pages', [StaticPageController::class, 'index'])->name('pages.index');
        Route::get('/pages/create', [StaticPageController::class, 'create'])->name('pages.create');
        Route::post('/pages', [StaticPageController::class, 'store'])->name('pages.store');
        Route::get('/pages/{staticPage}/edit', [StaticPageController::class, 'edit'])->name('pages.edit');
        Route::put('/pages/{staticPage}', [StaticPageController::class, 'update'])->name('pages.update');
        Route::delete('/pages/{staticPage}', [StaticPageController::class, 'destroy'])->name('pages.destroy');
        Route::patch('/pages/{staticPage}/toggle', [StaticPageController::class, 'toggleStatus'])->name('pages.toggle');
    });

    // Popups Management
    Route::get('/popups', [PopupController::class, 'index'])->name('popups.index');
    Route::get('/popups/create', [PopupController::class, 'create'])->name('popups.create');
    Route::post('/popups', [PopupController::class, 'store'])->name('popups.store');
    Route::get('/popups/{popup}/edit', [PopupController::class, 'edit'])->name('popups.edit');
    Route::put('/popups/{popup}', [PopupController::class, 'update'])->name('popups.update');
    Route::delete('/popups/{popup}', [PopupController::class, 'destroy'])->name('popups.destroy');
    Route::patch('/popups/{popup}/toggle', [PopupController::class, 'toggleStatus'])->name('popups.toggle');

    // Media Upload
    Route::middleware('role:media.view')->group(function () {
        Route::post('/media/upload', [MediaController::class, 'upload'])->name('media.upload')->middleware('role:media.create');
        Route::get('/media', [MediaController::class, 'index'])->name('media.index');
        Route::delete('/media', [MediaController::class, 'destroy'])->name('media.destroy')->middleware('role:media.delete');
    });

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index')->middleware('role:settings.view');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('/settings/test-broadcast', [SettingsController::class, 'testBroadcast'])->name('settings.test-broadcast');
    Route::get('/settings/test-notification', [SettingsController::class, 'sendTestNotification'])->name('settings.test-notification');
    Route::get('/settings/clear-cache', [SettingsController::class, 'clearCache'])->name('settings.clear-cache');
    Route::post('/settings/storage-link', [SettingsController::class, 'storageLink'])->name('settings.storage-link');
    Route::get('/settings/system-status', [SettingsController::class, 'systemStatus'])->name('settings.system-status');
    Route::get('/settings/web-run-automator', [SettingsController::class, 'webAutomator'])->name('settings.web-run-automator');

    // System Update
    Route::get('/settings/system-update', [SettingsController::class, 'systemUpdate'])->name('settings.system-update')->middleware('role:settings.view');
    Route::post('/settings/system-update', [SettingsController::class, 'processSystemUpdate'])->name('settings.system-update.process');

    // Demo Auto-Reset Settings
    Route::get('/settings/demo', [SettingsController::class, 'demoSettings'])->name('settings.demo');
    Route::post('/settings/demo', [SettingsController::class, 'updateDemoSettings'])->name('settings.demo.update');
    Route::post('/settings/demo/save-baseline', [SettingsController::class, 'saveDemoBaseline'])->name('settings.demo.save-baseline');
    Route::post('/settings/demo/reset-now', [SettingsController::class, 'resetDemoNow'])->name('settings.demo.reset-now');

    // Payment Settings
    Route::get('/settings/payment', [SettingsController::class, 'paymentSettings'])->name('settings.payment');
    Route::put('/settings/payment', [SettingsController::class, 'updatePaymentSettings'])->name('settings.payment.update');

    // Mobile App Settings
    Route::get('/settings/mobile-app', [SettingsController::class, 'mobileApp'])->name('settings.mobile-app');
    Route::put('/settings/mobile-app', [SettingsController::class, 'updateMobileApp'])->name('settings.mobile-app.update');
    Route::post('/settings/firebase-auto-setup', [SettingsController::class, 'firebaseAutoSetup'])->name('settings.firebase-auto-setup');
    Route::post('/settings/firebase-complete-setup', [SettingsController::class, 'firebaseCompleteSetup'])->name('settings.firebase-complete-setup');
    Route::post('/settings/firebase-status', [SettingsController::class, 'firebaseStatus'])->name('settings.firebase-status');
    Route::post('/settings/cloudflare-auto-setup', [SettingsController::class, 'cloudflareAutoSetup'])->name('settings.cloudflare-auto-setup');
    Route::post('/settings/cloudflare-purge-cache', [SettingsController::class, 'cloudflarePurgeCache'])->name('settings.cloudflare-purge-cache');
    Route::get('/settings/onboarding', [SettingsController::class, 'onboardingScreens'])->name('settings.onboarding');
    Route::post('/settings/onboarding', [SettingsController::class, 'saveOnboardingScreens'])->name('settings.onboarding.save');
    Route::post('/settings/update-exchange-rates', [SettingsController::class, 'updateExchangeRates'])->name('settings.update-exchange-rates');
    Route::post('/settings/test-fcm', [\App\Http\Controllers\Api\V1\FcmTokenController::class, 'adminTest'])->name('settings.test-fcm');

    // Auth Settings
    Route::middleware('role:settings.auth')->group(function () {
        Route::get('/settings/auth', [SettingsController::class, 'indexAuth'])->name('settings.auth');
        Route::put('/settings/auth', [SettingsController::class, 'updateAuth'])->name('settings.auth.update');
        Route::post('/settings/auth/test-sms', [SettingsController::class, 'testAuthSms'])->name('settings.auth.test-sms');
    });

    // Scheduler/Cron Jobs
    Route::middleware('role:settings.view')->group(function () {
        Route::resource('scheduler-jobs', \App\Http\Controllers\Admin\SchedulerJobController::class);
        Route::post('scheduler-jobs/{schedulerJob}/toggle', [\App\Http\Controllers\Admin\SchedulerJobController::class, 'toggleStatus'])->name('scheduler-jobs.toggle');
        Route::post('scheduler-jobs/{schedulerJob}/run', [\App\Http\Controllers\Admin\SchedulerJobController::class, 'runJob'])->name('scheduler-jobs.run');
    });

    // General Settings
    Route::get('/settings/general', [SettingsController::class, 'generalSettings'])->name('settings.general');
    Route::put('/settings/general', [SettingsController::class, 'updateGeneralSettings'])->name('settings.general.update');

    // App Settings / Business Settings (Identity, Branding, Support, Maintenance)
    Route::get('/settings/app-settings', [SettingsController::class, 'appSettings'])->name('settings.app-settings');
    Route::put('/settings/app-settings', [SettingsController::class, 'updateAppSettings'])->name('settings.app-settings.update');
    Route::match(['post', 'put'], '/settings/app-settings/test-s3', [SettingsController::class, 'testS3'])->name('settings.s3.test');

    // Website Storefront Manage & SEO Settings (Active website plugin only)
    Route::prefix('website-settings')->name('website-settings.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\WebsiteSettingsController::class, 'index'])->name('index');
        Route::put('/', [\App\Http\Controllers\Admin\WebsiteSettingsController::class, 'update'])->name('update');
        Route::post('/clear-cache', [\App\Http\Controllers\Admin\WebsiteSettingsController::class, 'clearCache'])->name('clear-cache');
    });

    // Email Settings & Templates
    Route::middleware('role:settings.email')->group(function() {
        Route::get('/settings/email', [SettingsController::class, 'emailSettings'])->name('settings.email');
        Route::put('/settings/email', [SettingsController::class, 'updateEmailSettings'])->name('settings.email.update');
        Route::post('/settings/email/auto-configure', [SettingsController::class, 'autoConfigureEmail'])->name('settings.email.auto-configure');
        Route::get('/settings/email/test', [SettingsController::class, 'sendTestEmail'])->name('settings.email.test');

        Route::resource('email-templates', \App\Http\Controllers\Admin\EmailTemplateController::class)->only(['index', 'edit', 'update']);
    });

    // AI Settings
    Route::get('/settings/ai', [SettingsController::class, 'aiSettings'])->name('settings.ai');
    Route::post('/settings/ai', [SettingsController::class, 'updateAiSettings'])->name('settings.ai.update');
    Route::post('/settings/ai/generate-info', [SettingsController::class, 'generateProductInfo'])->name('settings.ai.generate-info');

    // Loyalty Settings
    Route::get('/settings/loyalty', [SettingsController::class, 'loyaltySettings'])->name('settings.loyalty');
    Route::put('/settings/loyalty', [SettingsController::class, 'updateLoyaltySettings'])->name('settings.loyalty.update');

    // Order Flow Settings
    Route::get('/settings/order-flow', [SettingsController::class, 'orderFlowSettings'])->name('settings.order-flow');
    Route::put('/settings/order-flow', [SettingsController::class, 'updateOrderFlowSettings'])->name('settings.order-flow.update');

    // Notification Templates
    Route::get('/settings/notification-templates', [SettingsController::class, 'notificationTemplates'])->name('settings.notification-templates');
    Route::put('/settings/notification-templates', [SettingsController::class, 'updateNotificationTemplates'])->name('settings.notification-templates.update');

    // Loyalty Dashboard/Analytics
    Route::get('/loyalty', [\App\Http\Controllers\Admin\LoyaltyController::class, 'index'])->name('loyalty.index')->middleware('role:settings.view');

    // Push Notifications
    Route::prefix('push-notifications')->name('push-notifications.')->middleware('role:mobile_app.view')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\PushNotificationController::class, 'index'])->name('index');
        Route::post('/send', [\App\Http\Controllers\Admin\PushNotificationController::class, 'send'])->name('send');
        Route::get('/search-products', [\App\Http\Controllers\Admin\PushNotificationController::class, 'searchProducts'])->name('search-products');
    });

    // App Content Management
    Route::prefix('app-content')->name('app-content.')->middleware('role:mobile_app.view')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AppContentController::class, 'index'])->name('index');
        Route::get('/by-tab', [\App\Http\Controllers\Admin\AppContentController::class, 'getByTab'])->name('by-tab');
        Route::post('/', [\App\Http\Controllers\Admin\AppContentController::class, 'store'])->name('store')->middleware('role:mobile_app.manage');
        Route::put('/{id}', [\App\Http\Controllers\Admin\AppContentController::class, 'update'])->name('update')->middleware('role:mobile_app.manage');
        Route::post('/{id}/update', [\App\Http\Controllers\Admin\AppContentController::class, 'update'])->name('update-post')->middleware('role:mobile_app.manage');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\AppContentController::class, 'destroy'])->name('destroy')->middleware('role:mobile_app.manage');
        Route::post('/{id}/delete', [\App\Http\Controllers\Admin\AppContentController::class, 'destroy'])->name('destroy-post')->middleware('role:mobile_app.manage');
        Route::post('/reorder', [\App\Http\Controllers\Admin\AppContentController::class, 'reorder'])->name('reorder')->middleware('role:mobile_app.manage');
        Route::post('/{id}/media', [\App\Http\Controllers\Admin\AppContentController::class, 'uploadMedia'])->name('upload-media')->middleware('role:mobile_app.manage');
        Route::post('/{id}/background-media', [\App\Http\Controllers\Admin\AppContentController::class, 'uploadBackgroundMedia'])->name('upload-background-media')->middleware('role:mobile_app.manage');
        Route::post('/{id}/media-item', [\App\Http\Controllers\Admin\AppContentController::class, 'uploadMediaItem'])->name('upload-media-item')->middleware('role:mobile_app.manage');
        Route::post('/{id}/duplicate', [\App\Http\Controllers\Admin\AppContentController::class, 'duplicate'])->name('duplicate')->middleware('role:mobile_app.manage');
        Route::get('/products', [\App\Http\Controllers\Admin\AppContentController::class, 'availableProducts'])->name('products');
        Route::get('/categories', [\App\Http\Controllers\Admin\AppContentController::class, 'availableCategories'])->name('categories');
        Route::get('/brands', [\App\Http\Controllers\Admin\AppContentController::class, 'availableBrands'])->name('brands');
        Route::get('/stores', [\App\Http\Controllers\Admin\AppContentController::class, 'availableStores'])->name('stores');
        Route::get('/preview-items', [\App\Http\Controllers\Admin\AppContentController::class, 'previewItems'])->name('preview-items');
    });

    // Dedicated Website Content Management (Website Home Builder)
    Route::prefix('website-content')->name('website-content.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\WebsiteContentController::class, 'index'])->name('index');
        Route::get('/by-tab', [\App\Http\Controllers\Admin\WebsiteContentController::class, 'getByTab'])->name('by-tab');
        Route::post('/', [\App\Http\Controllers\Admin\WebsiteContentController::class, 'store'])->name('store');
        Route::put('/{id}', [\App\Http\Controllers\Admin\WebsiteContentController::class, 'update'])->name('update');
        Route::post('/{id}/update', [\App\Http\Controllers\Admin\WebsiteContentController::class, 'update'])->name('update-post');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\WebsiteContentController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/delete', [\App\Http\Controllers\Admin\WebsiteContentController::class, 'destroy'])->name('destroy-post');
        Route::post('/reorder', [\App\Http\Controllers\Admin\WebsiteContentController::class, 'reorder'])->name('reorder');
        Route::post('/{id}/duplicate', [\App\Http\Controllers\Admin\WebsiteContentController::class, 'duplicate'])->name('duplicate');
        Route::post('/{id}/media', [\App\Http\Controllers\Admin\WebsiteContentController::class, 'uploadMedia'])->name('upload-media');
        Route::post('/{id}/background-media', [\App\Http\Controllers\Admin\WebsiteContentController::class, 'uploadBackgroundMedia'])->name('upload-background-media');
        Route::post('/{id}/media-item', [\App\Http\Controllers\Admin\WebsiteContentController::class, 'uploadMediaItem'])->name('upload-media-item');
        Route::get('/products', [\App\Http\Controllers\Admin\WebsiteContentController::class, 'availableProducts'])->name('products');
        Route::get('/categories', [\App\Http\Controllers\Admin\WebsiteContentController::class, 'availableCategories'])->name('categories');
        Route::get('/brands', [\App\Http\Controllers\Admin\WebsiteContentController::class, 'availableBrands'])->name('brands');
        Route::get('/stores', [\App\Http\Controllers\Admin\WebsiteContentController::class, 'availableStores'])->name('stores');
    });

    // Category Screen Content Management (Dedicated category page widgets)
    Route::prefix('category-screen-content')->name('category-screen-content.')->middleware('role:mobile_app.view')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\CategoryScreenContentController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\CategoryScreenContentController::class, 'store'])->name('store')->middleware('role:mobile_app.manage');
        Route::put('/{id}', [\App\Http\Controllers\Admin\CategoryScreenContentController::class, 'update'])->name('update')->middleware('role:mobile_app.manage');
        Route::post('/{id}/update', [\App\Http\Controllers\Admin\CategoryScreenContentController::class, 'update'])->name('update-post')->middleware('role:mobile_app.manage');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\CategoryScreenContentController::class, 'destroy'])->name('destroy')->middleware('role:mobile_app.manage');
        Route::post('/{id}/delete', [\App\Http\Controllers\Admin\CategoryScreenContentController::class, 'destroy'])->name('destroy-post')->middleware('role:mobile_app.manage');
        Route::post('/reorder', [\App\Http\Controllers\Admin\CategoryScreenContentController::class, 'reorder'])->name('reorder')->middleware('role:mobile_app.manage');
        Route::post('/{id}/media', [\App\Http\Controllers\Admin\CategoryScreenContentController::class, 'uploadMedia'])->name('upload-media')->middleware('role:mobile_app.manage');
        Route::post('/{id}/background-media', [\App\Http\Controllers\Admin\CategoryScreenContentController::class, 'uploadBackgroundMedia'])->name('upload-background-media')->middleware('role:mobile_app.manage');
        Route::post('/{id}/media-item', [\App\Http\Controllers\Admin\CategoryScreenContentController::class, 'uploadMediaItem'])->name('upload-media-item')->middleware('role:mobile_app.manage');
        Route::post('/{id}/duplicate', [\App\Http\Controllers\Admin\CategoryScreenContentController::class, 'duplicate'])->name('duplicate')->middleware('role:mobile_app.manage');
        Route::get('/categories', [\App\Http\Controllers\Admin\CategoryScreenContentController::class, 'availableCategories'])->name('categories');
        Route::get('/products', [\App\Http\Controllers\Admin\CategoryScreenContentController::class, 'availableProducts'])->name('products');
        Route::get('/brands', [\App\Http\Controllers\Admin\CategoryScreenContentController::class, 'availableBrands'])->name('brands');
        Route::get('/stores', [\App\Http\Controllers\Admin\CategoryScreenContentController::class, 'availableStores'])->name('stores');
    });

    // Wallet Management
    Route::prefix('wallets')->name('wallets.')->middleware('role:wallets.view')->group(function () {
        Route::get('/', [WalletController::class, 'index'])->name('index');
        Route::get('/export', [WalletController::class, 'export'])->name('export');
        Route::get('/withdrawals', [WalletController::class, 'withdrawals'])->name('withdrawals');
        Route::get('/{wallet}', [WalletController::class, 'show'])->name('show');
        Route::post('/{wallet}/credit', [WalletController::class, 'credit'])->name('credit')->middleware('role:wallets.manage');
        Route::post('/{wallet}/debit', [WalletController::class, 'debit'])->name('debit')->middleware('role:wallets.manage');
        Route::post('/withdrawals/{withdrawal}/approve', [WalletController::class, 'approveWithdrawal'])->name('withdrawals.approve')->middleware('role:wallets.manage');
        Route::post('/withdrawals/{withdrawal}/reject', [WalletController::class, 'rejectWithdrawal'])->name('withdrawals.reject')->middleware('role:wallets.manage');
    });

    // Wallet Settings
    Route::get('/settings/wallet', [WalletController::class, 'settings'])->name('settings.wallet')->middleware('role:wallets.manage');
    Route::put('/settings/wallet', [WalletController::class, 'updateSettings'])->name('settings.wallet.update')->middleware('role:wallets.manage');

    // Home Header Settings (Blinkit-style header)
    Route::prefix('home-header')->name('home-header.')->middleware('role:mobile_app.view')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\HomeHeaderController::class, 'index'])->name('index');
        Route::put('/settings', [\App\Http\Controllers\Admin\HomeHeaderController::class, 'updateSettings'])->name('settings.update')->middleware('role:mobile_app.manage');
        Route::post('/config/save', [\App\Http\Controllers\Admin\HomeHeaderController::class, 'updateSettings'])->name('settings.update-post')->middleware('role:mobile_app.manage');
        Route::get('/categories', [\App\Http\Controllers\Admin\HomeHeaderController::class, 'availableCategories'])->name('categories');
        Route::get('/all-categories', [\App\Http\Controllers\Admin\HomeHeaderController::class, 'allCategories'])->name('all-categories');
        Route::get('/products', [\App\Http\Controllers\Admin\HomeHeaderController::class, 'availableProducts'])->name('products');
        Route::get('/stores', [\App\Http\Controllers\Admin\HomeHeaderController::class, 'availableStores'])->name('stores');

        // Tabs
        Route::get('/tabs/{tabId}/edit', [\App\Http\Controllers\Admin\HomeHeaderController::class, 'editTab'])->name('tabs.edit');
        Route::post('/tabs', [\App\Http\Controllers\Admin\HomeHeaderController::class, 'storeTab'])->name('tabs.store')->middleware('role:mobile_app.manage');
        Route::put('/tabs/{tabId}', [\App\Http\Controllers\Admin\HomeHeaderController::class, 'updateTab'])->name('tabs.update')->middleware('role:mobile_app.manage');
        Route::post('/tabs/{tabId}/update', [\App\Http\Controllers\Admin\HomeHeaderController::class, 'updateTab'])->name('tabs.update-post')->middleware('role:mobile_app.manage');
        Route::delete('/tabs/{tabId}', [\App\Http\Controllers\Admin\HomeHeaderController::class, 'destroyTab'])->name('tabs.destroy')->middleware('role:mobile_app.manage');
        Route::post('/tabs/{tabId}/delete', [\App\Http\Controllers\Admin\HomeHeaderController::class, 'destroyTab'])->name('tabs.destroy-post')->middleware('role:mobile_app.manage');
        Route::post('/tabs/reorder', [\App\Http\Controllers\Admin\HomeHeaderController::class, 'reorderTabs'])->name('tabs.reorder')->middleware('role:mobile_app.manage');
        Route::post('/tabs/{tabId}/background', [\App\Http\Controllers\Admin\HomeHeaderController::class, 'uploadBackground'])->name('tabs.background')->middleware('role:mobile_app.manage');
        Route::post('/tabs/{tabId}/icon', [\App\Http\Controllers\Admin\HomeHeaderController::class, 'uploadIcon'])->name('tabs.icon')->middleware('role:mobile_app.manage');

        // Cards
        Route::post('/tabs/{tabId}/cards', [\App\Http\Controllers\Admin\HomeHeaderController::class, 'storeCard'])->name('cards.store')->middleware('role:mobile_app.manage');
        Route::put('/cards/{cardId}', [\App\Http\Controllers\Admin\HomeHeaderController::class, 'updateCard'])->name('cards.update')->middleware('role:mobile_app.manage');
        Route::post('/cards/{cardId}/update', [\App\Http\Controllers\Admin\HomeHeaderController::class, 'updateCard'])->name('cards.update-post')->middleware('role:mobile_app.manage');
        Route::delete('/cards/{cardId}', [\App\Http\Controllers\Admin\HomeHeaderController::class, 'destroyCard'])->name('cards.destroy')->middleware('role:mobile_app.manage');
        Route::post('/cards/{cardId}/delete', [\App\Http\Controllers\Admin\HomeHeaderController::class, 'destroyCard'])->name('cards.destroy-post')->middleware('role:mobile_app.manage');
        Route::post('/tabs/{tabId}/cards/reorder', [\App\Http\Controllers\Admin\HomeHeaderController::class, 'reorderCards'])->name('cards.reorder')->middleware('role:mobile_app.manage');
        Route::post('/cards/upload-image', [\App\Http\Controllers\Admin\HomeHeaderController::class, 'uploadCardImage'])->name('cards.upload-image')->middleware('role:mobile_app.manage');
        Route::post('/cards/{cardId}/image', [\App\Http\Controllers\Admin\HomeHeaderController::class, 'uploadCardImage'])->name('cards.image')->middleware('role:mobile_app.manage');
    });

    // Landing Page Management
    Route::get('/landing-page', [\App\Http\Controllers\Admin\LandingPageController::class, 'index'])->name('landing-page.index')->middleware('role:landing_page.view');
    Route::post('/landing-page', [\App\Http\Controllers\Admin\LandingPageController::class, 'update'])->name('landing-page.update')->middleware('role:landing_page.manage');

    // Taxes (Customer)
    if (class_exists(\App\Http\Controllers\Admin\TaxController::class)) {
        Route::resource('taxes', \App\Http\Controllers\Admin\TaxController::class)->middleware('role:settings.view');
        Route::patch('taxes/{tax}/toggle', [\App\Http\Controllers\Admin\TaxController::class, 'toggle'])->name('taxes.toggle')->middleware('role:settings.view');
    }

    // Commissions (Store & Partner)
    Route::resource('commissions', \App\Http\Controllers\Admin\CommissionController::class)->middleware('role:settings.view');
    Route::post('commissions/global', [\App\Http\Controllers\Admin\CommissionController::class, 'updateGlobal'])->name('commissions.update-global')->middleware('role:settings.view');
    Route::post('commissions/store-base/{store}', [\App\Http\Controllers\Admin\CommissionController::class, 'updateStoreBase'])->name('commissions.update-store-base')->middleware('role:settings.view');
    Route::patch('commissions/{commission}/toggle', [\App\Http\Controllers\Admin\CommissionController::class, 'toggle'])->name('commissions.toggle')->middleware('role:settings.view');
    Route::get('commissions-stores', [\App\Http\Controllers\Admin\CommissionController::class, 'stores'])->name('commissions.stores');
    Route::get('commissions-stores/{store}/categories', [\App\Http\Controllers\Admin\CommissionController::class, 'storeCategories'])->name('commissions.store-categories');

    // Database Clean
    Route::get('/database-clean', [DatabaseCleanController::class, 'index'])->name('database-clean.index')->middleware('role:database_clean.view');
    Route::post('/database-clean', [DatabaseCleanController::class, 'clean'])->name('database-clean.clean')->middleware('role:database_clean.perform');

    // Service Booking & Ride Sharing Plugin Settings
    Route::prefix('service-booking')->name('service-booking.')->middleware('role:settings.view')->group(function () {
        Route::get('/settings', [\App\Http\Controllers\Admin\ServiceBookingSettingsController::class, 'index'])->name('settings');
        Route::post('/settings', [\App\Http\Controllers\Admin\ServiceBookingSettingsController::class, 'update'])->name('settings.update');
    });

    Route::prefix('ride-sharing')->name('ride-sharing.')->middleware('role:settings.view')->group(function () {
        Route::get('/settings', [\App\Http\Controllers\Admin\RideSharingSettingsController::class, 'index'])->name('settings');
        Route::post('/settings', [\App\Http\Controllers\Admin\RideSharingSettingsController::class, 'update'])->name('settings.update');
    });

    // Plugin Management
    Route::prefix('plugins')->name('plugins.')->middleware('role:settings.view')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\PluginController::class, 'index'])->name('index');
        Route::get('/marketplace-feed', [\App\Http\Controllers\Admin\PluginController::class, 'marketplaceFeed'])->name('marketplace-feed');
        Route::get('/upload', [\App\Http\Controllers\Admin\PluginController::class, 'create'])->name('create')->middleware('role:settings.manage');
        Route::post('/upload', [\App\Http\Controllers\Admin\PluginController::class, 'store'])->name('store')->middleware('role:settings.manage');
        Route::get('/{plugin}', [\App\Http\Controllers\Admin\PluginController::class, 'show'])->name('show');
        Route::get('/{plugin}/settings', [\App\Http\Controllers\Admin\PluginController::class, 'edit'])->name('settings');
        Route::put('/{plugin}/settings', [\App\Http\Controllers\Admin\PluginController::class, 'update'])->name('settings.update')->middleware('role:settings.manage');
        Route::post('/{plugin}/activate', [\App\Http\Controllers\Admin\PluginController::class, 'activate'])->name('activate')->middleware('role:settings.manage');
        Route::post('/{plugin}/deactivate', [\App\Http\Controllers\Admin\PluginController::class, 'deactivate'])->name('deactivate')->middleware('role:settings.manage');
        Route::delete('/{plugin}', [\App\Http\Controllers\Admin\PluginController::class, 'destroy'])->name('destroy')->middleware('role:settings.manage');
        Route::put('/{plugin}/license', [\App\Http\Controllers\Admin\PluginController::class, 'updateLicense'])->name('license.update')->middleware('role:settings.manage');
        Route::post('/sync', [\App\Http\Controllers\Admin\PluginController::class, 'sync'])->name('sync')->middleware('role:settings.manage');
    });

    // Search suggestions API
    Route::get('search/suggestions', [ProductController::class, 'searchSuggestions'])->name('search.suggestions');

    // Security & Audit Center
    Route::prefix('security')->name('security.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SecurityController::class, 'index'])->name('index');
        Route::post('/policies', [\App\Http\Controllers\Admin\SecurityController::class, 'updatePolicies'])->name('update-policies');
        Route::post('/ip/add', [\App\Http\Controllers\Admin\SecurityController::class, 'addIpRule'])->name('add-ip');
        Route::post('/ip/remove', [\App\Http\Controllers\Admin\SecurityController::class, 'removeIpRule'])->name('remove-ip');
    });

    // Topbar live stats
    Route::get('topbar-stats', [\App\Http\Controllers\Admin\TopbarStatsController::class, 'index'])->name('topbar-stats');
});

// Deep Link Verification Files
Route::get('/.well-known/assetlinks.json', function() {
    $path = public_path('.well-known/assetlinks.json');
    if (!file_exists($path)) {
        return response()->json([], 404);
    }
    return response()->file($path, ['Content-Type' => 'application/json']);
});
Route::get('/.well-known/apple-app-site-association', function() {
    $path = public_path('.well-known/apple-app-site-association');
    if (!file_exists($path)) {
        return response()->json([], 404);
    }
    return response()->file($path, ['Content-Type' => 'application/json']);
});

// Product Landing Page (for sharing)
Route::get('/product/{id}', [\App\Http\Controllers\WebProductController::class, 'show'])->name('product.share');
Route::get('/invite/{code}', [\App\Http\Controllers\WebReferralController::class, 'show'])->name('referral.invite');

// Fallback route for static pages (Catch-all for top-level slugs)
Route::get('/{slug}', [PageController::class, 'show'])->name('page.root');

// Support Tickets (appended)
Route::prefix('admin/support')->name('admin.support.')->middleware(['auth', 'role:dashboard.view'])->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\SupportTicketController::class, 'index'])->name('index');
    // ── chat sub-routes MUST come before /{ticket} to avoid being swallowed ──
    Route::get('/{ticket}/chat/init', [\App\Http\Controllers\Admin\SupportTicketController::class, 'chatInit'])->name('chat.init');
    Route::post('/{ticket}/chat/send', [\App\Http\Controllers\Admin\SupportTicketController::class, 'chatSend'])->name('chat.send');
    // ── ticket CRUD ──────────────────────────────────────────────────────────
    Route::get('/{ticket}', [\App\Http\Controllers\Admin\SupportTicketController::class, 'show'])->name('show');
    Route::post('/{ticket}/reply', [\App\Http\Controllers\Admin\SupportTicketController::class, 'reply'])->name('reply');
    Route::post('/{ticket}/assign', [\App\Http\Controllers\Admin\SupportTicketController::class, 'assign'])->name('assign');
    Route::post('/{ticket}/status', [\App\Http\Controllers\Admin\SupportTicketController::class, 'updateStatus'])->name('status');
});
