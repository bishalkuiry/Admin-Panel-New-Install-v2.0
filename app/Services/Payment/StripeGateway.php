<?php

namespace App\Services\Payment;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\WebhookSignature;
use Stripe\Exception\SignatureVerificationException;
use Exception;
use Illuminate\Support\Facades\Log;

class StripeGateway implements PaymentGatewayInterface
{
    protected $config;

    public function __construct()
    {
        $this->config = config('payment.gateways.stripe');
        
        if ($this->isEnabled()) {
            Stripe::setApiKey($this->config['secret_key']);
        }
    }

    public function createOrder(array $data): array
    {
        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $data['amount'] * 100, // Convert to cents
                'currency' => $data['currency'] ?? 'usd',
                'description' => $data['description'] ?? 'Order Payment',
                'metadata' => [
                    'order_number' => $data['order_number'],
                ],
            ], [
                // Idempotency key prevents duplicate charges on network retry
                'idempotency_key' => 'pi_' . $data['order_number'],
            ]);

            return [
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
            ];
        } catch (Exception $e) {
            Log::error('Stripe payment intent creation failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function verifyPayment(array $data): bool
    {
        try {
            $paymentIntent = PaymentIntent::retrieve($data['payment_intent_id']);
            
            return $paymentIntent->status === 'succeeded';
        } catch (Exception $e) {
            Log::error('Stripe payment verification failed: ' . $e->getMessage());
            return false;
        }
    }

    public function refund(string $paymentId, float $amount): array
    {
        try {
            $refund = Refund::create([
                'payment_intent' => $paymentId,
                'amount' => $amount * 100, // Convert to cents
            ]);

            return [
                'success' => true,
                'refund_id' => $refund->id,
                'amount' => $refund->amount / 100,
                'status' => $refund->status,
            ];
        } catch (Exception $e) {
            Log::error('Stripe refund failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function getPaymentDetails(string $paymentId): array
    {
        try {
            $paymentIntent = PaymentIntent::retrieve($paymentId);

            return [
                'success' => true,
                'payment_id' => $paymentIntent->id,
                'amount' => $paymentIntent->amount / 100,
                'currency' => $paymentIntent->currency,
                'status' => $paymentIntent->status,
                'created_at' => $paymentIntent->created,
            ];
        } catch (Exception $e) {
            Log::error('Stripe payment details fetch failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function isEnabled(): bool
    {
        return $this->config['enabled'] && 
               !empty($this->config['publishable_key']) && 
               !empty($this->config['secret_key']);
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        try {
            WebhookSignature::verifyHeader($payload, $signature, $this->config['webhook_secret']);
            return true;
        } catch (SignatureVerificationException $e) {
            Log::error('Stripe webhook verification failed: ' . $e->getMessage());
            return false;
        } catch (Exception $e) {
            Log::error('Stripe webhook error: ' . $e->getMessage());
            return false;
        }
    }
}
