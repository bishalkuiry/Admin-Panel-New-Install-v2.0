<?php

namespace App\Http\Controllers\Api\V1\Seller;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Store;
use App\Models\Setting;
use App\Services\StoreService;
use App\Services\WalletService;
use App\Enums\UserRole;
use App\Enums\VendorMode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private StoreService $storeService,
        private WalletService $walletService
    ) {}

    /**
     * Register as seller
     */
    public function register(Request $request)
    {
        // Check if multi-vendor mode is enabled
        if (!$this->storeService->isMultiVendor()) {
            return response()->json([
                'success' => false,
                'message' => 'Seller registration is currently disabled.',
            ], 403);
        }

        // Check if seller registration is enabled (default true)
        if (Setting::get('seller_registration', '1') == '0' || Setting::get('seller_registration', '1') === false) {
            return response()->json([
                'success' => false,
                'message' => 'Seller registration is currently closed by admin.',
            ], 403);
        }

        $validated = $request->validate([
            // User details
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:6',
            
            // Store details
            'store_name' => 'required|string|max:255',
            'store_description' => 'nullable|string',
            'business_type' => 'nullable|string',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'delivery_zone_id' => 'nullable|integer',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'store_logo' => 'nullable|file',
            'store_banner' => 'nullable|file',
        ]);

        $existingUser = User::withTrashed()->where('email', $validated['email'])->first();

        if ($existingUser && !$existingUser->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'The email has already been taken.',
                'errors' => ['email' => ['The email has already been taken.']],
            ], 422);
        }

        if ($existingUser && $existingUser->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'This account has been permanently deleted. Please contact support.',
                'error' => 'account_deleted',
            ], 403);
        }

        // Handle Store Logo & Banner File Uploads
        $logoPath = null;
        $bannerPath = null;
        $storage = app(\App\Services\StorageService::class);
        if ($request->hasFile('store_logo')) {
            $logoPath = storage_url($storage->store($request->file('store_logo'), 'stores/logos'));
        }
        if ($request->hasFile('store_banner')) {
            $bannerPath = storage_url($storage->store($request->file('store_banner'), 'stores/banners'));
        }

        // Create seller user in review mode
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => UserRole::STORE_OWNER,
            'is_active' => false,
            'kyc_status' => 'pending',
        ]);

        // Create wallet for the user
        $this->walletService->createWallet($user);

        // Create store entity
        $store = $this->storeService->register([
            'name' => $validated['store_name'],
            'description' => $validated['store_description'] ?? null,
            'business_type' => $validated['business_type'] ?? 'grocery',
            'address' => $validated['address'],
            'city' => $validated['city'],
            'state' => $validated['state'] ?? null,
            'postal_code' => $validated['postal_code'],
            'country' => $validated['country'] ?? 'India',
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'logo' => $logoPath,
            'banner' => $bannerPath,
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'status' => \App\Enums\StoreStatus::PENDING,
            'kyc_status' => \App\Enums\KycStatus::PENDING,
        ], $user);

        // Attach delivery zone if provided
        if (!empty($validated['delivery_zone_id'])) {
            $store->zones()->syncWithoutDetaching([$validated['delivery_zone_id']]);
        }

        // Handle dynamic eKYC data / files for Vendor
        $submittedData = [
            'store_name' => $validated['store_name'],
            'address' => $validated['address'],
            'city' => $validated['city'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
        ];

        if ($request->has('kyc_data') && is_array($request->input('kyc_data'))) {
            $submittedData = array_merge($submittedData, $request->input('kyc_data'));
        }

        $allFiles = $request->allFiles();
        foreach ($allFiles as $fileKey => $fileObj) {
            if ($fileObj && !in_array($fileKey, ['logo', 'banner'])) {
                $cleanKey = str_replace(['kyc_files[', ']'], '', $fileKey);
                $path = $storage->store($fileObj, 'kyc/vendor/' . $user->id);
                $submittedData[$cleanKey] = storage_url($path);
            }
        }

        $kycFields = \App\Models\KycFormField::where('target_role', 'vendor')->get();
        foreach ($kycFields as $field) {
            $key = $field->field_name;
            if ($request->hasFile($key)) {
                $path = $storage->store($request->file($key), 'kyc/vendor/' . $user->id);
                $submittedData[$key] = storage_url($path);
            } else if ($request->has($key)) {
                $val = $request->input($key);
                if (!is_string($val) || (!str_contains($val, '/data/user/') && !str_contains($val, '/cache/'))) {
                    $submittedData[$key] = $val;
                }
            }
        }

        // Filter out raw un-uploaded device paths
        foreach ($submittedData as $k => $v) {
            if (is_string($v) && (str_starts_with($v, '/data/') || str_starts_with($v, '/storage/emulated/') || str_contains($v, '/cache/scaled_'))) {
                unset($submittedData[$k]);
            }
        }

        // Always create KycSubmission for vendor registration
        \App\Models\KycSubmission::updateOrCreate(
            ['user_id' => $user->id, 'role' => 'vendor'],
            [
                'data' => $submittedData,
                'status' => 'pending',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Your account is currently in review. Our team will update you soon.',
            'status' => 'in_review',
            'data' => [
                'user' => new UserResource($user->fresh()),
                'store' => [
                    'id' => $store->id,
                    'name' => $store->name,
                    'status' => $store->status->value,
                ],
            ],
        ], 201);
    }

    /**
     * Seller login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        // Check if user is a store owner
        if ($user->role !== UserRole::STORE_OWNER && !$user->role->isStoreRole()) {
            return response()->json([
                'success' => false,
                'message' => 'This account is not registered as a seller.',
            ], 403);
        }

        $store = $user->getCurrentStore();

        if (!$store) {
            return response()->json([
                'success' => false,
                'message' => 'No store associated with this account.',
            ], 403);
        }

        if (!$user->is_active || $store->status === \App\Enums\StoreStatus::PENDING || $user->kyc_status === 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Your account is currently in review. Our team will update you soon.',
                'error' => 'account_in_review',
            ], 403);
        }

        // Update last login
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        $token = $user->createToken('seller-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => new UserResource($user),
                'store' => [
                    'id' => $store->id,
                    'store_id' => $store->store_id,
                    'name' => $store->name,
                    'status' => $store->status->value,
                    'is_online' => $store->is_online,
                    'kyc_status' => $store->kyc_status->value,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 200, [], JSON_INVALID_UTF8_SUBSTITUTE);
    }

    /**
     * Get seller profile
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        $store = $user->getCurrentStore();

        if (!$store) {
            return response()->json([
                'success' => false,
                'message' => 'No store found',
            ], 404);
        }

        $store->load(['zones', 'kycDocuments']);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => new UserResource($user),
                'store' => $store,
            ],
        ], 200, [], JSON_INVALID_UTF8_SUBSTITUTE);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Get all stores owned by the current user
     */
    public function myStores(Request $request)
    {
        $user = $request->user();
        $stores = $user->ownedStores()->select(['id', 'store_id', 'name', 'status', 'is_online', 'logo'])->get();

        // Also include stores where user is staff
        $staffStores = $user->staffStores()->with(['store' => function ($q) {
            $q->select(['id', 'store_id', 'name', 'status', 'is_online', 'logo']);
        }])->where('is_active', true)->get()->pluck('store')->filter();

        $allStores = $stores->merge($staffStores)->unique('id')->values();

        return response()->json([
            'success' => true,
            'data' => $allStores,
        ]);
    }

    /**
     * Switch to a different store
     */
    public function switchStore(Request $request)
    {
        $request->validate([
            'store_id' => 'required|integer|exists:stores,id',
        ]);

        $user = $request->user();
        $storeId = $request->store_id;

        // Verify the user owns or is staff of this store
        $isOwner = $user->ownedStores()->where('id', $storeId)->exists();
        $isStaff = $user->staffStores()->where('store_id', $storeId)->where('is_active', true)->exists();

        if (!$isOwner && !$isStaff) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this store.',
            ], 403);
        }

        // Save active store selection for API persistence
        $user->update(['active_store_id' => $storeId]);

        // Also update session for hybrid clients
        if ($request->hasSession()) {
            session(['active_store_id' => $storeId]);
        }

        $store = Store::find($storeId);

        return response()->json([
            'success' => true,
            'message' => 'Switched to ' . $store->name,
            'data' => array_merge($store->toArray(), [
                'logo'   => storage_url($store->logo),
                'banner' => storage_url($store->banner),
            ]),
        ], 200, [], JSON_INVALID_UTF8_SUBSTITUTE);
    }

    /**
     * Seller Forgot Password OTP
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email_or_phone' => 'required|string',
        ]);

        $input = $request->email_or_phone;
        $isEmail = filter_var($input, FILTER_VALIDATE_EMAIL);

        $user = User::where($isEmail ? 'email' : 'phone', $input)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No seller account found with this ' . ($isEmail ? 'email address' : 'phone number'),
            ], 404);
        }

        $otpService = app(\App\Services\OtpService::class);
        if ($isEmail) {
            $result = $otpService->sendEmailOtp($input, 'password_reset');
        } else {
            $result = $otpService->sendPhoneOtp($input, 'password_reset');
        }

        return response()->json([
            'success' => $result['success'] ?? true,
            'message' => $result['message'] ?? 'OTP code sent successfully.',
        ]);
    }

    /**
     * Seller Reset Password with OTP
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email_or_phone' => 'required|string',
            'code' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $input = $request->email_or_phone;
        $isEmail = filter_var($input, FILTER_VALIDATE_EMAIL);
        $otpService = app(\App\Services\OtpService::class);

        if ($isEmail) {
            $verifyResult = $otpService->verifyEmailOtp($input, $request->code);
        } else {
            $verifyResult = $otpService->verifyPhoneOtp($input, $request->code);
        }

        if (!$verifyResult['success']) {
            return response()->json([
                'success' => false,
                'message' => $verifyResult['message'] ?? 'Invalid or expired OTP code.',
            ], 400);
        }

        $user = User::where($isEmail ? 'email' : 'phone', $input)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Seller user account not found.',
            ], 404);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully! You can now log into your Seller App with your new password.',
        ]);
    }
}
