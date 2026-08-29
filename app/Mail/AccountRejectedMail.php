<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public ?string $reason = null, public string $accountType = 'Partner') {}

    public function build()
    {
        $reasonText = $this->reason ? "<p><strong>Reason:</strong> {$this->reason}</p>" : "";

        return $this->subject("Account Registration Status Update — {$this->accountType}")
            ->html("
                <div style='font-family: Arial, sans-serif; padding: 20px; color: #333;'>
                    <h2>Hello {$this->user->name},</h2>
                    <p>Thank you for submitting your application for an <strong>{$this->accountType}</strong> account.</p>
                    <p>After review by our onboarding team, your application was not approved at this time.</p>
                    {$reasonText}
                    <p>If you have any questions or need to re-submit your verification documents, please contact our support team.</p>
                    <br>
                    <p>Regards,</p>
                    <p><strong>InAllCart Support Team</strong></p>
                </div>
            ");
    }
}
