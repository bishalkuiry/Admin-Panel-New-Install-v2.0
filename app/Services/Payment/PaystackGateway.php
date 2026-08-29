<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class PaystackGateway implements PaymentGatewayInterface
{
    protected $config;
    protected $baseUrl = 'https://api.paystack.co';

    public function __construct()
    {
        $this->config = config('payment.gateways.paystack');
    }

    public function createOrder(array $data): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->config['secret_key'],
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/transaction/initialize', [
                'email' => $data['email'],
                'amount' => $data['amount'] * 100, // Convert to kobo
                'currency' => $data['currency'] ?? 'NGN',
                'reference' => $data['order_number'],
                'callback_url' => $data['callback_url'] ?? null,
                'metadata' => $data['metadata'] ?? [],
            ]);

            $result = $response->json();

            if ($result['status']) {
                return [
                    'success' => true,
                    'authorization_url' => $result['data']['authorization_url'],
                    'access_code' => $result['data']['access_code'],
                    'reference' => $result['data']['reference'],
                ];
            }

            return [
                'success' => false,
                'message' => $result['message'] ?? 'Payment initialization failed',
            ];
        } catch (Exception $e) {
            Log::error('Paystack order creation failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function verifyPayment(array $data): bool
    {
        try {
            $reference = $data['reference'];
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->config['secret_key'],
            ])->get($this->baseUrl . '/transaction/verify/' . $reference);

            $result = $response->json();

            return $result['status'] && $result['data']['status'] === 'success';
        } catch (Exception $e) {
            Log::error('Paystack payment verification failed: ' . $e->getMessage());
            return false;
        }
    }

    public function refund(string $paymentId, float $amount): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->config['secret_key'],
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/refund', [
                'transaction' => $paymentId,
                'amount' => $amount * 100, // Convert to kobo
            ]);

            $result = $response->json();

            if ($result['status']) {
                return [
                    'success' => true,
                    'refund_id' => $result['data']['id'],
                    'status' => $result['data']['status'],
                ];
            }

            return [
                'success' => false,
                'message' => $result['message'] ?? 'Refund failed',
            ];
        } catch (Exception $e) {
            Log::error('Paystack refund failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function getPaymentDetails(string $paymentId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->config['secret_key'],
            ])->get($this->baseUrl . '/transaction/' . $paymentId);

            $result = $response->json();

            if ($result['status']) {
                return [
                    'success' => true,
                    'data' => $result['data'],
                ];
            }

            return [
                'success' => false,
                'message' => $result['message'] ?? 'Failed to fetch payment details',
            ];
        } catch (Exception $e) {
            Log::error('Paystack payment details fetch failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function isEnabled(): bool
    {
        return $this->config['enabled'] && 
               !empty($this->config['public_key']) && 
               !empty($this->config['secret_key']);
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        try {
            return hash_hmac('sha512', $payload, $this->config['secret_key']) === $signature;
        } catch (Exception $e) {
            Log::error('Paystack webhook verification failed: ' . $e->getMessage());
            return false;
        }
    }
}
