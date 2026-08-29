<?php

namespace App\Services\Sms\Providers;

use App\Services\Sms\SmsProviderInterface;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Nexmo (Vonage) Verify SMS Provider
 */
class NexmoProvider implements SmsProviderInterface
{
    private ?string $apiKey = null;
    private ?string $apiSecret = null;
    private ?string $brandName = null;

    public function __construct()
    {
        $this->apiKey = Setting::get('auth_nexmo_api_key');
        $this->apiSecret = Setting::get('auth_nexmo_api_secret');
        $this->brandName = Setting::get('auth_nexmo_brand_name', config('app.name', 'InAllCart'));
    }

    public function sendOtp(string $phone, string $otp): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Nexmo/Vonage is not properly configured.',
                'data' => null,
            ];
        }

        try {
            // Use Vonage SMS API to send our own OTP (not Vonage Verify)
            // This way local OtpCode::verify() works correctly
            $response = Http::asForm()
                ->post('https://rest.nexmo.com/sms/json', [
                    'api_key'    => $this->apiKey,
                    'api_secret' => $this->apiSecret,
                    'to'         => $this->formatPhone($phone),
                    'from'       => $this->brandName,
                    'text'       => "Your verification code is: {$otp}",
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $messages = $data['messages'] ?? [];
                if (!empty($messages) && ($messages[0]['status'] ?? '1') === '0') {
                    return [
                        'success' => true,
                        'message' => 'OTP sent successfully via Vonage.',
                        'data' => ['provider' => 'nexmo'],
                    ];
                }
                $errorText = $messages[0]['error-text'] ?? 'Failed to send OTP via Vonage.';
                Log::error('Nexmo OTP send failed', ['response' => $data]);
                return [
                    'success' => false,
                    'message' => $errorText,
                    'data' => null,
                ];
            }

            $error = $response->json();
            Log::error('Nexmo OTP send failed', ['response' => $error]);
            return [
                'success' => false,
                'message' => $error['error_text'] ?? 'Failed to send OTP via Vonage.',
                'data' => null,
            ];
        } catch (\Exception $e) {
            Log::error('Nexmo OTP exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Failed to send OTP: ' . $e->getMessage(),
                'data' => null,
            ];
        }
    }

    public function verifyOtp(string $phone, string $code): ?array
    {
        // Verification handled locally via OtpCode::verify()
        return null;
    }

    public function handlesVerification(): bool
    {
        return false;
    }

    public function getName(): string
    {
        return 'Nexmo (Vonage)';
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->apiSecret);
    }

    /**
     * Format phone number for Nexmo (E.164 format without +)
     */
    private function formatPhone(string $phone): string
    {
        return ltrim($phone, '+');
    }
}
