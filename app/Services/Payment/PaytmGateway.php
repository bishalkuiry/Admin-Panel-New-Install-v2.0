<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class PaytmGateway implements PaymentGatewayInterface
{
    protected $config;
    protected $baseUrl;

    public function __construct()
    {
        $this->config = config('payment.gateways.paytm');
        $this->baseUrl = $this->config['environment'] === 'production'
            ? 'https://securegw.paytm.in'
            : 'https://securegw-stage.paytm.in';
    }

    public function createOrder(array $data): array
    {
        try {
            $orderId = $data['order_number'];
            $amount = $data['amount'];

            $paytmParams = [
                'body' => [
                    'requestType' => 'Payment',
                    'mid' => $this->config['merchant_id'],
                    'websiteName' => $this->config['website'],
                    'orderId' => $orderId,
                    'txnAmount' => [
                        'value' => $amount,
                        'currency' => 'INR',
                    ],
                    'userInfo' => [
                        'custId' => $data['user_id'] ?? 'CUST_' . time(),
                    ],
                    'callbackUrl' => $data['callback_url'] ?? url('/api/v1/payment/paytm/callback'),
                ],
            ];

            $checksum = $this->generateChecksum(json_encode($paytmParams['body']));

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/theia/api/v1/initiateTransaction?mid=' . $this->config['merchant_id'] . '&orderId=' . $orderId, [
                'body' => $paytmParams['body'],
                'head' => [
                    'signature' => $checksum,
                ],
            ]);

            $result = $response->json();

            if (isset($result['body']['txnToken'])) {
                return [
                    'success' => true,
                    'txn_token' => $result['body']['txnToken'],
                    'order_id' => $orderId,
                    'amount' => $amount,
                    'mid' => $this->config['merchant_id'],
                ];
            }

            return [
                'success' => false,
                'message' => $result['body']['resultInfo']['resultMsg'] ?? 'Payment initialization failed',
            ];
        } catch (Exception $e) {
            Log::error('Paytm order creation failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function verifyPayment(array $data): bool
    {
        try {
            $orderId = $data['order_id'];

            $paytmParams = [
                'body' => [
                    'mid' => $this->config['merchant_id'],
                    'orderId' => $orderId,
                ],
            ];

            $checksum = $this->generateChecksum(json_encode($paytmParams['body']));

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/v3/order/status', [
                'body' => $paytmParams['body'],
                'head' => [
                    'signature' => $checksum,
                ],
            ]);

            $result = $response->json();

            return isset($result['body']['resultInfo']['resultStatus']) && 
                   $result['body']['resultInfo']['resultStatus'] === 'TXN_SUCCESS';
        } catch (Exception $e) {
            Log::error('Paytm payment verification failed: ' . $e->getMessage());
            return false;
        }
    }

    public function refund(string $paymentId, float $amount): array
    {
        try {
            $refundId = 'REFUND_' . time();

            $paytmParams = [
                'body' => [
                    'mid' => $this->config['merchant_id'],
                    'txnType' => 'REFUND',
                    'orderId' => $paymentId,
                    'txnId' => $paymentId,
                    'refId' => $refundId,
                    'refundAmount' => $amount,
                ],
            ];

            $checksum = $this->generateChecksum(json_encode($paytmParams['body']));

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/refund/apply', [
                'body' => $paytmParams['body'],
                'head' => [
                    'signature' => $checksum,
                ],
            ]);

            $result = $response->json();

            if (isset($result['body']['resultInfo']['resultStatus']) && 
                $result['body']['resultInfo']['resultStatus'] === 'TXN_SUCCESS') {
                return [
                    'success' => true,
                    'refund_id' => $refundId,
                    'status' => 'success',
                ];
            }

            return [
                'success' => false,
                'message' => $result['body']['resultInfo']['resultMsg'] ?? 'Refund failed',
            ];
        } catch (Exception $e) {
            Log::error('Paytm refund failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function getPaymentDetails(string $paymentId): array
    {
        try {
            $paytmParams = [
                'body' => [
                    'mid' => $this->config['merchant_id'],
                    'orderId' => $paymentId,
                ],
            ];

            $checksum = $this->generateChecksum(json_encode($paytmParams['body']));

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/v3/order/status', [
                'body' => $paytmParams['body'],
                'head' => [
                    'signature' => $checksum,
                ],
            ]);

            $result = $response->json();

            if (isset($result['body'])) {
                return [
                    'success' => true,
                    'data' => $result['body'],
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to fetch payment details',
            ];
        } catch (Exception $e) {
            Log::error('Paytm payment details fetch failed: ' . $e->getMessage());
            
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
               !empty($this->config['merchant_key']);
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        try {
            // Paytm webhook verification uses checksum
            return $this->generateChecksum($payload) === $signature;
        } catch (Exception $e) {
            Log::error('Paytm webhook verification failed: ' . $e->getMessage());
            return false;
        }
    }

    protected function generateChecksum(string $body): string
    {
        return hash_hmac('sha256', $body, $this->config['merchant_key']);
    }
}
