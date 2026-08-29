<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public string $accountType = 'Partner') {}

    public function build()
    {
        return $this->subject("Your {$this->accountType} Account Has Been Approved! 🎉")
            ->html("
                <div style='font-family: Arial, sans-serif; padding: 20px; color: #333;'>
                    <h2>Congratulations, {$this->user->name}!</h2>
                    <p>We are pleased to inform you that your <strong>{$this->accountType}</strong> account has been reviewed and <strong>APPROVED</strong> by our admin team.</p>
                    <p>You can now open the app, sign in with your credentials, and start receiving orders/deliveries right away.</p>
                    <br>
                    <p>Thank you for partnering with us!</p>
                    <p><strong>InAllCart Operations Team</strong></p>
                </div>
            ");
    }
}
