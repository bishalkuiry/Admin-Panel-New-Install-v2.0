<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class PhonePeGateway implements PaymentGatewayInterface
{
    protected $config;
    protected $baseUrl;

    public function __construct()
    {
        $this->config = config('payment.gateways.phonepe');
        $this->baseUrl = $this->config['environment'] === 'production'
            ? 'https://api.phonepe.com/apis/hermes'
            : 'https://api-preprod.phonepe.com/apis/pg-sandbox';
    }

    public function createOrder(array $data): array
    {
        try {
            $merchantTransactionId = $data['order_number'];
            $amount = $data['amount'] * 100; // Convert to paise

            $payload = [
                'merchantId' => $this->config['merchant_id'],
                'merchantTransactionId' => $merchantTransactionId,
                'merchantUserId' => 'MUID' . $data['user_id'] ?? time(),
                'amount' => $amount,
                'redirectUrl' => $data['callback_url'] ?? url('/api/v1/payment/phonepe/callback'),
                'redirectMode' => 'POST',
                'callbackUrl' => $data['callback_url'] ?? url('/api/v1/payment/phonepe/callback'),
                'mobileNumber' => $data['mobile'] ?? '',
                'paymentInstrument' => [
                    'type' => 'PAY_PAGE',
                ],
            ];

            $base64Payload = base64_encode(json_encode($payload));
            $checksum = $this->generateChecksum($base64Payload);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-VERIFY' => $checksum,
            ])->post($this->baseUrl . '/pg/v1/pay', [
                'request' => $base64Payload,
            ]);

            $result = $response->json();

            if ($result['success']) {
                return [
                    'success' => true,
                    'payment_url' => $result['data']['instrumentResponse']['redirectInfo']['url'],
                    'merchant_transaction_id' => $merchantTransactionId,
                ];
            }

            return [
                'success' => false,
                'message' => $result['message'] ?? 'Payment initialization failed',
            ];
        } catch (Exception $e) {
            Log::error('PhonePe order creation failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function verifyPayment(array $data): bool
    {
        try {
            $merchantTransactionId = $data['merchant_transaction_id'];
            $statusPath = '/pg/v1/status/' . $this->config['merchant_id'] . '/' . $merchantTransactionId;
            $checksum = $this->generateChecksum($statusPath, $statusPath);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-VERIFY' => $checksum,
                'X-MERCHANT-ID' => $this->config['merchant_id'],
            ])->get($this->baseUrl . $statusPath);

            $result = $response->json();

            return $result['success'] && $result['data']['state'] === 'COMPLETED';
        } catch (Exception $e) {
            Log::error('PhonePe payment verification failed: ' . $e->getMessage());
            return false;
        }
    }

    public function refund(string $paymentId, float $amount): array
    {
        try {
            $refundId = 'REFUND_' . time();
            $refundAmount = $amount * 100; // Convert to paise

            $payload = [
                'merchantId' => $this->config['merchant_id'],
                'merchantTransactionId' => $refundId,
                'originalTransactionId' => $paymentId,
                'amount' => $refundAmount,
                'callbackUrl' => url('/api/v1/payment/phonepe/refund-callback'),
            ];

            $base64Payload = base64_encode(json_encode($payload));
            $checksum = $this->generateChecksum($base64Payload);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-VERIFY' => $checksum,
            ])->post($this->baseUrl . '/pg/v1/refund', [
                'request' => $base64Payload,
            ]);

            $result = $response->json();

            if ($result['success']) {
                return [
                    'success' => true,
                    'refund_id' => $refundId,
                    'status' => $result['data']['state'],
                ];
            }

            return [
                'success' => false,
                'message' => $result['message'] ?? 'Refund failed',
            ];
        } catch (Exception $e) {
            Log::error('PhonePe refund failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function getPaymentDetails(string $paymentId): array
    {
        try {
            $statusPath = '/pg/v1/status/' . $this->config['merchant_id'] . '/' . $paymentId;
            $checksum = $this->generateChecksum($statusPath, $statusPath);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-VERIFY' => $checksum,
                'X-MERCHANT-ID' => $this->config['merchant_id'],
            ])->get($this->baseUrl . $statusPath);

            $result = $response->json();

            if ($result['success']) {
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
            Log::error('PhonePe payment details fetch failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function isEnabled(): bool
    {
        return $this->config['enabled'] && 
               !empty($this->config['merchant_id']) && 
               !empty($this->config['salt_key']);
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        try {
            // PhonePe webhook verification uses checksum
            return $this->generateChecksum($payload, '') === $signature;
        } catch (Exception $e) {
            Log::error('PhonePe webhook verification failed: ' . $e->getMessage());
            return false;
        }
    }

    protected function generateChecksum(string $data, string $apiEndpoint = '/pg/v1/pay'): string
    {
        $saltKey = $this->config['salt_key'];
        $saltIndex = $this->config['salt_index'];

        return hash('sha256', $data . $apiEndpoint . $saltKey) . '###' . $saltIndex;
    }
}
