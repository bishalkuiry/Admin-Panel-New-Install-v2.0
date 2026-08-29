<?php

namespace App\Exceptions;

use Exception;

class InsufficientBalanceException extends Exception
{
    protected $required;
    protected $available;

    public function __construct(float $required, float $available)
    {
        $this->required = $required;
        $this->available = $available;
        
        parent::__construct(
            "Insufficient wallet balance. Required: {$required}, Available: {$available}"
        );
    }

    public function getRequired(): float
    {
        return $this->required;
    }

    public function getAvailable(): float
    {
        return $this->available;
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Insufficient wallet balance',
                'required' => $this->required,
                'available' => $this->available,
            ], 400);
        }

        return response()->view('errors.insufficient-balance', [
            'required' => $this->required,
            'available' => $this->available,
        ], 400);
    }
}
