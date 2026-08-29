<?php

namespace App\Services\Sms;

/**
 * Interface for SMS providers
 */
interface SmsProviderInterface
{
    /**
     * Send OTP to a phone number
     * 
     * @param string $phone Phone number with country code
     * @param string $otp The OTP code
     * @return array ['success' => bool, 'message' => string, 'data' => array|null]
     */
    public function sendOtp(string $phone, string $otp): array;

    /**
     * Verify OTP (for providers that handle verification)
     * Returns null if provider doesn't support verification
     * 
     * @param string $phone Phone number with country code
     * @param string $code The OTP code to verify
     * @return array|null ['success' => bool, 'message' => string]
     */
    public function verifyOtp(string $phone, string $code): ?array;

    /**
     * Check if provider handles its own verification
     */
    public function handlesVerification(): bool;

    /**
     * Get provider name
     */
    public function getName(): string;

    /**
     * Check if provider is properly configured
     */
    public function isConfigured(): bool;
}
