<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KycFormField;
use App\Models\KycSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KycFormBuilderController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->query('role', 'vendor');

        // Auto-sync missing submission records for existing pending sellers/drivers
        if ($role === 'vendor') {
            $stores = \App\Models\Store::with('owner')->get();
            foreach ($stores as $st) {
                if ($st->owner_id) {
                    \App\Models\KycSubmission::firstOrCreate(
                        ['user_id' => $st->owner_id, 'role' => 'vendor'],
                        [
                            'data' => [
                                'store_name' => $st->name,
                                'address' => $st->address,
                                'city' => $st->city,
                                'phone' => $st->phone,
                                'email' => $st->email,
                            ],
                            'status' => 'pending',
                        ]
                    );
                }
            }
        } else if ($role === 'rider') {
            $riders = \App\Models\User::where('role', \App\Enums\UserRole::DELIVERY_PARTNER)->get();
            foreach ($riders as $rd) {
                \App\Models\KycSubmission::firstOrCreate(
                    ['user_id' => $rd->id, 'role' => 'rider'],
                    [
                        'data' => [
                            'name' => $rd->name,
                            'phone' => $rd->phone,
                            'email' => $rd->email,
                        ],
                        'status' => 'pending',
                    ]
                );
            }
        }

        $fields = KycFormField::where('target_role', $role)->orderBy('sort_order')->get();
        $submissions = KycSubmission::with('user')->where('role', $role)->latest()->paginate(15);

        return view('admin.kyc.index', compact('fields', 'submissions', 'role'));
    }

    public function storeField(Request $request)
    {
        $validated = $request->validate([
            'target_role' => 'required|in:vendor,rider',
            'field_name' => 'required|string|max:100',
            'field_label' => 'required|string|max:255',
            'field_type' => 'required|in:text,number,file,date,select',
            'is_required' => 'boolean',
            'options' => 'nullable|array',
        ]);

        $validated['sort_order'] = KycFormField::where('target_role', $validated['target_role'])->count();

        KycFormField::create($validated);

        return redirect()->back()->with('success', 'eKYC Document field added successfully!');
    }

    public function destroyField(KycFormField $field)
    {
        $field->delete();
        return redirect()->back()->with('success', 'eKYC Document field deleted!');
    }

    public function verifySubmission(Request $request, KycSubmission $submission)
    {
        $validated = $request->validate([
            'status' => 'required|in:verified,rejected',
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $submission->status = $validated['status'];
        if ($validated['status'] === 'verified') {
            $submission->verified_at = now();
            $submission->user->update(['kyc_status' => 'approved']);
        } else {
            $submission->rejection_reason = $validated['rejection_reason'] ?? null;
            $submission->user->update(['kyc_status' => 'rejected']);
        }
        $submission->save();

        return redirect()->back()->with('success', "eKYC Submission #{$submission->id} updated to " . ucfirst($validated['status']));
    }
}
