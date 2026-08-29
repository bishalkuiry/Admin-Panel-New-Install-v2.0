<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendTransactionalEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Order $order,
        public string $templateType
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Reload order to ensure we have fresh data and relationships
            // especially if some time passed since dispatch
            $order = $this->order->load(['user', 'store', 'items', 'address', 'deliveryPartner']);

            if (!$order->user || !$order->user->email) {
                return;
            }

            // Find active template
            $template = EmailTemplate::where('type', $this->templateType)
                ->where('is_active', true)
                ->first();

            if (!$template) {
                return;
            }

            // Prepare common placeholders
            $placeholders = [
                '{{customer_name}}' => $order->user->name,
                '{{order_number}}' => $order->order_number,
                '{{order_date}}' => $order->created_at->format('F j, Y'),
                '{{order_total}}' => \App\Helpers\CurrencyHelper::format($order->total),
                '{{payment_method}}' => ucfirst($order->payment_method),
                '{{shipping_address}}' => $order->address->full_address ?? ($order->address->address ?? ''),
                '{{order_tracking_url}}' => config('app.url') . "/orders/{$order->id}/track", // Adjust URL as needed
                '{{app_name}}' => config('app.name'),
                '{{year}}' => date('Y'),
                '{{app_url}}' => config('app.url'),
                '{{delivery_partner}}' => $order->deliveryPartner->name ?? 'Our delivery partner',
            ];

            // Specific placeholder: Items List HTML
            if (str_contains($template->body, '{{items_list_html}}')) {
                $itemsHtml = '';
                foreach ($order->items as $item) {
                    $itemsHtml .= "<tr>
                        <td>{$item->product_name}</td>
                        <td>{$item->quantity}</td>
                        <td>" . \App\Helpers\CurrencyHelper::format($item->total) . "</td>
                    </tr>";
                }
                $placeholders['{{items_list_html}}'] = $itemsHtml;
            }

            // Replace placeholders in Subject and Body
            $subject = str_replace(array_keys($placeholders), array_values($placeholders), $template->subject);
            $body = str_replace(array_keys($placeholders), array_values($placeholders), $template->body);

            // Send Email
            Mail::html($body, function ($message) use ($order, $subject) {
                $message->to($order->user->email)
                    ->subject($subject);
            });

            Log::info("Sent transactional email [{$this->templateType}] to {$order->user->email}");

        } catch (\Exception $e) {
            Log::error("Failed to send transactional email [{$this->templateType}]: " . $e->getMessage());
            $this->release(60); // Retry in 60 seconds
        }
    }
}
