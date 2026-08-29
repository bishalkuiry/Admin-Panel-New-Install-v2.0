<?php

namespace App\Services\Sms;

use App\Models\Setting;
use App\Services\Sms\Providers\FirebaseSmsProvider;
use App\Services\Sms\Providers\TwilioProvider;
use App\Services\Sms\Providers\Msg91Provider;
use App\Services\Sms\Providers\TwoFactorProvider;
use App\Services\Sms\Providers\NexmoProvider;

/**
 * SMS Gateway Service
 * 
 * Manages SMS providers and routes OTP requests to the active provider.
 * Falls back to Firebase if no other provider is configured/active.
 */
class SmsGatewayService
{
    private array $providers = [];

    public function __construct()
    {
        // Initialize all providers
        $this->providers = [
            'firebase' => new FirebaseSmsProvider(),
            'twilio' => new TwilioProvider(),
            'msg91' => new Msg91Provider(),
            '2factor' => new TwoFactorProvider(),
            'nexmo' => new NexmoProvider(),
        ];
    }

    /**
     * Get the active SMS provider based on settings
     */
    public function getActiveProvider(): SmsProviderInterface
    {
        $gateway = Setting::get('auth_sms_gateway', 'firebase');

        // If a non-Firebase gateway is selected, check if it's configured
        if ($gateway !== 'firebase' && isset($this->providers[$gateway])) {
            $provider = $this->providers[$gateway];
            if ($provider->isConfigured()) {
                return $provider;
            }
            // Fall back to Firebase if provider isn't properly configured
        }

        // Default to Firebase
        return $this->providers['firebase'];
    }

    /**
     * Get the name of the active provider
     */
    public function getActiveProviderName(): string
    {
        return $this->getActiveProvider()->getName();
    }

    /**
     * Check if Firebase is the active provider
     */
    public function isFirebaseActive(): bool
    {
        $activeProvider = $this->getActiveProvider();
        return $activeProvider instanceof FirebaseSmsProvider;
    }

    /**
     * Send OTP via the active provider
     */
    public function sendOtp(string $phone, string $otp): array
    {
        $provider = $this->getActiveProvider();
        $result = $provider->sendOtp($phone, $otp);
        
        // Add provider info to result
        $result['provider'] = $provider->getName();
        $result['handles_verification'] = $provider->handlesVerification();
        
        return $result;
    }

    /**
     * Verify OTP via the active provider (if supported)
     */
    public function verifyOtp(string $phone, string $code): ?array
    {
        $provider = $this->getActiveProvider();
        
        if (!$provider->handlesVerification()) {
            return null; // Provider doesn't handle verification
        }

        return $provider->verifyOtp($phone, $code);
    }

    /**
     * Check if the active provider handles its own verification
     */
    public function providerHandlesVerification(): bool
    {
        return $this->getActiveProvider()->handlesVerification();
    }

    /**
     * Get all available providers with their status
     */
    public function getAvailableProviders(): array
    {
        $result = [];
        foreach ($this->providers as $key => $provider) {
            $result[$key] = [
                'name' => $provider->getName(),
                'configured' => $provider->isConfigured(),
                'handles_verification' => $provider->handlesVerification(),
            ];
        }
        return $result;
    }

    /**
     * Get a specific provider by key
     */
    public function getProvider(string $key): ?SmsProviderInterface
    {
        return $this->providers[$key] ?? null;
    }
}
