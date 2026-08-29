<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class OtpCode extends Model
{
    protected $fillable = [
        'identifier',
        'code',
        'type',
        'purpose',
        'expires_at',
        'verified_at',
        'attempts',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Generate and store a new OTP code
     */
    public static function generate(
        string $identifier,
        string $type = 'phone',
        string $purpose = 'login',
        ?int $length = null,
        ?int $expiryMinutes = null
    ): array {
        // Get settings
        $length = $length ?? (int) Setting::get('auth_otp_length', 6);
        $expiryMinutes = $expiryMinutes ?? (int) Setting::get('auth_otp_expiry_minutes', 10);

        // Generate OTP
        $otp = str_pad((string) random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);

        // Delete any existing OTPs for this identifier and type
        self::where('identifier', $identifier)
            ->where('type', $type)
            ->whereNull('verified_at')
            ->delete();

        // Create new OTP
        $otpCode = self::create([
            'identifier' => $identifier,
            'code' => Hash::make($otp),
            'type' => $type,
            'purpose' => $purpose,
            'expires_at' => Carbon::now()->addMinutes($expiryMinutes),
            'attempts' => 0,
        ]);

        return [
            'otp' => $otp,
            'expires_at' => $otpCode->expires_at,
            'expires_in_seconds' => $expiryMinutes * 60,
        ];
    }

    /**
     * Verify an OTP code
     */
    public static function verify(string $identifier, string $code, string $type = 'phone'): array
    {
        $maxAttempts = (int) Setting::get('auth_otp_max_attempts', 5);

        $otpCode = self::where('identifier', $identifier)
            ->where('type', $type)
            ->whereNull('verified_at')
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$otpCode) {
            return [
                'success' => false,
                'error' => 'otp_expired_or_not_found',
                'message' => 'OTP has expired or does not exist. Please request a new one.',
            ];
        }

        // Check max attempts
        if ($otpCode->attempts >= $maxAttempts) {
            $otpCode->delete();
            return [
                'success' => false,
                'error' => 'max_attempts_exceeded',
                'message' => 'Maximum verification attempts exceeded. Please request a new OTP.',
            ];
        }

        // Verify the code
        if (!Hash::check($code, $otpCode->code)) {
            $otpCode->increment('attempts');
            $remaining = $maxAttempts - $otpCode->attempts;
            return [
                'success' => false,
                'error' => 'invalid_otp',
                'message' => "Invalid OTP code. {$remaining} attempts remaining.",
                'attempts_remaining' => $remaining,
            ];
        }

        // Mark as verified
        $otpCode->update(['verified_at' => Carbon::now()]);

        return [
            'success' => true,
            'message' => 'OTP verified successfully.',
        ];
    }

    /**
     * Check if we can send a new OTP (rate limiting).
     * Only considers active, unverified, non-expired OTPs.
     */
    public static function canResend(string $identifier, string $type = 'phone'): array
    {
        $cooldown = (int) Setting::get('auth_otp_resend_cooldown', 60);

        $lastOtp = self::where('identifier', $identifier)
            ->where('type', $type)
            ->whereNull('verified_at')
            ->where('expires_at', '>', Carbon::now())
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$lastOtp) {
            return ['can_resend' => true, 'wait_seconds' => 0];
        }

        $secondsSinceLastOtp = Carbon::now()->diffInSeconds($lastOtp->created_at);
        $waitSeconds = max(0, $cooldown - $secondsSinceLastOtp);

        return [
            'can_resend' => $waitSeconds === 0,
            'wait_seconds' => $waitSeconds,
        ];
    }

    /**
     * Delete expired and verified OTP records.
     */
    public static function cleanupExpired(): int
    {
        return self::where(function ($q) {
            $q->where('expires_at', '<', Carbon::now())
              ->whereNull('verified_at');
        })->orWhere(function ($q) {
            $q->whereNotNull('verified_at')
              ->where('updated_at', '<', Carbon::now()->subMinutes(30));
        })->delete();
    }
}
