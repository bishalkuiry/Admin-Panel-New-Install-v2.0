<?php

namespace App\Services\Sms\Providers;

use App\Services\Sms\SmsProviderInterface;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Twilio Verify SMS Provider
 */
class TwilioProvider implements SmsProviderInterface
{
    private ?string $accountSid = null;
    private ?string $authToken = null;
    private ?string $verifySid = null;

    public function __construct()
    {
        $this->accountSid = Setting::get('auth_twilio_sid');
        $this->authToken = Setting::get('auth_twilio_token');
        $this->verifySid = Setting::get('auth_twilio_verify_sid');
    }

    public function sendOtp(string $phone, string $otp): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Twilio is not properly configured.',
                'data' => null,
            ];
        }

        try {
            // Twilio Verify API sends OTP (ignores our $otp, generates its own)
            $response = Http::withBasicAuth($this->accountSid, $this->authToken)
                ->asForm()
                ->post("https://verify.twilio.com/v2/Services/{$this->verifySid}/Verifications", [
                    'To' => $phone,
                    'Channel' => 'sms',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'message' => 'OTP sent successfully via Twilio.',
                    'data' => [
                        'provider' => 'twilio',
                        'sid' => $data['sid'] ?? null,
                        'status' => $data['status'] ?? 'pending',
                    ],
                ];
            }

            $error = $response->json();
            Log::error('Twilio OTP send failed', ['response' => $error]);
            return [
                'success' => false,
                'message' => $error['message'] ?? 'Failed to send OTP via Twilio.',
                'data' => null,
            ];
        } catch (\Exception $e) {
            Log::error('Twilio OTP exception', ['error' => $e->getMessage()]);
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
                'message' => 'Twilio is not properly configured.',
            ];
        }

        try {
            $response = Http::withBasicAuth($this->accountSid, $this->authToken)
                ->asForm()
                ->post("https://verify.twilio.com/v2/Services/{$this->verifySid}/VerificationCheck", [
                    'To' => $phone,
                    'Code' => $code,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (($data['status'] ?? '') === 'approved') {
                    return [
                        'success' => true,
                        'message' => 'OTP verified successfully.',
                    ];
                }
                return [
                    'success' => false,
                    'message' => 'Invalid OTP code.',
                ];
            }

            $error = $response->json();
            return [
                'success' => false,
                'message' => $error['message'] ?? 'OTP verification failed.',
            ];
        } catch (\Exception $e) {
            Log::error('Twilio verify exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Verification failed: ' . $e->getMessage(),
            ];
        }
    }

    public function handlesVerification(): bool
    {
        return true; // Twilio Verify handles its own verification
    }

    public function getName(): string
    {
        return 'Twilio';
    }

    public function isConfigured(): bool
    {
        return !empty($this->accountSid) 
            && !empty($this->authToken) 
            && !empty($this->verifySid);
    }
}
