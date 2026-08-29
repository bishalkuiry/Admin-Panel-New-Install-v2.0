<?php

namespace App\Services\Payment;

use Razorpay\Api\Api;
use Exception;
use Illuminate\Support\Facades\Log;

class RazorpayGateway implements PaymentGatewayInterface
{
    protected $api;
    protected $config;

    public function __construct()
    {
        $this->config = config('payment.gateways.razorpay');
        
        if ($this->isEnabled()) {
            $this->api = new Api(
                $this->config['key_id'],
                $this->config['key_secret']
            );
        }
    }

    public function createOrder(array $data): array
    {
        try {
            $orderData = [
                'receipt' => $data['order_number'],
                'amount' => $data['amount'] * 100, // Convert to paise
                'currency' => $data['currency'] ?? 'INR',
                'notes' => $data['notes'] ?? [],
            ];

            $order = $this->api->order->create($orderData);

            return [
                'success' => true,
                'order_id' => $order->id,
                'amount' => $order->amount,
                'currency' => $order->currency,
                'key_id' => $this->config['key_id'],
            ];
        } catch (Exception $e) {
            Log::error('Razorpay order creation failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function verifyPayment(array $data): bool
    {
        try {
            $attributes = [
                'razorpay_order_id' => $data['razorpay_order_id'],
                'razorpay_payment_id' => $data['razorpay_payment_id'],
                'razorpay_signature' => $data['razorpay_signature'],
            ];

            $this->api->utility->verifyPaymentSignature($attributes);
            
            return true;
        } catch (Exception $e) {
            Log::error('Razorpay payment verification failed: ' . $e->getMessage());
            return false;
        }
    }

    public function refund(string $paymentId, float $amount): array
    {
        try {
            $refund = $this->api->payment->fetch($paymentId)->refund([
                'amount' => $amount * 100, // Convert to paise
            ]);

            return [
                'success' => true,
                'refund_id' => $refund->id,
                'amount' => $refund->amount / 100,
                'status' => $refund->status,
            ];
        } catch (Exception $e) {
            Log::error('Razorpay refund failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function getPaymentDetails(string $paymentId): array
    {
        try {
            $payment = $this->api->payment->fetch($paymentId);

            return [
                'success' => true,
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'amount' => $payment->amount / 100,
                'currency' => $payment->currency,
                'status' => $payment->status,
                'method' => $payment->method,
                'email' => $payment->email,
                'contact' => $payment->contact,
                'created_at' => $payment->created_at,
            ];
        } catch (Exception $e) {
            Log::error('Razorpay payment details fetch failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function isEnabled(): bool
    {
        return $this->config['enabled'] && 
               !empty($this->config['key_id']) && 
               !empty($this->config['key_secret']);
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        try {
            $expectedSignature = hash_hmac('sha256', $payload, $this->config['webhook_secret']);
            return hash_equals($expectedSignature, $signature);
        } catch (Exception $e) {
            Log::error('Razorpay webhook verification failed: ' . $e->getMessage());
            return false;
        }
    }
}
