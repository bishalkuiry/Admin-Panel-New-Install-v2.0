<?php

namespace App\Services\Payment;

use App\Models\Order;
use Exception;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected $gateways = [];

    public function __construct()
    {
        $this->registerGateways();
    }

    protected function registerGateways(): void
    {
        $this->gateways = [
            'razorpay' => new RazorpayGateway(),
            'paystack' => new PaystackGateway(),
            'stripe' => new StripeGateway(),
            'flutterwave' => new FlutterwaveGateway(),
            'paytm' => new PaytmGateway(),
            'phonepe' => new PhonePeGateway(),
        ];
    }

    public function getGateway(string $gateway): ?PaymentGatewayInterface
    {
        return $this->gateways[$gateway] ?? null;
    }

    public function getEnabledGateways(): array
    {
        $enabled = [];
        
        foreach ($this->gateways as $name => $gateway) {
            if ($gateway->isEnabled()) {
                $enabled[] = [
                    'name' => $name,
                    'display_name' => ucfirst($name),
                ];
            }
        }

        // Add COD if enabled
        if (config('payment.cod.enabled')) {
            $enabled[] = [
                'name' => 'cod',
                'display_name' => 'Cash on Delivery',
                'min_amount' => config('payment.cod.min_amount'),
                'max_amount' => config('payment.cod.max_amount'),
            ];
        }

        // Add Bank Transfer if enabled
        if (config('payment.bank_transfer.enabled')) {
            $enabled[] = [
                'name' => 'bank_transfer',
                'display_name' => 'Direct Bank Transfer',
                'bank_details' => [
                    'bank_name' => config('payment.bank_transfer.bank_name'),
                    'account_name' => config('payment.bank_transfer.account_name'),
                    'account_number' => config('payment.bank_transfer.account_number'),
                    'ifsc_code' => config('payment.bank_transfer.ifsc_code'),
                    'swift_code' => config('payment.bank_transfer.swift_code'),
                    'bank_code' => config('payment.bank_transfer.bank_code'),
                ],
            ];
        }

        return $enabled;
    }

    public function createPayment(Order $order, string $gateway, array $additionalData = []): array
    {
        try {
            // Handle COD
            if ($gateway === 'cod') {
                return $this->handleCOD($order);
            }

            // Handle Bank Transfer
            if ($gateway === 'bank_transfer') {
                return $this->handleBankTransfer($order);
            }

            // Handle online payment gateways
            $gatewayInstance = $this->getGateway($gateway);
            
            if (!$gatewayInstance || !$gatewayInstance->isEnabled()) {
                throw new Exception('Payment gateway not available');
            }

            $paymentData = [
                'order_number' => $order->order_number,
                'amount' => $order->total,
                'currency' => $additionalData['currency'] ?? 'INR',
                'email' => $order->user->email,
                'notes' => [
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                ],
            ];

            $result = $gatewayInstance->createOrder(array_merge($paymentData, $additionalData));

            if ($result['success']) {
                $order->update([
                    'payment_gateway' => $gateway,
                    'payment_id' => $result['order_id'] ?? $result['payment_intent_id'] ?? $result['reference'] ?? null,
                ]);
            }

            return $result;
        } catch (Exception $e) {
            Log::error('Payment creation failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function verifyPayment(Order $order, string $gateway, array $paymentData): bool
    {
        try {
            if ($gateway === 'cod' || $gateway === 'bank_transfer') {
                return true; // Manual verification
            }

            $gatewayInstance = $this->getGateway($gateway);
            
            if (!$gatewayInstance) {
                return false;
            }

            $verified = $gatewayInstance->verifyPayment($paymentData);

            if ($verified) {
                $order->update([
                    'payment_status' => 'paid',
                    'transaction_id' => $paymentData['transaction_id'] ?? $paymentData['razorpay_payment_id'] ?? $paymentData['reference'] ?? null,
                ]);
            }

            return $verified;
        } catch (Exception $e) {
            Log::error('Payment verification failed: ' . $e->getMessage());
            return false;
        }
    }

    public function refundPayment(Order $order, float $amount = null): array
    {
        try {
            $refundAmount = $amount ?? $order->total;

            if ($order->payment_gateway === 'cod' || $order->payment_gateway === 'bank_transfer') {
                return [
                    'success' => true,
                    'message' => 'Manual refund required',
                ];
            }

            $gatewayInstance = $this->getGateway($order->payment_gateway);
            
            if (!$gatewayInstance) {
                throw new Exception('Payment gateway not found');
            }

            return $gatewayInstance->refund($order->payment_id, $refundAmount);
        } catch (Exception $e) {
            Log::error('Refund failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    protected function handleCOD(Order $order): array
    {
        $minAmount = config('payment.cod.min_amount');
        $maxAmount = config('payment.cod.max_amount');

        if ($order->total < $minAmount || $order->total > $maxAmount) {
            return [
                'success' => false,
                'message' => "COD is available for orders between {$minAmount} and {$maxAmount}",
            ];
        }

        $order->update([
            'payment_method' => 'cod',
            'payment_gateway' => 'cod',
            'payment_status' => 'pending',
        ]);

        return [
            'success' => true,
            'message' => 'Cash on Delivery selected',
        ];
    }

    protected function handleBankTransfer(Order $order): array
    {
        $order->update([
            'payment_method' => 'bank_transfer',
            'payment_gateway' => 'bank_transfer',
            'payment_status' => 'pending',
        ]);

        return [
            'success' => true,
            'message' => 'Bank transfer details sent',
            'bank_details' => [
                'bank_name' => config('payment.bank_transfer.bank_name'),
                'account_name' => config('payment.bank_transfer.account_name'),
                'account_number' => config('payment.bank_transfer.account_number'),
                'ifsc_code' => config('payment.bank_transfer.ifsc_code'),
                'notes' => config('payment.bank_transfer.notes'),
            ],
        ];
    }
}
