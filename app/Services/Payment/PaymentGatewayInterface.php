<?php

namespace App\Services\Payment;

interface PaymentGatewayInterface
{
    /**
     * Create a payment order
     */
    public function createOrder(array $data): array;

    /**
     * Verify payment
     */
    public function verifyPayment(array $data): bool;

    /**
     * Process refund
     */
    public function refund(string $paymentId, float $amount): array;

    /**
     * Get payment details
     */
    public function getPaymentDetails(string $paymentId): array;

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool;

    /**
     * Check if gateway is enabled
     */
    public function isEnabled(): bool;
}
