<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendPromotionEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user,
        public string $title,
        public string $message,
        public ?string $imageUrl = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Find active template
            $template = EmailTemplate::where('type', 'promotion')
                ->where('is_active', true)
                ->first();

            if (!$template) {
                return;
            }

            // Prepare placeholders
            $placeholders = [
                '{{customer_name}}' => $this->user->name,
                '{{promo_title}}' => $this->title,
                '{{promo_description}}' => $this->message,
                '{{promo_code}}' => 'SPECIALOFFER', // Placeholder logic 
                '{{promo_url}}' => config('app.url'),
                '{{app_url}}' => config('app.url'),
                '{{app_name}}' => config('app.name'),
                '{{year}}' => date('Y'),
            ];

            // Replace placeholders
            $subject = str_replace(array_keys($placeholders), array_values($placeholders), $template->subject);
            $body = str_replace(array_keys($placeholders), array_values($placeholders), $template->body);

            // Send Email
            Mail::html($body, function ($message) use ($subject) {
                $message->to($this->user->email)
                    ->subject($subject);
            });

        } catch (\Exception $e) {
            Log::error("Failed to send promotion email to {$this->user->email}: " . $e->getMessage());
            // Optionally release the job back to the queue to retry later
            // $this->release(60); 
        }
    }
}
