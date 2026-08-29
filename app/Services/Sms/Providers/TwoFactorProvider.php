<?php

namespace App\Services\Sms\Providers;

use App\Services\Sms\SmsProviderInterface;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * 2Factor.in OTP SMS Provider
 */
class TwoFactorProvider implements SmsProviderInterface
{
    private ?string $apiKey = null;

    public function __construct()
    {
        $this->apiKey = Setting::get('auth_2factor_api_key');
    }

    public function sendOtp(string $phone, string $otp): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => '2Factor is not properly configured.',
                'data' => null,
            ];
        }

        try {
            // 2Factor OTP API
            $phone = $this->formatPhone($phone);
            $response = Http::get("https://2factor.in/API/V1/{$this->apiKey}/SMS/{$phone}/{$otp}");

            if ($response->successful()) {
                $data = $response->json();
                if (($data['Status'] ?? '') === 'Success') {
                    return [
                        'success' => true,
                        'message' => 'OTP sent successfully via 2Factor.',
                        'data' => [
                            'provider' => '2factor',
                            'session_id' => $data['Details'] ?? null,
                        ],
                    ];
                }
            }

            $error = $response->json();
            Log::error('2Factor OTP send failed', ['response' => $error]);
            return [
                'success' => false,
                'message' => $error['Details'] ?? 'Failed to send OTP via 2Factor.',
                'data' => null,
            ];
        } catch (\Exception $e) {
            Log::error('2Factor OTP exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Failed to send OTP: ' . $e->getMessage(),
                'data' => null,
            ];
        }
    }

    public function verifyOtp(string $phone, string $code): ?array
    {
        // 2Factor doesn't have a verify API for transactional OTP
        // Verification is done server-side using our otp_codes table
        return null;
    }

    public function handlesVerification(): bool
    {
        // 2Factor sends OTP but verification is done locally
        return false;
    }

    public function getName(): string
    {
        return '2Factor';
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Format phone number for 2Factor (10 digit Indian number without country code)
     */
    private function formatPhone(string $phone): string
    {
        // Remove all non-digits
        $phone = preg_replace('/\D/', '', $phone);
        
        // If it starts with 91 and is 12 digits, remove the 91
        if (strlen($phone) === 12 && str_starts_with($phone, '91')) {
            $phone = substr($phone, 2);
        }
        
        return $phone;
    }
}
