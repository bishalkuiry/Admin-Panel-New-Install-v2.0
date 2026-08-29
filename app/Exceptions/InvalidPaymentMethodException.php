<?php

namespace App\Exceptions;

use Exception;

class InvalidPaymentMethodException extends Exception
{
    private string $paymentMethod;
    private string $details;

    public function __construct(string $paymentMethod, string $details = '')
    {
        $this->paymentMethod = $paymentMethod;
        $this->details = $details;

        $message = "Invalid payment method: {$paymentMethod}";
        if ($details) {
            $message .= " - {$details}";
        }

        parent::__construct($message);
    }

    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    public function getDetails(): string
    {
        return $this->details;
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render()
    {
        return response()->json([
            'error' => 'Invalid payment method',
            'payment_method' => $this->paymentMethod,
            'details' => $this->details ?: 'This payment method cannot be used for wallet operations',
        ], 400);
    }
}
