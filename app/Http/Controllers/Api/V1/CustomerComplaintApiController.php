<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CustomerComplaint;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerComplaintApiController extends Controller
{
    public function index(Request $request)
    {
        $complaints = CustomerComplaint::where('user_id', Auth::id())
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $complaints,
        ]);
    }

    public function categories()
    {
        $raw = \App\Models\Setting::where('key', 'complaint_allowed_categories')->value('value')
            ?? 'Food Quality,Wrong Item,Damaged Packaging,Late Delivery,Rider Behavior,Missing Item';
        $categories = array_map('trim', explode(',', $raw));

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'nullable',
            'category' => 'required|string|max:100',
            'description' => 'required|string|max:1000',
            'attachments' => 'nullable|array',
        ]);

        $orderIdInput = $request->input('order_id');
        $order = null;
        if (!empty($orderIdInput)) {
            $order = Order::where('id', $orderIdInput)
                ->orWhere('order_number', $orderIdInput)
                ->orWhere('order_number', 'like', "%{$orderIdInput}%")
                ->first();
        }

        $attachmentUrls = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = app(\App\Services\StorageService::class)->store($file, 'complaints');
                $attachmentUrls[] = storage_url($path);
            }
        } elseif ($request->filled('attachments')) {
            foreach ((array)$request->input('attachments') as $att) {
                if (is_string($att) && (str_starts_with($att, 'http://') || str_starts_with($att, 'https://') || str_starts_with($att, '/storage'))) {
                    $attachmentUrls[] = str_starts_with($att, '/') ? asset(ltrim($att, '/')) : $att;
                }
            }
        }

        try {
            DB::beginTransaction();

            $complaint = CustomerComplaint::create([
                'user_id' => Auth::id(),
                'order_id' => $order?->id,
                'store_id' => $order?->store_id,
                'driver_id' => $order?->delivery_partner_id,
                'category' => $validated['category'],
                'description' => $validated['description'],
                'attachments' => $attachmentUrls,
                'status' => 'open',
                'action_taken' => 'none',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Complaint filed successfully. Our support team will review your evidence.',
                'data' => $complaint,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API Complaint filing failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to file complaint: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function sellerComplaints(Request $request)
    {
        $seller = Auth::user();
        $storeId = $seller->stores()->first()?->id ?? $seller->active_store_id;

        $complaints = CustomerComplaint::where('store_id', $storeId)
            ->with(['user:id,name,phone', 'order:id,order_number,total_amount,status'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $complaints,
        ]);
    }

    public function driverComplaints(Request $request)
    {
        $driverId = Auth::id();

        $complaints = CustomerComplaint::where('driver_id', $driverId)
            ->with(['user:id,name,phone', 'order:id,order_number,total_amount,status'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $complaints,
        ]);
    }
}
