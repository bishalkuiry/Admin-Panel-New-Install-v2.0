<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class FlutterwaveGateway implements PaymentGatewayInterface
{
    protected $config;
    protected $baseUrl = 'https://api.flutterwave.com/v3';

    public function __construct()
    {
        $this->config = config('payment.gateways.flutterwave');
    }

    public function createOrder(array $data): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->config['secret_key'],
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/payments', [
                'tx_ref' => $data['order_number'],
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'NGN',
                'redirect_url' => $data['callback_url'] ?? null,
                'payment_options' => 'card,mobilemoney,ussd',
                'customer' => [
                    'email' => $data['email'],
                    'name' => $data['name'] ?? '',
                ],
                'customizations' => [
                    'title' => 'Order Payment',
                    'description' => 'Payment for order ' . $data['order_number'],
                ],
            ]);

            $result = $response->json();

            if ($result['status'] === 'success') {
                return [
                    'success' => true,
                    'payment_link' => $result['data']['link'],
                    'reference' => $data['order_number'],
                ];
            }

            return [
                'success' => false,
                'message' => $result['message'] ?? 'Payment initialization failed',
            ];
        } catch (Exception $e) {
            Log::error('Flutterwave order creation failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function verifyPayment(array $data): bool
    {
        try {
            $transactionId = $data['transaction_id'];
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->config['secret_key'],
            ])->get($this->baseUrl . '/transactions/' . $transactionId . '/verify');

            $result = $response->json();

            return $result['status'] === 'success' && 
                   $result['data']['status'] === 'successful';
        } catch (Exception $e) {
            Log::error('Flutterwave payment verification failed: ' . $e->getMessage());
            return false;
        }
    }

    public function refund(string $paymentId, float $amount): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->config['secret_key'],
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/transactions/' . $paymentId . '/refund', [
                'amount' => $amount,
            ]);

            $result = $response->json();

            if ($result['status'] === 'success') {
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
            Log::error('Flutterwave refund failed: ' . $e->getMessage());
            
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
            ])->get($this->baseUrl . '/transactions/' . $paymentId . '/verify');

            $result = $response->json();

            if ($result['status'] === 'success') {
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
            Log::error('Flutterwave payment details fetch failed: ' . $e->getMessage());
            
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
            return $this->config['webhook_secret'] === $signature;
        } catch (Exception $e) {
            Log::error('Flutterwave webhook verification failed: ' . $e->getMessage());
            return false;
        }
    }
}
