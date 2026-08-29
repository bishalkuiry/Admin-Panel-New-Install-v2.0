<?php

namespace App\Exceptions;

use Exception;

class UnauthorizedWithdrawalException extends Exception
{
    private string $userRole;
    private string $details;

    public function __construct(string $userRole, string $details = '')
    {
        $this->userRole = $userRole;
        $this->details = $details;

        $message = "Unauthorized withdrawal request for role: {$userRole}";
        if ($details) {
            $message .= " - {$details}";
        }

        parent::__construct($message);
    }

    public function getUserRole(): string
    {
        return $this->userRole;
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
            'error' => 'Unauthorized withdrawal request',
            'user_role' => $this->userRole,
            'details' => $this->details ?: 'Withdrawals are only available for sellers and delivery partners',
        ], 403);
    }
}
