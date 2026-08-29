<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\KycFormField;
use App\Models\KycSubmission;
use App\Services\StorageService;
use Illuminate\Http\Request;

class KycApiController extends Controller
{
    public function __construct(private StorageService $storage) {}

    /**
     * Get dynamic KYC form fields for a role (vendor / rider)
     */
    public function getFields(Request $request)
    {
        $role = $request->query('role', 'vendor');
        if (!in_array($role, ['vendor', 'rider'])) {
            $role = 'vendor';
        }

        $fields = KycFormField::where('target_role', $role)
            ->orderBy('sort_order', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $fields,
        ]);
    }

    /**
     * Submit dynamic KYC details for authenticated user
     */
    public function submit(Request $request)
    {
        $user = $request->user();
        $role = $request->input('role', $user->hasRole('vendor') ? 'vendor' : 'rider');

        $fields = KycFormField::where('target_role', $role)->get();
        $submittedData = [];

        foreach ($fields as $field) {
            $key = $field->field_name;
            if ($field->field_type === 'file') {
                if ($request->hasFile($key)) {
                    $path = $this->storage->store($request->file($key), 'kyc/' . $role . '/' . $user->id);
                    $submittedData[$key] = storage_url($path);
                }
            } else {
                if ($request->has($key)) {
                    $submittedData[$key] = $request->input($key);
                }
            }
        }

        // Include any raw kyc_data passed as JSON array/map
        if ($request->has('kyc_data') && is_array($request->input('kyc_data'))) {
            $submittedData = array_merge($submittedData, $request->input('kyc_data'));
        }

        $submission = KycSubmission::updateOrCreate(
            ['user_id' => $user->id, 'role' => $role],
            [
                'data' => $submittedData,
                'status' => 'pending',
                'rejection_reason' => null,
            ]
        );

        $user->update(['kyc_status' => 'pending']);

        return response()->json([
            'success' => true,
            'message' => 'eKYC details submitted successfully! Pending verification.',
            'data' => $submission,
        ]);
    }
}
