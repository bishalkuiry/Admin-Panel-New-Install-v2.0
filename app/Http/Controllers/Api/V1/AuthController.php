<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Setting;
use App\Enums\UserRole;
use App\Services\WalletService;
use App\Services\OtpService;
use App\Services\StorageService;
use App\Services\ReferralService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Kreait\Firebase\Factory;

class AuthController extends Controller
{
    public function __construct(
        private WalletService $walletService,
        private ReferralService $referralService,
        private OtpService $otpService,
        private StorageService $storage,
    ) {}

    /**
     * Get authentication configuration for mobile app
     */
    public function getAuthConfig()
    {
        return response()->json([
            'success' => true,
            'data' => $this->otpService->getAuthConfig(),
        ]);
    }

    /**
     * Send OTP to phone number
     */
    public function sendPhoneOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
            'purpose' => 'nullable|in:login,register,verify',
        ]);

        $result = $this->otpService->sendPhoneOtp(
            $request->phone,
            $request->purpose ?? 'login'
        );

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data' => array_filter([
                'provider' => $result['provider'] ?? null,
                'is_firebase' => $result['is_firebase'] ?? false,
                'expires_in_seconds' => $result['expires_in_seconds'] ?? null,
                'wait_seconds' => $result['wait_seconds'] ?? null,
            ]),
        ], $result['success'] ? 200 : 400);
    }

    /**
     * Verify phone OTP and login/register user
     */
    public function verifyPhoneOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
            'code' => 'required_without:firebase_token|string|size:' . Setting::get('auth_otp_length', 6),
            'firebase_token' => 'required_without:code|string',
            'name' => 'nullable|string|max:255',
            'device_name' => 'nullable|string|max:255',
            'referral_code' => 'nullable|string|max:20',
        ]);

        // Handle Firebase token verification
        if ($request->has('firebase_token')) {
            return $this->verifyFirebaseToken($request);
        }

        // Verify OTP via OtpService
        $result = $this->otpService->verifyPhoneOtp($request->phone, $request->code);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'error' => $result['error'] ?? 'verification_failed',
            ], 400);
        }

        [$user, $isNew, $isRestored] = $this->findOrRestoreOrCreateUser(
            'phone',
            $request->phone,
            $request->name,
            null,
            $request->referral_code
        );

        if ($user === null) {
            return response()->json([
                'success' => false,
                'message' => 'This account has been permanently deleted.',
                'error' => 'account_deleted',
            ], 403);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is inactive. Please contact support.',
                'error' => 'account_inactive',
            ], 403);
        }

        // Generate token
        $token = $user->createToken($request->device_name ?? 'auth-token')->plainTextToken;

        $message = $isNew ? 'Phone verified successfully'
            : ($isRestored ? 'Account restored and verified successfully' : 'Login successful');

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
                'is_new_user' => $isNew,
            ],
        ]);
    }

    /**
     * Verify Firebase ID token
     */
    private function verifyFirebaseToken(Request $request)
    {
        try {
            // Resolve service account from the same sources as FirebaseCloudMessagingService:
            // 1. FIREBASE_CREDENTIALS env var (file path)
            // 2. firebase_service_account setting (JSON string)
            // 3. Legacy hardcoded path
            $factory = null;

            $credentialsPath = env('FIREBASE_CREDENTIALS');
            if ($credentialsPath && file_exists($credentialsPath)) {
                $factory = (new Factory)->withServiceAccount($credentialsPath);
            }

            if (!$factory) {
                $serviceAccountJson = \App\Models\Setting::get('firebase_service_account');
                if ($serviceAccountJson) {
                    $serviceAccount = json_decode($serviceAccountJson, true);
                    if ($serviceAccount) {
                        $factory = (new Factory)->withServiceAccount($serviceAccount);
                    }
                }
            }

            if (!$factory) {
                $legacyPath = storage_path('app/firebase/service-account.json');
                if (file_exists($legacyPath)) {
                    $factory = (new Factory)->withServiceAccount($legacyPath);
                }
            }

            if (!$factory) {
                return response()->json([
                    'success' => false,
                    'message' => 'Firebase configuration not found. Please upload a service account JSON in Admin → Settings → Mobile App.',
                    'error' => 'firebase_not_configured',
                ], 500);
            }

            $auth = $factory->createAuth();

            // Verify the ID token
            $verifiedIdToken = $auth->verifyIdToken($request->firebase_token);
            $phone = $verifiedIdToken->claims()->get('phone_number');

            if (!$phone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Phone number not found in Firebase token.',
                    'error' => 'phone_not_found',
                ], 400);
            }

            [$user, $isNew, $isRestored] = $this->findOrRestoreOrCreateUser(
                'phone',
                $phone,
                $request->name,
                null,
                $request->referral_code
            );

            if ($user === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'This account has been permanently deleted.',
                    'error' => 'account_deleted',
                ], 403);
            }

            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is inactive. Please contact support.',
                    'error' => 'account_inactive',
                ], 403);
            }

            // Generate token
            $token = $user->createToken($request->device_name ?? 'auth-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Firebase authentication successful',
                'data' => [
                    'user' => new UserResource($user),
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'is_new_user' => $isNew,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Firebase token verification failed: ' . $e->getMessage(),
                'error' => 'firebase_verification_failed',
            ], 400);
        }
    }

    /**
     * Send OTP to email
     */
    public function sendEmailOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'purpose' => 'nullable|in:login,register,verify',
        ]);

        $result = $this->otpService->sendEmailOtp(
            $request->email,
            $request->purpose ?? 'login'
        );

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data' => array_filter([
                'expires_in_seconds' => $result['expires_in_seconds'] ?? null,
                'wait_seconds' => $result['wait_seconds'] ?? null,
            ]),
        ], $result['success'] ? 200 : 400);
    }

    /**
     * Verify email OTP and login/register user
     */
    public function verifyEmailOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'code' => 'required|string|size:' . Setting::get('auth_otp_length', 6),
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
            'device_name' => 'nullable|string|max:255',
            'referral_code' => 'nullable|string|max:20',
        ]);

        // Verify OTP
        $result = $this->otpService->verifyEmailOtp($request->email, $request->code);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'error' => $result['error'] ?? 'verification_failed',
            ], 400);
        }

        [$user, $isNew, $isRestored] = $this->findOrRestoreOrCreateUser(
            'email',
            $request->email,
            $request->name,
            $request->password,
            $request->referral_code
        );

        if ($user === null) {
            return response()->json([
                'success' => false,
                'message' => 'This account has been permanently deleted.',
                'error' => 'account_deleted',
            ], 403);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is inactive. Please contact support.',
                'error' => 'account_inactive',
            ], 403);
        }

        if ($isNew && $request->filled('phone')) {
            $user->update(['phone' => $request->phone]);
        }

        // Generate token
        $token = $user->createToken($request->device_name ?? 'auth-token')->plainTextToken;

        $message = $isNew ? 'Email verified successfully'
            : ($isRestored ? 'Account restored and verified successfully' : 'Login successful');

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
                'is_new_user' => $isNew,
            ],
        ]);
    }

    /**
     * Register a new user (email/password)
     */
    public function register(Request $request)
    {
        // Check if manual login is enabled
        if (Setting::get('auth_manual_login_enabled', '1') !== '1') {
            return response()->json([
                'success' => false,
                'message' => 'Email/password registration is not enabled.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'referral_code' => 'nullable|string|max:20',
        ]);

        $existingUser = User::withTrashed()->where('email', $validated['email'])->first();

        if ($existingUser) {
            if ($existingUser->trashed()) {
                $gracePeriod = (int) Setting::get('auth_account_deletion_grace_period', 7);
                $daysSinceDeletion = (int) $existingUser->deleted_at->diffInDays(Carbon::now(), false);

                if ($daysSinceDeletion >= 0 && $daysSinceDeletion <= $gracePeriod) {
                    $existingUser->restore();
                    $existingUser->update([
                        'name' => $validated['name'],
                        'password' => Hash::make($validated['password']),
                        'phone' => $validated['phone'] ?? $existingUser->phone,
                    ]);
                    $token = $existingUser->createToken('auth-token')->plainTextToken;
                    return response()->json([
                        'success' => true,
                        'message' => 'Account restored successfully',
                        'data' => [
                            'user' => new UserResource($existingUser),
                            'token' => $token,
                            'token_type' => 'Bearer',
                            'is_new_user' => false,
                        ],
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'This account has been permanently deleted.',
                    'error' => 'account_deleted',
                ], 403);
            }

            // Active user already exists
            return response()->json([
                'success' => false,
                'message' => 'The email has already been taken.',
                'errors' => ['email' => ['The email has already been taken.']],
            ], 422);
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'role' => UserRole::CUSTOMER,
            'is_active' => true,
        ]);

        // Create wallet and apply signup bonus
        $this->walletService->createWallet($user);
        $this->walletService->applySignupBonus($user);

        // Generate user's own referral code
        $this->referralService->generateReferralCode($user);

        // Apply referral code if provided
        if (!empty($validated['referral_code'])) {
            $this->referralService->applyReferralCode($user, $validated['referral_code']);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 201);
    }

    /**
     * Login user (email/password)
     */
    public function login(Request $request)
    {
        // Check if manual login is enabled
        if (Setting::get('auth_manual_login_enabled', '1') !== '1') {
            return response()->json([
                'success' => false,
                'message' => 'Email/password login is not enabled.',
            ], 403);
        }

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'nullable|string|max:255',
        ]);

        $user = User::withTrashed()->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->trashed()) {
            $gracePeriod = (int) Setting::get('auth_account_deletion_grace_period', 7);
            $daysSinceDeletion = (int) $user->deleted_at->diffInDays(Carbon::now(), false);

            if ($daysSinceDeletion >= 0 && $daysSinceDeletion <= $gracePeriod) {
                $user->restore();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'This account has been permanently deleted.',
                    'error' => 'account_deleted',
                ], 403);
            }
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is inactive. Please contact support.',
                'error' => 'account_inactive',
            ], 403);
        }

        $token = $user->createToken($request->device_name ?? 'auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    /**
     * Atomically find, restore (if soft-deleted within grace period), or create a user.
     *
     * Returns [User|null, bool $isNew, bool $isRestored].
     * Returns [null, false, false] when the account is permanently deleted.
     */
    private function findOrRestoreOrCreateUser(
        string $type,
        string $identifier,
        ?string $name,
        ?string $password,
        ?string $referralCode
    ): array {
        $field = $type === 'phone' ? 'phone' : 'email';
        $gracePeriod = (int) Setting::get('auth_account_deletion_grace_period', 7);

        return DB::transaction(function () use (
            $field, $identifier, $type, $name, $password, $referralCode, $gracePeriod
        ) {
            $user = User::withTrashed()->where($field, $identifier)->lockForUpdate()->first();

            if ($user) {
                if ($user->trashed()) {
                    $daysSinceDeletion = (int) $user->deleted_at->diffInDays(Carbon::now(), false);

                    if ($daysSinceDeletion >= 0 && $daysSinceDeletion <= $gracePeriod) {
                        $user->restore();
                        return [$user, false, true];
                    }

                    return [null, false, false];
                }

                return [$user, false, false];
            }

            $userData = [
                $field      => $identifier,
                'name'      => $name ?? $this->otpService->generateDefaultNamePublic($identifier, $type),
                'password'  => $password ? Hash::make($password) : Hash::make(Str::random(32)),
                'role'      => UserRole::CUSTOMER,
                'is_active' => true,
            ];

            if ($type === 'phone') {
                $sanitizedPhone = preg_replace('/\D/', '', $identifier);
                $userData['email'] = 'user_' . $sanitizedPhone . '_' . substr(md5($identifier), 0, 6) . '@phone.local';
            }

            try {
                $user = User::create($userData);
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                $user = User::withTrashed()->where($field, $identifier)->first();

                if ($user && $user->trashed()) {
                    $daysSinceDeletion = (int) $user->deleted_at->diffInDays(Carbon::now(), false);

                    if ($daysSinceDeletion >= 0 && $daysSinceDeletion <= $gracePeriod) {
                        $user->restore();
                        return [$user, false, true];
                    }

                    return [null, false, false];
                }

                return [$user, false, false];
            }

            $this->walletService->createWallet($user);
            $this->walletService->applySignupBonus($user);
            $this->referralService->generateReferralCode($user);

            if ($referralCode) {
                $this->referralService->applyReferralCode($user, $referralCode);
            }

            return [$user, true, false];
        });
    }

    /**
     * Logout user
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
     * Logout from all devices
     */
    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out from all devices',
        ]);
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => new UserResource($request->user()->load('addresses')),
        ]);
    }

    /**
     * Update profile
     */
    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $user = $request->user();

        if ($request->hasFile('avatar')) {
            $path = $this->storage->store($request->file('avatar'), 'avatars');
            $validated['avatar'] = $path;
        }

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully',
        ]);
    }

    /**
     * Delete account (Soft delete)
     */
    public function deleteAccount(Request $request)
    {
        $user = $request->user();
        
        // Revoke tokens
        $user->tokens()->delete();
        
        // Soft delete the user
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Your account has been deactivated and will be permanently deleted after the grace period.',
        ]);
    }

    /**
     * Send OTP for Forgot Password
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
                'message' => 'No account found with this ' . ($isEmail ? 'email address' : 'phone number'),
            ], 404);
        }

        if ($isEmail) {
            $result = $this->otpService->sendEmailOtp($input, 'password_reset');
        } else {
            $result = $this->otpService->sendPhoneOtp($input, 'password_reset');
        }

        return response()->json([
            'success' => $result['success'] ?? true,
            'message' => $result['message'] ?? 'OTP code sent successfully.',
        ]);
    }

    /**
     * Verify OTP and Reset Password
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

        if ($isEmail) {
            $verifyResult = $this->otpService->verifyEmailOtp($input, $request->code);
        } else {
            $verifyResult = $this->otpService->verifyPhoneOtp($input, $request->code);
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
                'message' => 'User account not found.',
            ], 404);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully! You can now log in with your new password.',
        ]);
    }
}
