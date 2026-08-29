<?php

namespace App\Services\Sms\Providers;

use App\Services\Sms\SmsProviderInterface;
use App\Models\Setting;

/**
 * Firebase SMS Provider
 * 
 * Firebase Phone Auth is handled client-side on mobile app.
 * This provider returns configuration for the app to use Firebase SDK.
 */
class FirebaseSmsProvider implements SmsProviderInterface
{
    public function sendOtp(string $phone, string $otp): array
    {
        // Firebase OTP is sent client-side via Firebase SDK
        // The server just confirms Firebase should be used
        return [
            'success' => true,
            'message' => 'Use Firebase SDK for phone verification.',
            'data' => [
                'provider' => 'firebase',
                'client_side' => true,
            ],
        ];
    }

    public function verifyOtp(string $phone, string $code): ?array
    {
        // Firebase verification is handled client-side
        // The app will send Firebase ID token for server verification
        return null;
    }

    public function handlesVerification(): bool
    {
        // Firebase handles verification on client-side
        return false;
    }

    public function getName(): string
    {
        return 'Firebase';
    }

    public function isConfigured(): bool
    {
        // Firebase is always "configured" as it's set up in the mobile app
        return true;
    }
}
