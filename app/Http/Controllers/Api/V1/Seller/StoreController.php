<?php

namespace App\Http\Controllers\Api\V1\Seller;

use App\Http\Controllers\Controller;
use App\Services\StoreService;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StoreController extends Controller
{
    public function __construct(
        private StoreService $storeService,
        private StorageService $storage,
    ) {}

    /**
     * Get store details
     */
    public function show(Request $request)
    {
        $store = $request->user()->getCurrentStore();

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Store not found'], 404);
        }

        $store->load(['zones', 'kycDocuments', 'commissionRules.category']);

        return response()->json([
            'success' => true,
            'data' => array_merge($store->toArray(), [
                'logo'   => storage_url($store->logo),
                'banner' => storage_url($store->banner),
            ]),
        ], 200, [], JSON_INVALID_UTF8_SUBSTITUTE);
    }

    /**
     * Update store details
     */
    public function update(Request $request)
    {
        $store = $request->user()->getCurrentStore();

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Store not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'phone' => 'sometimes|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'address' => 'sometimes|string',
            'opening_hours' => 'nullable|array',
            'preparation_time' => 'sometimes|integer|min:5|max:180',
            'pickup_enabled' => 'sometimes|boolean',
            'delivery_enabled' => 'sometimes|boolean',
            'min_order_amount' => 'sometimes|numeric|min:0',
            'packing_charge' => 'sometimes|numeric|min:0',
            'delivery_type' => 'sometimes|string|in:global,custom,self',
            'delivery_method' => 'nullable|string|in:flat,percentage,per_km',
            'delivery_flat_rate' => 'nullable|numeric|min:0',
            'delivery_percentage' => 'nullable|numeric|min:0|max:100',
            'delivery_per_km_rate' => 'nullable|numeric|min:0',
            'store_free_delivery' => 'sometimes|boolean',
        ]);

        // Map 'address' to 'address_line_1' if present, as the DB column is address_line_1
        if (isset($validated['address'])) {
            $validated['address_line_1'] = $validated['address'];
            unset($validated['address']);
        }

        $this->storeService->updateSettings($store, $validated, $request->user());

        $fresh = $store->fresh();
        return response()->json([
            'success' => true,
            'message' => 'Store updated successfully',
            'data' => array_merge($fresh->toArray(), [
                'logo'   => storage_url($fresh->logo),
                'banner' => storage_url($fresh->banner),
            ]),
        ], 200, [], JSON_INVALID_UTF8_SUBSTITUTE);
    }

    /**
     * Upload store logo
     */
    public function uploadLogo(Request $request)
    {
        $store = $request->user()->getCurrentStore();

        $request->validate([
            'logo' => 'required|image|max:2048',
        ]);

        $path = $this->storage->store($request->file('logo'), 'stores/logos');

        // Delete old logo
        if ($store->logo) {
            $this->storage->delete($store->logo);
        }

        $store->update(['logo' => $path]);

        return response()->json([
            'success' => true,
            'data' => ['logo' => $this->storage->url($path)],
        ]);
    }

    /**
     * Toggle online status
     */
    public function toggleOnline(Request $request)
    {
        $store = $request->user()->getCurrentStore();

        if (!$store->status->canAcceptOrders()) {
            return response()->json([
                'success' => false,
                'message' => 'Store must be approved before going online',
            ], 403);
        }

        $this->storeService->toggleOnline($store, $request->user());

        return response()->json([
            'success' => true,
            'message' => $store->fresh()->is_online ? 'Store is now online' : 'Store is now offline',
            'data' => ['is_online' => $store->fresh()->is_online],
        ]);
    }

    /**
     * Get store stats
     */
    public function stats(Request $request)
    {
        $store = $request->user()->getCurrentStore();

        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        $stats = [
            'total_orders' => $store->order_count,
            'total_revenue' => $store->total_revenue,
            'payout_balance' => $store->payout_balance,
            'rating' => $store->rating,
            'rating_count' => $store->rating_count,
            'today_orders' => $store->orders()->whereDate('created_at', $today)->count(),
            'today_revenue' => $store->orders()->whereDate('created_at', $today)->sum('total'),
            'month_orders' => $store->orders()->where('created_at', '>=', $thisMonth)->count(),
            'month_revenue' => $store->orders()->where('created_at', '>=', $thisMonth)->sum('total'),
            'pending_orders' => $store->orders()->where('status', 'pending')->count(),
            'low_stock_products' => $store->products()->whereColumn('quantity', '<=', 'low_stock_threshold')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Submit KYC document
     */
    public function submitKyc(Request $request)
    {
        $store = $request->user()->getCurrentStore();

        $request->validate([
            'document_type' => 'required|string|in:pan_card,gst_certificate,business_proof,owner_id,fssai,trade_license,bank_proof',
            'document_number' => 'nullable|string|max:50',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $path = $this->storage->store($request->file('file'), 'stores/kyc/' . $store->id);

        $document = $this->storeService->submitKycDocument($store, [
            'document_type' => $request->document_type,
            'document_number' => $request->document_number,
            'file_path' => $path,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Document submitted for verification',
            'data' => $document,
        ]);
    }

    /**
     * Get KYC status
     */
    public function kycStatus(Request $request)
    {
        $store = $request->user()->getCurrentStore();
        $documents = $store->kycDocuments;

        $required = ['pan_card', 'gst_certificate', 'owner_id', 'bank_proof'];
        $submitted = $documents->pluck('document_type')->toArray();
        $pending = array_diff($required, $submitted);

        return response()->json([
            'success' => true,
            'data' => [
                'kyc_status' => $store->kyc_status->value,
                'documents' => $documents,
                'required' => $required,
                'pending' => array_values($pending),
            ],
        ]);
    }

    /**
     * Get payouts
     */
    public function payouts(Request $request)
    {
        $store = $request->user()->getCurrentStore();
        $payouts = $store->payouts()->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $payouts,
        ]);
    }

    /**
     * Get activity logs
     */
    public function activityLogs(Request $request)
    {
        $store = $request->user()->getCurrentStore();
        $logs = $store->activityLogs()->with('user')->latest()->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }
}
