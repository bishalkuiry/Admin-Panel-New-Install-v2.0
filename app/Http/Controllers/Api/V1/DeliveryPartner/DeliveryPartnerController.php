<?php

namespace App\Http\Controllers\Api\V1\DeliveryPartner;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Order;
use App\Models\Setting;
use App\Enums\UserRole;
use App\Enums\OrderStatus;
use App\Services\StorageService;
use App\Services\WalletService;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class DeliveryPartnerController extends Controller
{
    public function __construct(private StorageService $storage) {}
    /**
     * Driver registration
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6',
            'phone' => 'required|string|max:20',
        ]);

        $existingUser = User::withTrashed()->where('email', $request->email)->first();

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

        // Driver accounts start in review (inactive until approved)
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => UserRole::DELIVERY_PARTNER,
            'is_active' => false,
            'kyc_status' => 'pending',
        ]);

        // Create wallet for the new driver
        $walletService = app(WalletService::class);
        $walletService->createWallet($user);

        // Generate referral code
        $referralService = app(ReferralService::class);
        $referralService->generateReferralCode($user);

        // Handle dynamic eKYC submitted data / files
        $submittedData = [];
        if ($request->has('kyc_data') && is_array($request->input('kyc_data'))) {
            $submittedData = $request->input('kyc_data');
        }

        // Process all uploaded files
        $allFiles = $request->allFiles();
        foreach ($allFiles as $fileKey => $fileObj) {
            if ($fileObj) {
                // Strip kyc_files prefix if sent as kyc_files[key]
                $cleanKey = str_replace(['kyc_files[', ']'], '', $fileKey);
                $path = $this->storage->store($fileObj, 'kyc/rider/' . $user->id);
                $submittedData[$cleanKey] = storage_url($path);
            }
        }

        $kycFields = \App\Models\KycFormField::where('target_role', 'rider')->get();
        foreach ($kycFields as $field) {
            $key = $field->field_name;
            if ($request->hasFile($key)) {
                $path = $this->storage->store($request->file($key), 'kyc/rider/' . $user->id);
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

        if (!empty($submittedData)) {
            \App\Models\KycSubmission::updateOrCreate(
                ['user_id' => $user->id, 'role' => 'rider'],
                [
                    'data' => $submittedData,
                    'status' => 'pending',
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Your account is currently in review. Our team will update you soon.',
            'status' => 'in_review',
            'data' => [
                'user' => new UserResource($user),
            ],
        ], 201);
    }

    /**
     * Driver login
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
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->role !== UserRole::DELIVERY_PARTNER) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only delivery partners can login here.',
            ], 403);
        }

        if (!$user->is_active || $user->kyc_status === 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Your account is currently in review. Our team will update you soon.',
                'error' => 'account_in_review',
            ], 403);
        }

        $token = $user->createToken('delivery-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
            ],
        ]);
    }

    /**
     * Get driver profile
     */
    public function profile(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => new UserResource($request->user()),
        ]);
    }

    /**
     * Get dashboard stats
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        
        $todayDeliveries = Order::where('delivery_partner_id', $user->id)
            ->where('status', OrderStatus::DELIVERED)
            ->whereDate('delivered_at', now()->toDateString())
            ->count();

        $confirmBy = Setting::get('order_confirmation_by', 'store');

        // Active deliveries (accepted but not delivered)
        $activeDeliveries = Order::where('delivery_partner_id', $user->id)
            ->whereIn('status', [OrderStatus::CONFIRMED, OrderStatus::PACKED, OrderStatus::PICKED_UP, OrderStatus::OUT_FOR_DELIVERY])
            ->count();

        // Available deliveries based on system configuration
        $availableOrdersQuery = Order::whereNull('delivery_partner_id')
            ->where(function ($q) {
                $q->whereNull('order_type')->orWhere('order_type', '!=', 'pickup');
            });
        
        if (in_array($confirmBy, ['driver', 'delivery_partner'])) {
            $availableOrdersQuery->where('status', OrderStatus::PENDING);
        } else {
            $availableOrdersQuery->whereIn('status', [OrderStatus::CONFIRMED, OrderStatus::PACKED]);
        }
        
        $availableOrders = $availableOrdersQuery->count();

        // Earnings: sum actual delivery_fee from delivered orders today
        $earningsToday = Order::where('delivery_partner_id', $user->id)
            ->where('status', OrderStatus::DELIVERED)
            ->whereDate('delivered_at', now()->toDateString())
            ->sum('delivery_fee');

        return response()->json([
            'success' => true,
            'data' => [
                'available_orders' => $availableOrders,
                'active_deliveries' => $activeDeliveries,
                'completed_today' => $todayDeliveries,
                'earnings_today' => (float) $earningsToday,
                'wallet_balance' => $user->wallet ? (float) $user->wallet->balance : 0.0,
            ]
        ]);
    }

    /**
     * Update profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($request->only('name', 'email', 'phone'));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Get KYC status
     */
    public function kycStatus(Request $request)
    {
        $user = $request->user();

        // kyc_status is stored on the user model (set by admin)
        $kycStatus = $user->kyc_status ?? 'not_submitted';

        // Normalise: if it's an enum instance, get its value
        if ($kycStatus instanceof \App\Enums\KycStatus) {
            $kycStatus = $kycStatus->value;
        }

        // Map status to a human-readable label
        $labelMap = [
            'not_submitted'      => 'Not Submitted',
            'pending'            => 'Pending Review',
            'under_review'       => 'Under Review',
            'approved'           => 'Approved',
            'rejected'           => 'Rejected',
            'resubmit_required'  => 'Resubmit Required',
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'status'  => $kycStatus,
                'label'   => $labelMap[$kycStatus] ?? ucfirst(str_replace('_', ' ', $kycStatus)),
                'is_verified' => $kycStatus === 'approved',
                'rejection_reason' => $kycStatus === 'rejected' ? ($user->kyc_rejection_reason ?? null) : null,
            ]
        ]);
    }

    /**
     * Delivery Partner Forgot Password OTP
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
                'message' => 'No driver account found with this ' . ($isEmail ? 'email address' : 'phone number'),
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
     * Delivery Partner Reset Password with OTP
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
                'message' => 'Driver user account not found.',
            ], 404);
        }

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully! You can now log into your Driver App with your new password.',
        ]);
    }

    /**
     * Logout delivery partner (revoke current token)
     */
    public function logout(Request $request)
    {
        // Revoke the current access token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->old_password, $user->password)) {
            throw ValidationException::withMessages([
                'old_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully',
        ]);
    }

    /**
     * Upload avatar image
     */
    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = $request->user();

        // Delete old avatar if exists
        if ($user->avatar) {
            $this->storage->delete($user->avatar);
        }

        // Store new avatar
        $path = $this->storage->store($request->file('avatar'), 'avatars');

        $user->update(['avatar' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'Avatar uploaded successfully',
            'data' => [
                'avatar_url' => storage_url($path),
            ],
        ]);
    }

    /**
     * Register FCM token for push notifications
     */
    public function registerFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user = $request->user();
        $user->fcm_token = $request->fcm_token;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'FCM token registered successfully',
        ]);
    }

    /**
     * Update driver online/offline status
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'is_online' => 'required|boolean',
        ]);

        $user = $request->user();
        $user->is_active = $request->boolean('is_online');
        $user->save();

        return response()->json([
            'success' => true,
            'data' => ['is_online' => $user->is_active],
        ]);
    }

    /**
     * Get notification/app settings
     */
    public function getSettings(Request $request)
    {
        $user = $request->user();

        // Settings are stored as JSON in user metadata or a dedicated column.
        // We use a simple JSON column approach via the user's permissions/metadata.
        // Fall back to sensible defaults if not set.
        $settings = $user->notification_settings ?? [];

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => [
                    'new_orders'       => $settings['new_orders']       ?? true,
                    'order_updates'    => $settings['order_updates']    ?? true,
                    'payment_alerts'   => $settings['payment_alerts']   ?? true,
                    'chat_messages'    => $settings['chat_messages']    ?? true,
                    'promotions'       => $settings['promotions']       ?? false,
                    'sound_enabled'    => $settings['sound_enabled']    ?? true,
                    'vibration_enabled'=> $settings['vibration_enabled']?? true,
                ],
            ],
        ]);
    }

    /**
     * Save notification/app settings
     */
    public function saveSettings(Request $request)
    {
        $request->validate([
            'notifications'                      => 'required|array',
            'notifications.new_orders'           => 'boolean',
            'notifications.order_updates'        => 'boolean',
            'notifications.payment_alerts'       => 'boolean',
            'notifications.chat_messages'        => 'boolean',
            'notifications.promotions'           => 'boolean',
            'notifications.sound_enabled'        => 'boolean',
            'notifications.vibration_enabled'    => 'boolean',
        ]);

        $user = $request->user();
        $user->notification_settings = $request->input('notifications');
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Settings saved successfully',
        ]);
    }

    /**
     * Get bank details
     */
    public function getBankDetails(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'account_holder_name' => $user->bank_account_holder,
                'bank_name'           => $user->bank_name,
                'account_number'      => $user->bank_account_number ? '••••' . substr($user->bank_account_number, -4) : null,
                'ifsc_code'           => $user->bank_ifsc,
                'has_bank_details'    => !empty($user->bank_account_number),
            ]
        ]);
    }

    /**
     * Save bank details
     */
    public function saveBankDetails(Request $request)
    {
        $request->validate([
            'account_holder_name' => 'required|string|max:255',
            'bank_name'           => 'required|string|max:255',
            'account_number'      => 'required|string|min:9|max:18',
            'ifsc_code'           => ['required', 'string', 'regex:/^[A-Z]{4}0[A-Z0-9]{6}$/'],
        ]);

        $user = $request->user();
        $user->update([
            'bank_account_holder' => $request->account_holder_name,
            'bank_name'           => $request->bank_name,
            'bank_account_number' => $request->account_number,
            'bank_ifsc'           => strtoupper($request->ifsc_code),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bank details saved successfully',
        ]);
    }

    /**
     * Unregister FCM token
     */
    public function unregisterFcmToken(Request $request)
    {
        $user = $request->user();
        $user->fcm_token = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'FCM token unregistered successfully',
        ]);
    }

    /**
     * Generate dynamic Razorpay / UPI QR code for COD order collection
     */
    public function generateUpiQr(Request $request, Order $order)
    {
        $enabled = Setting::get('driver_upi_qr_enabled', '1');
        if ($enabled !== '1' && $enabled !== 1 && $enabled !== true) {
            return response()->json([
                'success' => false,
                'message' => 'UPI QR Payment collection is disabled by admin.',
            ], 400);
        }

        $amount = (float)$order->total_amount;
        $orderNumber = $order->order_number ?: ('#' . $order->id);
        
        $razorpayKey = Setting::get('razorpay_key');
        $razorpaySecret = Setting::get('razorpay_secret');

        $qrImageUrl = null;
        $paymentRef = 'QR_' . $order->id . '_' . time();

        if ($razorpayKey && $razorpaySecret) {
            try {
                $response = \Illuminate\Support\Facades\Http::withBasicAuth($razorpayKey, $razorpaySecret)
                    ->post('https://api.razorpay.com/v1/payments/qr_codes', [
                        'type' => 'upi_qr',
                        'name' => 'Order ' . $orderNumber,
                        'usage' => 'single_use',
                        'fixed_amount' => true,
                        'payment_amount' => (int)round($amount * 100),
                        'description' => 'Payment for Order ' . $orderNumber,
                        'notes' => [
                            'order_id' => $order->id,
                            'driver_id' => $request->user()->id,
                        ],
                    ]);

                if ($response->successful()) {
                    $resData = $response->json();
                    $qrImageUrl = $resData['image_url'] ?? null;
                    $paymentRef = $resData['id'] ?? $paymentRef;
                }
            } catch (\Exception $e) {
                // Fallback to standard UPI Intent QR
            }
        }

        if (!$qrImageUrl) {
            $appName = Setting::get('app_name', 'InAllCart');
            $upiVpa = Setting::get('razorpay_vpa', Setting::get('admin_upi_id', 'inallcart@upi'));
            $upiUrl = "upi://pay?pa={$upiVpa}&pn=" . urlencode($appName) . "&am={$amount}&cu=INR&tn=" . urlencode("Order " . $orderNumber);
            $qrImageUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($upiUrl);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'order_id' => $order->id,
                'amount' => $amount,
                'qr_image_url' => $qrImageUrl,
                'payment_ref' => $paymentRef,
                'currency_symbol' => \App\Models\Setting::get('currency_symbol', '₹'),
            ]
        ]);
    }

    /**
     * Check if Razorpay / UPI QR payment has been completed for an order
     */
    public function checkQrPayment(Request $request, Order $order)
    {
        $order->refresh();
        $statusStr = strtolower((string)$order->payment_status);
        $isPaid = in_array($statusStr, ['paid', 'completed', 'success']);

        return response()->json([
            'success' => true,
            'data' => [
                'is_paid' => $isPaid,
                'payment_status' => $order->payment_status,
                'paid_at' => $order->updated_at?->toIso8601String(),
            ]
        ]);
    }

    /**
     * Get Cash Collection & Pending Submit summary
     */
    public function getCashCollection(Request $request)
    {
        $user = $request->user();
        $cashInHand = (float)($user->cash_in_hand ?? 0.00);
        $pendingDeposit = (float)($user->pending_cash_deposit ?? 0.00);
        $blockedAmount = (float)($user->blocked_amount ?? 0.00);

        $history = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('cash_deposits')) {
            $history = \DB::table('cash_deposits')
                ->where('driver_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'cash_in_hand' => $cashInHand,
                'pending_deposit' => $pendingDeposit,
                'blocked_amount' => $blockedAmount,
                'currency_symbol' => \App\Models\Setting::get('currency_symbol', '₹'),
                'history' => $history,
            ]
        ]);
    }

    /**
     * Submit Cash Deposit Request to Admin
     */
    public function depositCash(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'reference' => 'nullable|string',
        ]);

        $user = $request->user();
        $amount = (float)$request->amount;

        if (\Illuminate\Support\Facades\Schema::hasTable('cash_deposits')) {
            \DB::table('cash_deposits')->insert([
                'driver_id' => $user->id,
                'amount' => $amount,
                'reference' => $request->reference ?? ('DEP_' . time()),
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cash deposit request submitted successfully to admin.',
        ]);
    }

    /**
     * Get Failed / Returned Deliveries
     */
    public function getFailedDeliveries(Request $request)
    {
        $user = $request->user();
        $failedOrders = Order::where('driver_id', $user->id)
            ->whereIn('status', ['cancelled', 'failed', 'returned'])
            ->with(['customer', 'store'])
            ->orderBy('updated_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $failedOrders,
        ]);
    }

    /**
     * Get Driver Feedback & Rating
     */
    public function getFeedback(Request $request)
    {
        $user = $request->user();
        $rating = (float)($user->rating ?? 4.8);
        $reviewCount = (int)($user->review_count ?? 24);

        $reviews = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('driver_reviews')) {
            $reviews = \DB::table('driver_reviews')
                ->where('driver_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'average_rating' => $rating,
                'review_count' => $reviewCount,
                'reviews' => $reviews,
            ]
        ]);
    }
}
