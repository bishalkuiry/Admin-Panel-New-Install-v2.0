<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Prescription;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrescriptionApiController extends Controller
{
    public function __construct(private StorageService $storage) {}

    public function upload(Request $request)
    {
        $validated = $request->validate([
            'prescription' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'store_id' => 'nullable|exists:stores,id',
        ]);

        $path = $this->storage->store($request->file('prescription'), 'prescriptions');

        $prescription = Prescription::create([
            'user_id' => $request->user()->id,
            'store_id' => $validated['store_id'] ?? null,
            'prescription_file' => $path,
            'status' => 'pending_review',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Prescription uploaded successfully! Pharmacist will review and add medicines.',
            'data' => $prescription,
        ]);
    }

    public function myPrescriptions(Request $request)
    {
        $prescriptions = Prescription::with('store')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $prescriptions,
        ]);
    }

    public function approve(Request $request, Prescription $prescription)
    {
        if ($prescription->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($prescription->status !== 'medicines_added') {
            return response()->json(['success' => false, 'message' => 'Prescription is not ready for approval'], 400);
        }

        try {
            DB::beginTransaction();

            $prescription->status = 'customer_approved';
            $prescription->save();

            // Create Order automatically from approved prescription!
            if ($prescription->store_id) {
                $order = Order::create([
                    'order_number' => 'RX-' . strtoupper(uniqid()),
                    'user_id' => $prescription->user_id,
                    'store_id' => $prescription->store_id,
                    'status' => \App\Enums\OrderStatus::CONFIRMED,
                    'subtotal' => $prescription->estimated_price,
                    'total' => $prescription->estimated_price,
                    'payment_method' => 'cod',
                    'notes' => 'Prescription Order #' . $prescription->id,
                ]);

                if (!empty($prescription->prescribed_medicines)) {
                    foreach ($prescription->prescribed_medicines as $med) {
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_name' => $med['name'],
                            'quantity' => $med['quantity'],
                            'price' => $med['price'],
                            'total' => $med['price'] * $med['quantity'],
                        ]);
                    }
                }

                $prescription->order_id = $order->id;
                $prescription->status = 'ordered';
                $prescription->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Prescription approved and order confirmed!',
                'data' => $prescription,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
