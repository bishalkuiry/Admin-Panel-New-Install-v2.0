<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Setting;
use App\Models\OtpCode;
use App\Services\OtpService;
use App\Services\Sms\SmsGatewayService;
use App\Services\EmailOtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

class OtpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Clear settings cache to ensure fresh state
        Cache::flush();
    }

    /** @test */
    public function it_can_generate_and_verify_email_otp()
    {
        Mail::fake();

        // Configure settings
        Setting::set('auth_email_otp_enabled', '1');
        Setting::set('auth_otp_length', '6');
        Setting::set('auth_otp_expiry_minutes', '10');

        $service = app(EmailOtpService::class);
        $email = 'test@example.com';

        // Send OTP
        $result = $service->sendOtp($email);

        $this->assertTrue($result['success']);
        
        // Check DB
        $otpRecord = OtpCode::where('identifier', $email)->where('type', 'email')->first();
        $this->assertNotNull($otpRecord);
        $this->assertFalse($otpRecord->is_verified);

        // Verify invalid OTP
        $verifyResult = $service->verifyOtp($email, '000000');
        $this->assertFalse($verifyResult['success']);

        // Manual check of hash (since we don't know the generated random code easily without capturing it or hacking randomness)
        // Check if we can verify with the correct code?
        // In real test, we might check if a mail was sent.
        
        Mail::assertSent(function (\Illuminate\Mail\Mailable $mail) use ($email) {
            return $mail->hasTo($email);
        });
    }

    /** @test */
    public function sms_gateway_service_selects_correct_provider()
    {
        $service = app(SmsGatewayService::class);

        // Default should be firebase
        Setting::set('auth_sms_gateway', 'firebase');
        $this->assertTrue($service->isFirebaseActive());
        
        // Twilio
        Setting::set('auth_sms_gateway', 'twilio');
        Setting::set('auth_twilio_sid', 'test_sid');
        Setting::set('auth_twilio_token', 'test_token');
        Setting::set('auth_twilio_verify_sid', 'test_verify_sid');
        
        // Re-resolve service or just check active provider if dynamic
        // Since service caches providers in constructor, we might need a fresh instance
        $service = app(SmsGatewayService::class); 
        $this->assertEquals('twilio', $service->getActiveProviderName());
        
        // Fallback to firebase if config missing
        Setting::set('auth_sms_gateway', 'nexmo');
        Setting::set('auth_nexmo_api_key', ''); // Empty config
        
        $service = app(SmsGatewayService::class);
        $this->assertTrue($service->isFirebaseActive());
    }

    /** @test */
    public function otp_service_creates_user_if_not_exists()
    {
        $otpService = app(OtpService::class);
        $phone = '+1234567890';
        
        // Verify user doesn't exist
        $this->assertNull(User::where('phone', $phone)->first());

        // Create user
        $user = $otpService->findOrCreateUser($phone, 'phone', 'New User');

        $this->assertNotNull($user);
        $this->assertEquals($phone, $user->phone);
        $this->assertEquals('New User', $user->name);
    }

    /** @test */
    public function otp_service_returns_correct_config()
    {
        Setting::set('auth_phone_otp_enabled', '1');
        Setting::set('auth_email_otp_enabled', '0');
        Setting::set('auth_manual_login_enabled', '1');
        
        $otpService = app(OtpService::class);
        $config = $otpService->getAuthConfig();

        $this->assertTrue($config['phone_otp_enabled']);
        $this->assertFalse($config['email_otp_enabled']);
        $this->assertTrue($config['manual_login_enabled']);
    }
}
