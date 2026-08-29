<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\OtpCode;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

/**
 * Email OTP Service
 * 
 * Handles sending and verifying OTP codes via email (SMTP).
 */
class EmailOtpService
{
    /**
     * Send OTP to an email address
     */
    public function sendOtp(string $email, string $purpose = 'login'): array
    {
        // Check rate limiting
        $canResend = OtpCode::canResend($email, 'email');
        if (!$canResend['can_resend']) {
            return [
                'success' => false,
                'error' => 'rate_limited',
                'message' => "Please wait {$canResend['wait_seconds']} seconds before requesting another OTP.",
                'wait_seconds' => $canResend['wait_seconds'],
            ];
        }

        // Generate OTP
        $otpData = OtpCode::generate($email, 'email', $purpose);
        $otp = $otpData['otp'];

        try {
            // Get app name for email
            $appName = config('app.name', 'InAllCart');

            // Get template data
            $templateData = $this->getTemplateData($otp, $appName);

            // Send email
            Mail::send([], [], function ($message) use ($email, $templateData) {
                $message->to($email)
                    ->subject($templateData['subject'])
                    ->html($templateData['body']);
            });

            return [
                'success' => true,
                'message' => 'OTP sent to your email address.',
                'expires_in_seconds' => $otpData['expires_in_seconds'],
            ];
        } catch (\Exception $e) {
            Log::error('Email OTP send failed', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'email_send_failed',
                'message' => 'Failed to send OTP email. Please try again.',
            ];
        }
    }

    /**
     * Verify OTP code
     */
    public function verifyOtp(string $email, string $code): array
    {
        return OtpCode::verify($email, $code, 'email');
    }

    /**
     * Get email template data (subject & body) for OTP
     */
    private function getTemplateData(string $otp, string $appName): array
    {
        $expiryMinutes = Setting::get('auth_otp_expiry_minutes', 10);
        $year = date('Y');

        // Try to find the custom template
        $template = \App\Models\EmailTemplate::where('type', 'otp_verification')
            ->where('is_active', true)
            ->first();

        if ($template) {
            // Processing placeholders for subject
            $subject = $template->subject;
            $subject = str_replace('{{otp}}', $otp, $subject);
            $subject = str_replace('{{app_name}}', $appName, $subject);

            // Processing placeholders for body
            $body = $template->body;
            $body = str_replace('{{otp}}', $otp, $body);
            $body = str_replace('{{app_name}}', $appName, $body);
            $body = str_replace('{{year}}', $year, $body);
            $body = str_replace('{{expiry_minutes}}', $expiryMinutes, $body);
            
            return [
                'subject' => $subject,
                'body' => $body
            ];
        }

        // Fallback to default hardcoded template
        $params = [
            'appName' => $appName,
            'otp' => $otp,
            'expiryMinutes' => $expiryMinutes
        ];

        return [
            'subject' => "{$appName} - Your Verification Code",
            'body' => $this->getDefaultTemplateBody($params)
        ];
    }

    /**
     * Get default HTML body if no template exists
     */
    private function getDefaultTemplateBody(array $params): string
    {
        extract($params);
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f5f5f5; margin: 0; padding: 20px;">
    <div style="max-width: 480px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center;">
            <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 600;">{$appName}</h1>
        </div>
        <div style="padding: 40px 30px; text-align: center;">
            <h2 style="color: #1a1a1a; margin: 0 0 10px 0; font-size: 20px; font-weight: 600;">Verification Code</h2>
            <p style="color: #666666; margin: 0 0 30px 0; font-size: 14px;">Enter this code to verify your identity</p>
            <div style="background-color: #f8f9fa; border: 2px dashed #e0e0e0; border-radius: 8px; padding: 20px; margin: 0 0 30px 0;">
                <span style="font-family: 'Courier New', monospace; font-size: 36px; font-weight: 700; letter-spacing: 8px; color: #333333;">{$otp}</span>
            </div>
            <p style="color: #999999; margin: 0; font-size: 13px;">This code will expire in {$expiryMinutes} minutes.<br>Do not share this code with anyone.</p>
        </div>
        <div style="background-color: #f8f9fa; padding: 20px 30px; text-align: center; border-top: 1px solid #e0e0e0;">
            <p style="color: #999999; margin: 0; font-size: 12px;">If you didn't request this code, you can safely ignore this email.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
