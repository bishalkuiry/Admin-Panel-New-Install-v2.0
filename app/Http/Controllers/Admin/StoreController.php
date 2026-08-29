<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\Zone;
use App\Models\Category;
use App\Services\StoreService;
use App\Services\StorageService;
use App\Enums\StoreStatus;
use App\Enums\KycStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreController extends Controller
{
    public function __construct(
        private StoreService $storeService,
        private StorageService $storage,
    ) {}

    /**
     * List all stores
     */
    public function index(Request $request)
    {
        $stores = $this->storeService->getStores($request->all());
        $cities = Store::distinct()->pluck('city');
        $zones = Zone::active()->get();
        $pendingCount = Store::pending()->count();

        return view('admin.stores.index', compact('stores', 'cities', 'zones', 'pendingCount'));
    }

    /**
     * Show create store form
     */
    public function create()
    {
        $zones = Zone::active()->get();
        // Allow sellers to have multiple stores - don't filter by existing stores
        $sellers = User::where('role', UserRole::STORE_OWNER)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        return view('admin.stores.create', compact('zones', 'sellers'));
    }

    /**
     * Store new store
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:stores,slug',
            'description' => 'nullable|string',
            'owner_id' => 'required|exists:users,id',
            'logo' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:2048',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'postal_code' => 'required|string|max:10',
            'country' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'zone_ids' => 'nullable|array',
            'zone_ids.*' => 'exists:zones,id',
            'preparation_time' => 'nullable|integer|min:5|max:180',
            'delivery_radius_km' => 'nullable|integer|min:1|max:50',
            'min_order_amount' => 'nullable|numeric|min:0',
            'packing_charge' => 'nullable|numeric|min:0',
            'delivery_type' => 'nullable|in:global,custom,self',
            'delivery_method' => 'nullable|in:flat,percentage,per_km',
            'delivery_flat_rate' => 'nullable|numeric|min:0',
            'delivery_percentage' => 'nullable|numeric|min:0|max:100',
            'delivery_per_km_rate' => 'nullable|numeric|min:0',
            'store_free_delivery' => 'nullable|boolean',
            'commission_percent' => 'nullable|numeric|min:0|max:100',
            'pickup_enabled' => 'boolean',
            'delivery_enabled' => 'boolean',
            'is_featured' => 'boolean',
            'discount_text' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            // Get seller's email and phone
            $seller = User::findOrFail($validated['owner_id']);
            $validated['email'] = $seller->email;
            $validated['phone'] = $seller->phone;
            
            // Handle logo upload
            if ($request->hasFile('logo')) {
                $validated['logo'] = $this->storage->store($request->file('logo'), 'stores/logos');
            }
            
            // Handle banner upload
            if ($request->hasFile('banner')) {
                $validated['banner'] = $this->storage->store($request->file('banner'), 'stores/banners');
            }
            
            // Map address to address_line_1
            $validated['address_line_1'] = $validated['address'];
            unset($validated['address']);

            // Create store
            $store = Store::create(array_merge($validated, [
                'status' => StoreStatus::ACTIVE,
                'kyc_status' => KycStatus::PENDING,
                'is_online' => true,
                'commission_type' => 'percent',
            ]));

            // Assign zones if provided
            if ($request->filled('zone_ids')) {
                $store->zones()->attach($request->zone_ids, [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $store->logActivity('store_created', $request->user(), [
                'notes' => 'Store created by admin'
            ]);

            DB::commit();

            return redirect()
                ->route('admin.stores.show', $store)
                ->with('success', 'Store created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to create store: ' . $e->getMessage());
        }
    }

    /**
     * Show store details
     */
    public function show(Store $store)
    {
        $store->load([
            'owner',
            'zones',
            'staff.user',
            'kycDocuments',
            'commissionRules.category',
            'payouts' => fn($q) => $q->latest()->limit(10),
            'activityLogs' => fn($q) => $q->latest()->limit(20),
        ]);

        $zones = Zone::active()->get();
        $categories = Category::active()->get();

        $reviewsQuery = \App\Models\Review::with(['user', 'order', 'product'])
            ->whereIn('product_id', function ($q) use ($store) {
                $q->select('id')->from('products')->where('store_id', $store->id);
            });

        $totalReviews = (clone $reviewsQuery)->count();
        $avgRating = $totalReviews > 0 ? round((float)(clone $reviewsQuery)->avg('rating'), 1) : 5.0;
        $reviews = $reviewsQuery->latest()->paginate(10);

        return view('admin.stores.show', compact('store', 'zones', 'categories', 'reviews', 'avgRating', 'totalReviews'));
    }

    /**
     * Show edit store form
     */
    public function edit(Store $store)
    {
        $zones = Zone::active()->get();
        $sellers = User::where('role', UserRole::STORE_OWNER)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $assignedZoneIds = $store->zones->pluck('id')->toArray();
        
        return view('admin.stores.edit', compact('store', 'zones', 'sellers', 'assignedZoneIds'));
    }

    /**
     * Update store
     */
    public function update(Request $request, Store $store)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:stores,slug,' . $store->id,
            'description' => 'nullable|string',
            'owner_id' => 'sometimes|exists:users,id',
            'logo' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:2048',
            'email' => 'sometimes|email',
            'phone' => 'sometimes|string|max:20',
            'address' => 'sometimes|string',
            'city' => 'sometimes|string',
            'state' => 'sometimes|string',
            'postal_code' => 'sometimes|string|max:10',
            'country' => 'sometimes|string',
            'latitude' => 'sometimes|numeric|between:-90,90',
            'longitude' => 'sometimes|numeric|between:-180,180',
            'zone_ids' => 'nullable|array',
            'zone_ids.*' => 'exists:zones,id',
            'preparation_time' => 'sometimes|integer|min:5|max:180',
            'delivery_radius_km' => 'sometimes|integer|min:1|max:50',
            'min_order_amount' => 'sometimes|numeric|min:0',
            'packing_charge' => 'sometimes|numeric|min:0',
            'delivery_type' => 'sometimes|in:global,custom,self',
            'delivery_method' => 'nullable|in:flat,percentage,per_km',
            'delivery_flat_rate' => 'nullable|numeric|min:0',
            'delivery_percentage' => 'nullable|numeric|min:0|max:100',
            'delivery_per_km_rate' => 'nullable|numeric|min:0',
            'store_free_delivery' => 'nullable|boolean',
            'commission_percent' => 'sometimes|numeric|min:0|max:100',
            'commission_flat' => 'sometimes|numeric|min:0',
            'commission_type' => 'sometimes|in:percent,flat,category_wise',
            'tax_enabled' => 'sometimes|boolean',
            'tax_percent' => 'sometimes|numeric|min:0|max:100',
            'pickup_enabled' => 'sometimes|boolean',
            'delivery_enabled' => 'sometimes|boolean',
            'is_featured' => 'sometimes|boolean',
            'discount_text' => 'nullable|string|max:50',
            'admin_notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Handle logo upload or selection
            if ($request->hasFile('logo')) {
                // Delete old logo
                if ($store->logo) {
                    $this->storage->delete($store->logo);
                }
                $validated['logo'] = $this->storage->store($request->file('logo'), 'stores/logos');
            } elseif ($request->filled('logo_url')) {
                // Handle media library selection
                // Convert URL to relative path (remove /storage/)
                $path = parse_url($request->logo_url, PHP_URL_PATH);
                $validated['logo'] = ltrim(str_replace('/storage/', '', $path), '/');
            }
            
            // Handle banner upload or selection
            if ($request->hasFile('banner')) {
                // Delete old banner
                if ($store->banner) {
                    $this->storage->delete($store->banner);
                }
                $validated['banner'] = $this->storage->store($request->file('banner'), 'stores/banners');
            } elseif ($request->filled('banner_url')) {
                // Handle media library selection
                $path = parse_url($request->banner_url, PHP_URL_PATH);
                $validated['banner'] = ltrim(str_replace('/storage/', '', $path), '/');
                $path = parse_url($request->banner_url, PHP_URL_PATH);
                $validated['banner'] = ltrim(str_replace('/storage/', '', $path), '/');
            }

            // Map address to address_line_1 if present
            if (isset($validated['address'])) {
                $validated['address_line_1'] = $validated['address'];
                unset($validated['address']);
            }

            // Update store
            $store->update($validated);

            // Update zones if provided
            if ($request->has('zone_ids')) {
                $store->zones()->sync($request->zone_ids);
            }

            $store->logActivity('store_updated', $request->user(), [
                'notes' => 'Store updated by admin'
            ]);

            DB::commit();

            return redirect()
                ->route('admin.stores.show', $store)
                ->with('success', 'Store updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to update store: ' . $e->getMessage());
        }
    }

    /**
     * Approve store & seller account
     */
    public function approve(Request $request, Store $store)
    {
        $store->update([
            'status' => StoreStatus::ACTIVE,
            'kyc_status' => KycStatus::APPROVED,
            'approved_by' => \Illuminate\Support\Facades\Auth::id(),
            'approved_at' => now(),
        ]);

        if ($store->owner) {
            $store->owner->update([
                'is_active' => true,
                'kyc_status' => 'approved',
            ]);

            try {
                \Illuminate\Support\Facades\Mail::to($store->owner->email)->send(new \App\Mail\AccountApprovedMail($store->owner, 'Store Partner'));
            } catch (\Exception $e) {}
        }

        $store->logActivity('store_approved', $request->user());

        return back()->with('success', 'Store partner approved successfully! Confirmation email sent to seller.');
    }

    /**
     * Reject store & seller account
     */
    public function reject(Request $request, Store $store)
    {
        $reason = $request->input('reason', 'Store details or verification documents did not meet criteria.');

        $store->update([
            'status' => StoreStatus::INACTIVE,
            'kyc_status' => KycStatus::REJECTED,
            'rejection_reason' => $reason,
        ]);

        if ($store->owner) {
            $store->owner->update([
                'kyc_status' => 'rejected',
                'kyc_rejection_reason' => $reason,
            ]);

            try {
                \Illuminate\Support\Facades\Mail::to($store->owner->email)->send(new \App\Mail\AccountRejectedMail($store->owner, $reason, 'Store Partner'));
            } catch (\Exception $e) {}
        }

        $store->logActivity('store_rejected', $request->user());

        return back()->with('success', 'Store partner rejected. Notification email sent to seller.');
    }

    /**
     * Suspend store
     */
    public function suspend(Request $request, Store $store)
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $this->storeService->suspend($store, $request->user(), $request->reason);

        return back()->with('success', 'Store suspended');
    }

    /**
     * Reactivate store
     */
    public function reactivate(Request $request, Store $store)
    {
        $store->update(['status' => StoreStatus::ACTIVE]);
        $store->logActivity('store_reactivated', $request->user());

        return back()->with('success', 'Store reactivated');
    }

    /**
     * Assign zones to store
     */
    public function assignZones(Request $request, Store $store)
    {
        $request->validate([
            'zone_ids' => 'required|array',
            'zone_ids.*' => 'exists:zones,id',
        ]);

        $this->storeService->assignZones($store, $request->zone_ids, $request->user());

        return back()->with('success', 'Zones assigned successfully');
    }

    /**
     * Verify KYC document
     */
    public function verifyKyc(Request $request, Store $store, int $documentId)
    {
        $request->validate([
            'approved' => 'required|boolean',
            'reason' => 'required_if:approved,false|nullable|string|max:500',
        ]);

        $document = $store->kycDocuments()->findOrFail($documentId);
        $this->storeService->verifyKycDocument($document, $request->user(), $request->approved, $request->reason);

        return back()->with('success', 'KYC document ' . ($request->approved ? 'approved' : 'rejected'));
    }

    /**
     * Store staff management
     */
    public function staff(Store $store)
    {
        $staff = $store->staff()->with('user')->get();
        return view('admin.stores.staff', compact('store', 'staff'));
    }

    /**
     * Store orders
     */
    public function orders(Store $store)
    {
        $orders = $store->orders()->with(['user', 'items'])->latest()->paginate(20);
        return view('admin.stores.orders', compact('store', 'orders'));
    }

    /**
     * Store products
     */
    public function products(Store $store)
    {
        $products = $store->products()->with('category')->paginate(20);
        return view('admin.stores.products', compact('store', 'products'));
    }

    /**
     * Store payouts
     */
    public function payouts(Store $store)
    {
        $payouts = $store->payouts()->latest()->paginate(20);
        return view('admin.stores.payouts', compact('store', 'payouts'));
    }

    /**
     * Create payout
     */
    public function createPayout(Request $request, Store $store)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:bank_transfer,upi',
            'notes' => 'nullable|string',
        ]);

        $this->storeService->createPayout($store, $request->amount, $request->user(), $request->all());

        return back()->with('success', 'Payout created');
    }

    /**
     * Process payout
     */
    public function processPayout(Request $request, Store $store, int $payoutId)
    {
        $request->validate(['transaction_id' => 'required|string']);

        $payout = $store->payouts()->findOrFail($payoutId);
        $this->storeService->processPayout($payout, $request->user(), $request->transaction_id);

        return back()->with('success', 'Payout processed');
    }

    /**
     * Update commission rules
     */
    public function updateCommission(Request $request, Store $store)
    {
        $request->validate([
            'rules' => 'required|array',
            'rules.*.category_id' => 'nullable|exists:categories,id',
            'rules.*.commission_type' => 'required|in:percent,flat',
            'rules.*.commission_value' => 'required|numeric|min:0',
        ]);

        // Clear existing rules
        $store->commissionRules()->delete();

        // Create new rules
        foreach ($request->rules as $rule) {
            $store->commissionRules()->create($rule);
        }

        $store->logActivity('commission_updated', $request->user());

        return back()->with('success', 'Commission rules updated');
    }

    /**
     * Activity logs
     */
    public function activityLogs(Store $store)
    {
        $logs = $store->activityLogs()->with('user')->latest()->paginate(50);
        return view('admin.stores.activity', compact('store', 'logs'));
    }

    /**
     * Pending approvals queue
     */
    public function pendingApprovals()
    {
        $stores = $this->storeService->getPendingApprovals();
        return view('admin.stores.pending', compact('stores'));
    }

    /**
     * Customer Ratings & Reviews tab
     */
    public function reviews(Store $store)
    {
        $reviewsQuery = \App\Models\Review::whereHas('product', function ($q) use ($store) {
            $q->where('store_id', $store->id);
        })->with(['user', 'product']);

        $totalReviews = (clone $reviewsQuery)->count();
        $avgRating = $totalReviews > 0 ? round((float)(clone $reviewsQuery)->avg('rating'), 1) : 5.0;
        $reviews = $reviewsQuery->latest()->paginate(15);

        return view('admin.stores.reviews', compact('store', 'reviews', 'avgRating', 'totalReviews'));
    }
}
