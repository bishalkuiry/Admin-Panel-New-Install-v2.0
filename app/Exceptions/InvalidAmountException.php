<?php

namespace App\Exceptions;

use Exception;

class InvalidAmountException extends Exception
{
    protected $amount;
    protected $details;

    public function __construct(float $amount, string $details)
    {
        $this->amount = $amount;
        $this->details = $details;
        
        parent::__construct(
            "Invalid amount: {$amount}. {$details}"
        );
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getDetails(): string
    {
        return $this->details;
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Invalid amount',
                'details' => $this->details,
            ], 400);
        }

        return response()->view('errors.invalid-amount', [
            'amount' => $this->amount,
            'details' => $this->details,
        ], 400);
    }
}
