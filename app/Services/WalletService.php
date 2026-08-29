<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WalletWithdrawal;
use App\Models\Order;
use App\Models\Setting;
use App\Exceptions\InvalidAmountException;
use App\Exceptions\InvalidPaymentMethodException;
use App\Exceptions\UnauthorizedWithdrawalException;
use App\Exceptions\InsufficientBalanceException;
use App\Services\Payment\PaymentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletService
{
    public function __construct(
        private RealtimeService $realtimeService,
        private PaymentService $paymentService
    ) {}

    /**
     * Create a wallet for a new user
     *
     * @param User $user The user to create a wallet for
     * @return Wallet The created wallet
     */
    public function createWallet(User $user): Wallet
    {
        return DB::transaction(function () use ($user) {
            // Create wallet with zero balance
            $wallet = Wallet::create([
                'user_id' => $user->id,
                'balance' => 0.00,
                'currency' => config('app.currency') ?? 'INR',
            ]);

            return $wallet;
        });
    }

    /**
     * Get or create wallet for user (lazy creation)
     *
     * @param User $user The user to get or create wallet for
     * @return Wallet The user's wallet
     */
    public function getOrCreateWallet(User $user): Wallet
    {
        // Try to get existing wallet
        $wallet = Wallet::where('user_id', $user->id)->first();

        // If wallet doesn't exist, create it
        if (!$wallet) {
            $wallet = $this->createWallet($user);
        }

        return $wallet;
    }

    /**
     * Credit wallet balance with transaction recording
     *
     * @param Wallet $wallet The wallet to credit
     * @param float $amount Amount to credit
     * @param string $type Transaction type
     * @param string $description Transaction description
     * @param array|null $metadata Additional metadata
     * @param User|null $createdBy User who created this transaction (for admin operations)
     * @return WalletTransaction The created transaction record
     * @throws InvalidAmountException If the amount is invalid
     */
    public function creditBalance(
        Wallet $wallet,
        float $amount,
        string $type,
        string $description,
        ?array $metadata = null,
        ?User $createdBy = null
    ): WalletTransaction {
        // Validate amount before processing
        $this->validateAmount($amount);

        return DB::transaction(function () use ($wallet, $amount, $type, $description, $metadata, $createdBy) {
            // Lock the wallet row for update to prevent concurrent modifications
            $lockedWallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();

            // Record balance before
            $balanceBefore = $lockedWallet->balance;

            // Update balance
            $lockedWallet->balance += $amount;
            $lockedWallet->save();

            // Create transaction record
            $transaction = $lockedWallet->transactions()->create([
                'type' => $type,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $lockedWallet->balance,
                'description' => $description,
                'metadata' => $metadata,
                'created_by' => $createdBy?->id,
            ]);

            return $transaction;
        });
    }

    /**
     * Debit wallet balance with transaction recording
     *
     * @param Wallet $wallet The wallet to debit
     * @param float $amount Amount to debit
     * @param string $type Transaction type
     * @param string $description Transaction description
     * @param array|null $metadata Additional metadata
     * @param User|null $createdBy User who created this transaction (for admin operations)
     * @param bool $allowNegative Allow negative balance (for admin operations)
     * @return WalletTransaction The created transaction record
     * @throws InvalidAmountException If the amount is invalid
     * @throws \App\Exceptions\InsufficientBalanceException If balance is insufficient and allowNegative is false
     */
    public function debitBalance(
        Wallet $wallet,
        float $amount,
        string $type,
        string $description,
        ?array $metadata = null,
        ?User $createdBy = null,
        bool $allowNegative = false
    ): WalletTransaction {
        // Validate amount before processing
        $this->validateAmount($amount);

        return DB::transaction(function () use ($wallet, $amount, $type, $description, $metadata, $createdBy, $allowNegative) {
            // Lock the wallet row for update to prevent concurrent modifications
            $lockedWallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();

            // Check if sufficient balance (unless allowNegative is true)
            if (!$allowNegative && !$lockedWallet->hasBalance($amount)) {
                throw new \App\Exceptions\InsufficientBalanceException($amount, $lockedWallet->balance);
            }

            // Record balance before
            $balanceBefore = $lockedWallet->balance;

            // Update balance
            $lockedWallet->balance -= $amount;
            $lockedWallet->save();

            // Create transaction record
            $transaction = $lockedWallet->transactions()->create([
                'type' => $type,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $lockedWallet->balance,
                'description' => $description,
                'metadata' => $metadata,
                'created_by' => $createdBy?->id,
            ]);

            return $transaction;
        });
    }

    /**
     * Validate amount for wallet operations.
     *
     * @throws InvalidAmountException
     */
    private function validateAmount(float $amount): void
    {
        if ($amount <= 0) {
            throw new InvalidAmountException(
                $amount,
                'Amount must be positive and greater than zero'
            );
        }

        if (round($amount, 2) != $amount) {
            throw new InvalidAmountException(
                $amount,
                'Amount must have maximum 2 decimal places'
            );
        }

        $maxLimit = 1000000.00;
        if ($amount > $maxLimit) {
            throw new InvalidAmountException(
                $amount,
                "Amount must not exceed {$maxLimit}"
            );
        }
    }

    /**
     * Apply signup bonus to new user
     *
     * Checks if signup bonus is enabled and if the user is a customer.
     * If both conditions are met, credits the configured bonus amount to the user's wallet.
     *
     * @param User $user The user to apply signup bonus to
     * @return WalletTransaction|null The transaction record if bonus was applied, null otherwise
     */
    public function applySignupBonus(User $user): ?WalletTransaction
    {
        // Check if signup bonus is enabled
        $signupBonusEnabled = Setting::get('wallet_signup_bonus_enabled', false);
        if (!$signupBonusEnabled || $signupBonusEnabled === '0') {
            return null;
        }

        // Check if user is a customer (only customers get signup bonus)
        // Handle both enum and string values, and null case
        $userRole = $user->role;
        if ($userRole === null) {
            // If role is null, assume customer (default role)
            $isCustomer = true;
        } elseif (is_string($userRole)) {
            $isCustomer = $userRole === 'customer';
        } else {
            // UserRole enum
            $isCustomer = $userRole->value === 'customer';
        }

        if (!$isCustomer) {
            return null;
        }

        // Get signup bonus amount
        $bonusAmount = (float) Setting::get('wallet_signup_bonus_amount', 0);
        if ($bonusAmount <= 0) {
            return null;
        }

        // Get or create wallet for the user
        $wallet = $this->getOrCreateWallet($user);

        // Credit the signup bonus
        $transaction = $this->creditBalance(
            $wallet,
            $bonusAmount,
            WalletTransaction::TYPE_SIGNUP_BONUS,
            'Signup bonus credited to your wallet',
            ['reason' => 'New user registration bonus'],
            null // No admin user for signup bonus
        );

        // Send notification to user
        $this->sendTransactionNotification($user, $transaction);

        return $transaction;
    }

    /**
     * Process wallet payment for an order
     *
     * Handles full and partial wallet payments for orders.
     * Validates that wallet is not combined with COD or Bank Transfer.
     * Uses database transactions and locking for data integrity.
     *
     * @param User $user The user making the payment
     * @param Order $order The order being paid for
     * @param float $orderTotal The total amount of the order
     * @param bool $useWallet Whether to use wallet for payment
     * @param string $paymentMethod The payment method being used
     * @return array Array with 'wallet_amount' and 'remaining_amount'
     * @throws InvalidPaymentMethodException If wallet is combined with COD or Bank Transfer
     * @throws InvalidAmountException If the order total is invalid
     */
    public function processOrderPayment(
        User $user,
        Order $order,
        float $orderTotal,
        bool $useWallet,
        string $paymentMethod
    ): array {
        // Validate order total
        $this->validateAmount($orderTotal);

        // If wallet is not being used, return full amount as remaining
        if (!$useWallet) {
            return [
                'wallet_amount' => 0.00,
                'remaining_amount' => $orderTotal,
            ];
        }

        // Get or create wallet for the user
        $wallet = $this->getOrCreateWallet($user);

        // Process payment within a database transaction
        return DB::transaction(function () use ($wallet, $order, $orderTotal, $user) {
            // Lock the wallet row for update to prevent concurrent modifications
            $lockedWallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();

            $walletBalance = $lockedWallet->balance;

            // Case 1: Wallet balance covers full order amount
            if ($walletBalance >= $orderTotal) {
                // Deduct full amount from wallet
                $this->debitBalance(
                    $lockedWallet,
                    $orderTotal,
                    WalletTransaction::TYPE_ORDER_PAYMENT,
                    "Payment for order #{$order->order_number}",
                    [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'order_total' => $orderTotal,
                    ],
                    null, // No admin user for order payments
                    false // Don't allow negative balance
                );

                // Send notification to user
                $this->sendTransactionNotification($user, $lockedWallet->transactions()->latest()->first());

                return [
                    'wallet_amount' => $orderTotal,
                    'remaining_amount' => 0.00,
                ];
            }

            // Case 2: Wallet balance is less than order amount (partial payment)
            if ($walletBalance > 0) {
                // Deduct full wallet balance
                $this->debitBalance(
                    $lockedWallet,
                    $walletBalance,
                    WalletTransaction::TYPE_ORDER_PAYMENT,
                    "Partial payment for order #{$order->order_number}",
                    [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'order_total' => $orderTotal,
                        'partial_payment' => true,
                    ],
                    null, // No admin user for order payments
                    false // Don't allow negative balance
                );

                // Send notification to user
                $this->sendTransactionNotification($user, $lockedWallet->transactions()->latest()->first());

                return [
                    'wallet_amount' => $walletBalance,
                    'remaining_amount' => $orderTotal - $walletBalance,
                ];
            }

            // Case 3: Wallet balance is zero
            return [
                'wallet_amount' => 0.00,
                'remaining_amount' => $orderTotal,
            ];
        });
    }

    /**
     * Send notification for wallet transaction
     *
     * @param User $user The user to notify
     * @param WalletTransaction $transaction The transaction to notify about
     * @return void
     */
    private function sendTransactionNotification(User $user, WalletTransaction $transaction): void
    {
        try {
            $this->realtimeService->notifyUser(
                $user->id,
                'wallet-transaction',
                [
                    'title' => 'Wallet Transaction',
                    'message' => $this->getTransactionNotificationMessage($transaction),
                    'type' => 'wallet',
                    'transaction_id' => $transaction->id,
                    'transaction_type' => $transaction->type,
                    'amount' => $transaction->amount,
                    'balance' => $transaction->balance_after,
                ]
            );
        } catch (\Exception $e) {
            // Log the error but don't block the transaction
            Log::error('Failed to send wallet transaction notification', [
                'user_id' => $user->id,
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Initiate wallet top-up via payment gateway
     *
     * Validates the top-up amount against configured min/max limits.
     * Rejects COD and Bank Transfer payment methods.
     * Creates a payment intent with type "wallet_top_up" using PaymentService.
     *
     * @param User $user The user requesting the top-up
     * @param float $amount The amount to top-up
     * @param string $paymentGateway The payment gateway to use
     * @return array Array with payment intent data (payment_url, transaction_id, etc.)
     * @throws InvalidAmountException If the amount is invalid or outside min/max limits
     * @throws InvalidPaymentMethodException If COD or Bank Transfer is used
     */
    public function initiateTopUp(
        User $user,
        float $amount,
        string $paymentGateway
    ): array {
        // Validate basic amount format (positive, 2 decimals, max limit)
        $this->validateAmount($amount);

        // Reject COD and Bank Transfer payment methods
        $invalidMethods = ['cod', 'bank_transfer', 'COD', 'Bank Transfer', 'bank transfer'];
        if (in_array($paymentGateway, $invalidMethods, true)) {
            throw new InvalidPaymentMethodException(
                $paymentGateway,
                'COD and Bank Transfer cannot be used for wallet top-up'
            );
        }

        // Get min and max top-up amounts from settings
        $minTopUpAmount = (float) Setting::get('wallet_min_top_up_amount', 10.00);
        $maxTopUpAmount = (float) Setting::get('wallet_max_top_up_amount', 50000.00);

        // Validate amount against min limit
        if ($amount < $minTopUpAmount) {
            throw new InvalidAmountException(
                $amount,
                "Top-up amount must be at least ₹{$minTopUpAmount}"
            );
        }

        // Validate amount against max limit
        if ($amount > $maxTopUpAmount) {
            throw new InvalidAmountException(
                $amount,
                "Top-up amount must not exceed ₹{$maxTopUpAmount}"
            );
        }

        // Get or create wallet for the user
        $wallet = $this->getOrCreateWallet($user);

        // Create payment intent using PaymentService
        // We create a temporary order-like structure for the payment
        $paymentData = [
            'order_number' => 'WALLET_TOPUP_' . time() . '_' . $user->id,
            'amount' => $amount,
            'currency' => config('app.currency') ?? 'INR',
            'email' => $user->email,
            'notes' => [
                'type' => 'wallet_top_up',
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'amount' => $amount,
            ],
        ];

        // Get the payment gateway instance
        $gatewayInstance = $this->paymentService->getGateway($paymentGateway);
        
        if (!$gatewayInstance || !$gatewayInstance->isEnabled()) {
            throw new \Exception('Payment gateway not available');
        }

        // Create payment order
        $result = $gatewayInstance->createOrder($paymentData);

        if (!$result['success']) {
            throw new \Exception($result['message'] ?? 'Failed to create payment intent');
        }

        // Return payment intent data with metadata for callback verification
        return [
            'success' => true,
            'payment_url' => $result['payment_url'] ?? null,
            'transaction_id' => $result['order_id'] ?? $result['payment_intent_id'] ?? $result['reference'] ?? null,
            'gateway' => $paymentGateway,
            'amount' => $amount,
            'currency' => $paymentData['currency'],
            // Include all gateway-specific data (like key_id for Razorpay)
            'gateway_data' => $result,
            'metadata' => [
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'type' => 'wallet_top_up',
            ],
        ];
    }

    /**
     * Complete top-up after payment confirmation
     *
     * This method is called by payment gateway callbacks/webhooks after successful payment.
     * It verifies the payment and credits the wallet balance.
     * Uses idempotency check to prevent duplicate credits.
     *
     * @param User $user The user who initiated the top-up
     * @param float $amount The amount to credit
     * @param string $paymentGatewayTransactionId The payment gateway transaction ID
     * @param array $paymentData Additional payment data (for verification)
     * @return WalletTransaction The created transaction record
     * @throws InvalidAmountException If the amount is invalid
     * @throws \Exception If payment verification fails or duplicate transaction detected
     */
    public function completeTopUp(
        User $user,
        float $amount,
        string $paymentGatewayTransactionId,
        array $paymentData = []
    ): WalletTransaction {
        // Validate amount
        $this->validateAmount($amount);

        // Get or create wallet for the user
        $wallet = $this->getOrCreateWallet($user);

        return DB::transaction(function () use ($wallet, $user, $amount, $paymentGatewayTransactionId, $paymentData) {
            // Idempotency check: Check if transaction with same gateway transaction ID already exists
            $existingTransaction = WalletTransaction::where('wallet_id', $wallet->id)
                ->where('type', WalletTransaction::TYPE_TOP_UP)
                ->where('metadata->gateway_transaction_id', $paymentGatewayTransactionId)
                ->first();

            if ($existingTransaction) {
                // Transaction already processed, return existing transaction
                // This prevents duplicate credits from webhook retries
                Log::warning('Duplicate top-up attempt detected', [
                    'user_id' => $user->id,
                    'wallet_id' => $wallet->id,
                    'gateway_transaction_id' => $paymentGatewayTransactionId,
                    'existing_transaction_id' => $existingTransaction->id,
                ]);

                return $existingTransaction;
            }

            // Verify amount matches expected amount (if provided in payment data)
            if (isset($paymentData['amount']) && abs($paymentData['amount'] - $amount) > 0.01) {
                throw new \Exception(
                    "Payment amount mismatch. Expected: {$amount}, Received: {$paymentData['amount']}"
                );
            }

            // Credit wallet balance with transaction type "top_up"
            $transaction = $this->creditBalance(
                $wallet,
                $amount,
                WalletTransaction::TYPE_TOP_UP,
                "Wallet top-up via payment gateway",
                [
                    'gateway_transaction_id' => $paymentGatewayTransactionId,
                    'payment_gateway' => $paymentData['gateway'] ?? 'unknown',
                    'payment_method' => $paymentData['payment_method'] ?? null,
                    'payment_status' => $paymentData['status'] ?? 'completed',
                    'payment_data' => $paymentData,
                ],
                null // No admin user for top-ups
            );

            // Send notification to user
            $this->sendTransactionNotification($user, $transaction);

            // Log successful top-up
            Log::info('Wallet top-up completed successfully', [
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'gateway_transaction_id' => $paymentGatewayTransactionId,
                'transaction_id' => $transaction->id,
            ]);

            return $transaction;
        });
    }

    /**
     * Process refund to wallet
     *
     * Credits the wallet with a refund amount for a cancelled order.
     * Handles both full and partial payment refunds (only refunds wallet portion).
     * Stores original order ID and order number as reference.
     *
     * @param User $user The user to refund
     * @param Order $order The order being refunded
     * @param float $amount The amount to refund (wallet portion only)
     * @return WalletTransaction The created transaction record
     * @throws InvalidAmountException If the amount is invalid
     */
    public function processRefund(
        User $user,
        Order $order,
        float $amount
    ): WalletTransaction {
        // Validate amount
        $this->validateAmount($amount);

        // Get or create wallet for the user
        $wallet = $this->getOrCreateWallet($user);

        // Credit wallet balance with transaction type "refund"
        $transaction = $this->creditBalance(
            $wallet,
            $amount,
            WalletTransaction::TYPE_REFUND,
            "Refund for order #{$order->order_number}",
            [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'refund_amount' => $amount,
                'order_total' => $order->total,
                'refund_reason' => $order->cancellation_reason ?? 'Order cancelled',
            ],
            null // No admin user for refunds
        );

        // Send notification to user
        $this->sendTransactionNotification($user, $transaction);

        // Log successful refund
        Log::info('Wallet refund processed successfully', [
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'refund_amount' => $amount,
            'transaction_id' => $transaction->id,
        ]);

        return $transaction;
    }

    /**
     * Request withdrawal for seller/delivery boy
     *
     * Validates user role (only sellers and delivery_boys can withdraw).
     * Validates amount against wallet_min_withdrawal_amount setting.
     * Checks sufficient balance.
     * Deducts amount immediately using debitBalance with TYPE_WITHDRAWAL.
     * Creates WalletWithdrawal record with status pending.
     * Stores bank details: bank_name, account_number, account_holder_name, ifsc_code.
     *
     * @param User $user The user requesting the withdrawal
     * @param float $amount The amount to withdraw
     * @param array $bankDetails Bank account details (bank_name, account_number, account_holder_name, ifsc_code)
     * @return WalletWithdrawal The created withdrawal record
     * @throws UnauthorizedWithdrawalException If user role is not seller or delivery_boy
     * @throws InvalidAmountException If the amount is invalid or below minimum
     * @throws InsufficientBalanceException If wallet balance is insufficient
     */
    public function requestWithdrawal(
        User $user,
        float $amount,
        array $bankDetails
    ): WalletWithdrawal {
        // Validate user role: only sellers and delivery_boys can withdraw
        $userRole = $user->role;
        
        // Handle both enum and string values, and null case
        if ($userRole === null) {
            $roleValue = 'customer'; // Default role
        } elseif (is_string($userRole)) {
            $roleValue = $userRole;
        } else {
            // UserRole enum
            $roleValue = $userRole->value;
        }

        // Check if user is seller (store_owner) or delivery_boy (delivery_partner)
        $allowedRoles = ['store_owner', 'delivery_partner'];
        if (!in_array($roleValue, $allowedRoles, true)) {
            throw new UnauthorizedWithdrawalException(
                $roleValue,
                'Only sellers and delivery partners can request withdrawals'
            );
        }

        // Validate basic amount format (positive, 2 decimals, max limit)
        $this->validateAmount($amount);

        // Get minimum withdrawal amount from settings
        $minWithdrawalAmount = (float) Setting::get('wallet_min_withdrawal_amount', 100.00);

        // Validate amount against minimum limit
        if ($amount < $minWithdrawalAmount) {
            throw new InvalidAmountException(
                $amount,
                "Withdrawal amount must be at least ₹{$minWithdrawalAmount}"
            );
        }

        // Get or create wallet for the user
        $wallet = $this->getOrCreateWallet($user);

        // Process withdrawal within a database transaction
        return DB::transaction(function () use ($wallet, $user, $amount, $bankDetails) {
            // Deduct amount immediately from wallet using debitBalance
            // This creates a transaction record and locks the wallet
            $transaction = $this->debitBalance(
                $wallet,
                $amount,
                WalletTransaction::TYPE_WITHDRAWAL,
                "Withdrawal request - pending approval",
                [
                    'bank_name' => $bankDetails['bank_name'] ?? '',
                    'account_number' => $bankDetails['account_number'] ?? '',
                    'account_holder_name' => $bankDetails['account_holder_name'] ?? '',
                    'ifsc_code' => $bankDetails['ifsc_code'] ?? null,
                    'status' => 'pending',
                ],
                null, // No admin user for withdrawal requests
                false // Don't allow negative balance
            );

            // Create WalletWithdrawal record with status pending
            $withdrawal = WalletWithdrawal::create([
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'bank_name' => $bankDetails['bank_name'] ?? '',
                'account_number' => $bankDetails['account_number'] ?? '',
                'account_holder_name' => $bankDetails['account_holder_name'] ?? '',
                'ifsc_code' => $bankDetails['ifsc_code'] ?? null,
                'status' => WalletWithdrawal::STATUS_PENDING,
                'notes' => null,
                'processed_by' => null,
                'processed_at' => null,
            ]);

            // Log successful withdrawal request
            Log::info('Withdrawal request created', [
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'withdrawal_id' => $withdrawal->id,
                'transaction_id' => $transaction->id,
                'amount' => $amount,
                'balance_after' => $transaction->balance_after,
            ]);

            return $withdrawal;
        });
    }

    /**
     * Approve withdrawal request (admin only)
     *
     * Updates withdrawal status to approved.
     * Sets processed_by to admin user ID.
     * Sets processed_at to current timestamp.
     * Sends notification to user about approval.
     * Logs the approval.
     *
     * @param WalletWithdrawal $withdrawal The withdrawal to approve
     * @param User $admin The admin user approving the withdrawal
     * @return void
     */
    public function approveWithdrawal(WalletWithdrawal $withdrawal, User $admin): void
    {
        DB::transaction(function () use ($withdrawal, $admin) {
            // Update withdrawal status to approved
            $withdrawal->status = WalletWithdrawal::STATUS_APPROVED;
            $withdrawal->processed_by = $admin->id;
            $withdrawal->processed_at = now();
            $withdrawal->save();

            // Get the user who requested the withdrawal
            $user = $withdrawal->wallet->user;

            // Send notification to user about approval
            $this->sendWithdrawalNotification($user, $withdrawal, 'approved');

            // Log the approval
            Log::info('Withdrawal approved', [
                'withdrawal_id' => $withdrawal->id,
                'user_id' => $user->id,
                'wallet_id' => $withdrawal->wallet_id,
                'amount' => $withdrawal->amount,
                'processed_by' => $admin->id,
                'processed_at' => $withdrawal->processed_at,
            ]);
        });
    }

    /**
     * Reject withdrawal request (admin only)
     *
     * Refunds amount to wallet using creditBalance with TYPE_WITHDRAWAL_REVERSAL.
     * Updates withdrawal status to rejected.
     * Sets processed_by to admin user ID.
     * Sets processed_at to current timestamp.
     * Stores rejection reason in notes field.
     * Sends notification to user about rejection with reason.
     * Logs the rejection.
     *
     * @param WalletWithdrawal $withdrawal The withdrawal to reject
     * @param User $admin The admin user rejecting the withdrawal
     * @param string $reason The reason for rejection
     * @return void
     */
    public function rejectWithdrawal(WalletWithdrawal $withdrawal, User $admin, string $reason): void
    {
        DB::transaction(function () use ($withdrawal, $admin, $reason) {
            // Get the wallet
            $wallet = $withdrawal->wallet;

            // Refund amount to wallet using creditBalance with TYPE_WITHDRAWAL_REVERSAL
            $transaction = $this->creditBalance(
                $wallet,
                $withdrawal->amount,
                WalletTransaction::TYPE_WITHDRAWAL_REVERSAL,
                "Withdrawal rejected - {$reason}",
                [
                    'withdrawal_id' => $withdrawal->id,
                    'rejection_reason' => $reason,
                    'processed_by' => $admin->id,
                ],
                $admin // Admin user who rejected the withdrawal
            );

            // Update withdrawal status to rejected
            $withdrawal->status = WalletWithdrawal::STATUS_REJECTED;
            $withdrawal->processed_by = $admin->id;
            $withdrawal->processed_at = now();
            $withdrawal->notes = $reason;
            $withdrawal->save();

            // Get the user who requested the withdrawal
            $user = $wallet->user;

            // Send notification to user about rejection with reason
            $this->sendWithdrawalNotification($user, $withdrawal, 'rejected', $reason);

            // Log the rejection
            Log::info('Withdrawal rejected', [
                'withdrawal_id' => $withdrawal->id,
                'user_id' => $user->id,
                'wallet_id' => $withdrawal->wallet_id,
                'amount' => $withdrawal->amount,
                'reason' => $reason,
                'processed_by' => $admin->id,
                'processed_at' => $withdrawal->processed_at,
                'transaction_id' => $transaction->id,
            ]);
        });
    }

    /**
     * Send notification for withdrawal approval/rejection
     *
     * @param User $user The user to notify
     * @param WalletWithdrawal $withdrawal The withdrawal
     * @param string $action The action (approved or rejected)
     * @param string|null $reason The rejection reason (if rejected)
     * @return void
     */
    private function sendWithdrawalNotification(User $user, WalletWithdrawal $withdrawal, string $action, ?string $reason = null): void
    {
        try {
            $message = $action === 'approved'
                ? "Your withdrawal request of ₹{$withdrawal->amount} has been approved."
                : "Your withdrawal request of ₹{$withdrawal->amount} has been rejected. Reason: {$reason}";

            $this->realtimeService->notifyUser(
                $user->id,
                'wallet-withdrawal',
                [
                    'title' => 'Withdrawal ' . ucfirst($action),
                    'message' => $message,
                    'type' => 'wallet',
                    'withdrawal_id' => $withdrawal->id,
                    'action' => $action,
                    'amount' => $withdrawal->amount,
                    'reason' => $reason,
                ]
            );
        } catch (\Exception $e) {
            // Log the error but don't block the operation
            Log::error('Failed to send withdrawal notification', [
                'user_id' => $user->id,
                'withdrawal_id' => $withdrawal->id,
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get transaction history with filters and pagination
     *
     * Returns transactions in reverse chronological order (newest first).
     * Supports filtering by type, date range, amount range, and search.
     * Uses Laravel query builder with when() for conditional filters.
     *
     * @param Wallet $wallet The wallet to get transaction history for
     * @param array|null $filters Optional filters (type, start_date, end_date, min_amount, max_amount, search)
     * @param int $perPage Number of transactions per page (default: 20)
     * @return \Illuminate\Pagination\LengthAwarePaginator Paginated transaction results
     */
    public function getTransactionHistory(
        Wallet $wallet,
        ?array $filters = null,
        int $perPage = 20
    ): \Illuminate\Pagination\LengthAwarePaginator {
        // Start with base query for wallet transactions
        $query = WalletTransaction::where('wallet_id', $wallet->id);

        // Apply filters conditionally using when()
        if ($filters) {
            // Filter by transaction type
            $query->when(
                isset($filters['type']) && !empty($filters['type']),
                fn($q) => $q->where('type', $filters['type'])
            );

            // Filter by start date (transactions after this date)
            $query->when(
                isset($filters['start_date']) && !empty($filters['start_date']),
                fn($q) => $q->where('created_at', '>=', $filters['start_date'])
            );

            // Filter by end date (transactions before this date)
            $query->when(
                isset($filters['end_date']) && !empty($filters['end_date']),
                fn($q) => $q->where('created_at', '<=', $filters['end_date'])
            );

            // Filter by minimum amount
            $query->when(
                isset($filters['min_amount']) && !empty($filters['min_amount']),
                fn($q) => $q->where('amount', '>=', $filters['min_amount'])
            );

            // Filter by maximum amount
            $query->when(
                isset($filters['max_amount']) && !empty($filters['max_amount']),
                fn($q) => $q->where('amount', '<=', $filters['max_amount'])
            );

            // Search in description or reference_id
            $query->when(
                isset($filters['search']) && !empty($filters['search']),
                function ($q) use ($filters) {
                    $searchTerm = $filters['search'];
                    $q->where(function ($subQuery) use ($searchTerm) {
                        $subQuery->where('description', 'like', "%{$searchTerm}%")
                            ->orWhere('reference_id', 'like', "%{$searchTerm}%");
                    });
                }
            );
        }

        // Order by created_at in reverse chronological order (newest first)
        $query->orderBy('created_at', 'desc');

        // Return paginated results
        return $query->paginate($perPage);
    }

    /**
     * Get notification message for transaction type
     *
     * @param WalletTransaction $transaction
     * @return string
     */
    private function getTransactionNotificationMessage(WalletTransaction $transaction): string
    {
        return match ($transaction->type) {
            WalletTransaction::TYPE_SIGNUP_BONUS => "Welcome! ₹{$transaction->amount} signup bonus credited to your wallet.",
            WalletTransaction::TYPE_CREDIT, WalletTransaction::TYPE_ADMIN_CREDIT => "₹{$transaction->amount} credited to your wallet.",
            WalletTransaction::TYPE_DEBIT, WalletTransaction::TYPE_ADMIN_DEBIT => "₹{$transaction->amount} debited from your wallet.",
            WalletTransaction::TYPE_TOP_UP => "₹{$transaction->amount} added to your wallet.",
            WalletTransaction::TYPE_ORDER_PAYMENT => "₹{$transaction->amount} used for order payment.",
            WalletTransaction::TYPE_REFUND => "₹{$transaction->amount} refunded to your wallet.",
            WalletTransaction::TYPE_WITHDRAWAL => "₹{$transaction->amount} withdrawal approved.",
            WalletTransaction::TYPE_WITHDRAWAL_REVERSAL => "₹{$transaction->amount} refunded - withdrawal rejected.",
            WalletTransaction::TYPE_RIDE_PAYMENT => "₹{$transaction->amount} used for ride payment.",
            WalletTransaction::TYPE_DRIVER_EARNING => "₹{$transaction->amount} earned from ride.",
            WalletTransaction::TYPE_RIDE_REFUND => "₹{$transaction->amount} refunded for ride.",
            default => "Wallet transaction: ₹{$transaction->amount}",
        };
    }

    /**
     * Helper to debit from user's wallet by user ID
     */
    public function debit($userId, float $amount, string $type, string $description, ?array $metadata = null): array
    {
        $user = User::findOrFail($userId);
        $wallet = $this->getOrCreateWallet($user);
        $transaction = $this->debitBalance($wallet, $amount, $type, $description, $metadata);
        
        return [
            'success' => true,
            'transaction' => $transaction,
            'message' => 'Debit successful'
        ];
    }

    /**
     * Helper to credit to user's wallet by user ID
     */
    public function credit($userId, float $amount, string $type, string $description, ?array $metadata = null): array
    {
        $user = User::findOrFail($userId);
        $wallet = $this->getOrCreateWallet($user);
        $transaction = $this->creditBalance($wallet, $amount, $type, $description, $metadata);
        
        return [
            'success' => true,
            'transaction' => $transaction,
            'message' => 'Credit successful'
        ];
    }
}
