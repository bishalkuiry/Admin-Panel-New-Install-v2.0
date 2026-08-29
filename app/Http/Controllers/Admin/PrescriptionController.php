<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Models\Store;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    public function index(Request $request)
    {
        $query = Prescription::with(['user', 'store', 'order'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $prescriptions = $query->paginate(20);
        $stores = Store::where('status', 'approved')->get();

        return view('admin.prescriptions.index', compact('prescriptions', 'stores'));
    }

    public function prepareMedicines(Request $request, Prescription $prescription)
    {
        $validated = $request->validate([
            'medicines' => 'required|array|min:1',
            'medicines.*.name' => 'required|string',
            'medicines.*.quantity' => 'required|integer|min:1',
            'medicines.*.price' => 'required|numeric|min:0',
            'pharmacist_notes' => 'nullable|string',
        ]);

        $total = 0;
        foreach ($validated['medicines'] as $med) {
            $total += ($med['price'] * $med['quantity']);
        }

        $prescription->update([
            'prescribed_medicines' => $validated['medicines'],
            'estimated_price' => $total,
            'pharmacist_notes' => $validated['pharmacist_notes'],
            'status' => 'medicines_added',
        ]);

        return redirect()->back()->with('success', 'Medicines prepared! Sent to customer for price & item approval.');
    }

    public function updateStatus(Request $request, Prescription $prescription)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending_review,medicines_added,customer_approved,rejected,ordered',
        ]);

        $prescription->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', "Prescription status updated to {$validated['status']}");
    }
}
