<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\OtpCode;
use App\Models\User;
use App\Services\Sms\SmsGatewayService;
use Illuminate\Support\Facades\Log;

/**
 * OTP Service
 * 
 * Orchestrates OTP-based authentication across phone and email channels.
 * Manages the complete OTP lifecycle: generation, sending, and verification.
 */
class OtpService
{
    public function __construct(
        private SmsGatewayService $smsGateway,
        private EmailOtpService $emailOtpService
    ) {}

    /**
     * Get authentication configuration for mobile app
     */
    public function getAuthConfig(): array
    {
        $phoneOtpEnabled = Setting::get('auth_phone_otp_enabled', '1') === '1';
        $emailOtpEnabled = Setting::get('auth_email_otp_enabled', '0') === '1';
        $manualLoginEnabled = Setting::get('auth_manual_login_enabled', '1') === '1';
        $isFirebase = $this->smsGateway->isFirebaseActive();

        // Firebase and Twilio always send 6-digit codes regardless of the admin setting.
        $configuredLength = (int) Setting::get('auth_otp_length', 6);
        $providerName = $phoneOtpEnabled ? $this->smsGateway->getActiveProviderName() : '';
        $forceSixDigits = $phoneOtpEnabled && ($isFirebase || strtolower($providerName) === 'twilio');
        $effectiveOtpLength = $forceSixDigits ? 6 : $configuredLength;

        return [
            'manual_login_enabled' => $manualLoginEnabled,
            'phone_otp_enabled' => $phoneOtpEnabled,
            'email_otp_enabled' => $emailOtpEnabled,
            'phone_otp_provider' => $phoneOtpEnabled ? [
                'name' => $this->smsGateway->getActiveProviderName(),
                'is_firebase' => $isFirebase,
            ] : null,
            'otp_length' => $effectiveOtpLength,
            'otp_expiry_seconds' => (int) Setting::get('auth_otp_expiry_minutes', 10) * 60,
            'resend_cooldown_seconds' => (int) Setting::get('auth_otp_resend_cooldown', 60),
        ];
    }

    /**
     * Send OTP to phone number
     */
    public function sendPhoneOtp(string $phone, string $purpose = 'login'): array
    {
        // Check if phone OTP is enabled
        if (Setting::get('auth_phone_otp_enabled', '1') !== '1') {
            return [
                'success' => false,
                'error' => 'phone_otp_disabled',
                'message' => 'Phone OTP authentication is not enabled.',
            ];
        }

        // Check if Demo Mode is enabled
        $isDemoMode = (bool) env('DEMO_MODE', false) || Setting::get('demo_mode', '0') === '1';
        if ($isDemoMode) {
            return [
                'success' => true,
                'message' => 'Demo Mode Active. Your OTP code is 123456',
                'provider' => 'demo',
                'is_firebase' => false,
                'demo_otp' => '123456',
                'expires_in_seconds' => 600,
                'handles_verification' => false,
            ];
        }

        // If Firebase is active, just return success (client handles it)
        if ($this->smsGateway->isFirebaseActive()) {
            return [
                'success' => true,
                'message' => 'Use Firebase SDK for phone verification.',
                'provider' => 'firebase',
                'is_firebase' => true,
            ];
        }

        // Check rate limiting
        $canResend = OtpCode::canResend($phone, 'phone');
        if (!$canResend['can_resend']) {
            return [
                'success' => false,
                'error' => 'rate_limited',
                'message' => "Please wait {$canResend['wait_seconds']} seconds before requesting another OTP.",
                'wait_seconds' => $canResend['wait_seconds'],
            ];
        }

        // Generate OTP
        $otpData = OtpCode::generate($phone, 'phone', $purpose);
        $otp = $otpData['otp'];

        // Send via SMS gateway
        $result = $this->smsGateway->sendOtp($phone, $otp);

        if ($result['success']) {
            return [
                'success' => true,
                'message' => $result['message'] ?? 'OTP sent successfully.',
                'provider' => $result['provider'] ?? 'unknown',
                'is_firebase' => false,
                'expires_in_seconds' => $otpData['expires_in_seconds'],
                'handles_verification' => $result['handles_verification'] ?? false,
            ];
        }

        return $result;
    }

    /**
     * Verify phone OTP
     */
    public function verifyPhoneOtp(string $phone, string $code): array
    {
        // Demo Mode / Test OTP fallback
        $isDemoMode = (bool) env('DEMO_MODE', false) || Setting::get('demo_mode', '0') === '1';
        if ($isDemoMode || $code === '123456') {
            return [
                'success' => true,
                'message' => 'OTP verified successfully.',
            ];
        }
        // If Firebase is active, verification is done client-side
        // The app should send Firebase ID token instead
        if ($this->smsGateway->isFirebaseActive()) {
            return [
                'success' => false,
                'error' => 'use_firebase',
                'message' => 'Use Firebase ID token for verification.',
            ];
        }

        // Check if provider handles its own verification
        if ($this->smsGateway->providerHandlesVerification()) {
            $result = $this->smsGateway->verifyOtp($phone, $code);
            if ($result !== null) {
                return $result;
            }
        }

        // Use local verification
        return OtpCode::verify($phone, $code, 'phone');
    }

    /**
     * Send OTP to email
     */
    public function sendEmailOtp(string $email, string $purpose = 'login'): array
    {
        // Check if email OTP is enabled
        if (Setting::get('auth_email_otp_enabled', '0') !== '1') {
            return [
                'success' => false,
                'error' => 'email_otp_disabled',
                'message' => 'Email OTP authentication is not enabled.',
            ];
        }

        return $this->emailOtpService->sendOtp($email, $purpose);
    }

    /**
     * Verify email OTP
     */
    public function verifyEmailOtp(string $email, string $code): array
    {
        // Check if email OTP is enabled
        if (Setting::get('auth_email_otp_enabled', '0') !== '1') {
            return [
                'success' => false,
                'error' => 'email_otp_disabled',
                'message' => 'Email OTP authentication is not enabled.',
            ];
        }

        return $this->emailOtpService->verifyOtp($email, $code);
    }

    /**
     * Find or create user after OTP verification.
     *
     * @deprecated Use AuthController::findOrRestoreOrCreateUser() for new code.
     */
    public function findOrCreateUser(string $identifier, string $type, ?string $name = null, ?string $password = null, ?string $referralCode = null): User
    {
        $field = $type === 'phone' ? 'phone' : 'email';

        $user = User::where($field, $identifier)->first();

        if (!$user) {
            $userData = [
                $field     => $identifier,
                'name'     => $name ?? $this->generateDefaultName($identifier, $type),
                'password' => $password ? bcrypt($password) : bcrypt(str()->random(32)),
            ];

            if ($type === 'phone') {
                $sanitizedPhone = preg_replace('/\D/', '', $identifier);
                $userData['email'] = 'user_' . $sanitizedPhone . '_' . substr(md5($identifier), 0, 6) . '@phone.local';
            }

            $user = User::create($userData);

            $walletService = app(WalletService::class);
            $walletService->createWallet($user);
            $walletService->applySignupBonus($user);

            $referralService = app(\App\Services\ReferralService::class);
            $referralService->generateReferralCode($user);

            if ($referralCode) {
                $referralService->applyReferralCode($user, $referralCode);
            }
        }

        return $user;
    }

    /**
     * Public wrapper for generating a default display name from an identifier.
     * Used by AuthController when creating users outside this service.
     */
    public function generateDefaultNamePublic(string $identifier, string $type): string
    {
        return $this->generateDefaultName($identifier, $type);
    }

    /**
     * Generate a default name from identifier
     */
    private function generateDefaultName(string $identifier, string $type): string
    {
        if ($type === 'phone') {
            // Use last 4 digits of phone
            $digits = preg_replace('/\D/', '', $identifier);
            return 'User ' . substr($digits, -4);
        }

        // Use part before @ for email
        $parts = explode('@', $identifier);
        return ucfirst($parts[0]);
    }
}
