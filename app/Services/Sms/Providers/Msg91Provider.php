<?php

namespace App\Services\Sms\Providers;

use App\Services\Sms\SmsProviderInterface;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * MSG91 OTP SMS Provider
 */
class Msg91Provider implements SmsProviderInterface
{
    private ?string $authKey = null;
    private ?string $templateId = null;

    public function __construct()
    {
        $this->authKey = Setting::get('auth_msg91_auth_key');
        $this->templateId = Setting::get('auth_msg91_template_id');
    }

    public function sendOtp(string $phone, string $otp): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'MSG91 is not properly configured.',
                'data' => null,
            ];
        }

        try {
            // MSG91 OTP API
            $response = Http::withHeaders([
                'authkey' => $this->authKey,
                'Content-Type' => 'application/json',
            ])->post('https://control.msg91.com/api/v5/otp', [
                'template_id' => $this->templateId,
                'mobile' => $this->formatPhone($phone),
                'otp' => $otp,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (($data['type'] ?? '') === 'success') {
                    return [
                        'success' => true,
                        'message' => 'OTP sent successfully via MSG91.',
                        'data' => [
                            'provider' => 'msg91',
                            'request_id' => $data['request_id'] ?? null,
                        ],
                    ];
                }
            }

            $error = $response->json();
            Log::error('MSG91 OTP send failed', ['response' => $error]);
            return [
                'success' => false,
                'message' => $error['message'] ?? 'Failed to send OTP via MSG91.',
                'data' => null,
            ];
        } catch (\Exception $e) {
            Log::error('MSG91 OTP exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Failed to send OTP: ' . $e->getMessage(),
                'data' => null,
            ];
        }
    }

    public function verifyOtp(string $phone, string $code): ?array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'MSG91 is not properly configured.',
            ];
        }

        try {
            $response = Http::withHeaders([
                'authkey' => $this->authKey,
            ])->get('https://control.msg91.com/api/v5/otp/verify', [
                'mobile' => $this->formatPhone($phone),
                'otp' => $code,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (($data['type'] ?? '') === 'success') {
                    return [
                        'success' => true,
                        'message' => 'OTP verified successfully.',
                    ];
                }
                return [
                    'success' => false,
                    'message' => $data['message'] ?? 'Invalid OTP code.',
                ];
            }

            $error = $response->json();
            return [
                'success' => false,
                'message' => $error['message'] ?? 'OTP verification failed.',
            ];
        } catch (\Exception $e) {
            Log::error('MSG91 verify exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Verification failed: ' . $e->getMessage(),
            ];
        }
    }

    public function handlesVerification(): bool
    {
        return true; // MSG91 can verify OTPs
    }

    public function getName(): string
    {
        return 'MSG91';
    }

    public function isConfigured(): bool
    {
        return !empty($this->authKey) && !empty($this->templateId);
    }

    /**
     * Format phone number for MSG91 (remove + prefix)
     */
    private function formatPhone(string $phone): string
    {
        return ltrim($phone, '+');
    }
}
