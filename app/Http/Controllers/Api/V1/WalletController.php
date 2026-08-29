<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletWithdrawal;
use App\Services\WalletService;
use App\Exceptions\InvalidAmountException;
use App\Exceptions\InvalidPaymentMethodException;
use App\Exceptions\UnauthorizedWithdrawalException;
use App\Exceptions\InsufficientBalanceException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class WalletController extends Controller
{
    public function __construct(
        private WalletService $walletService
    ) {}

    /**
     * Get current user's wallet balance and info
     * 
     * GET /api/v1/wallet
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getBalance(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Get or create wallet for the user
            $wallet = $this->walletService->getOrCreateWallet($user);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => (int) $wallet->id,
                    'user_id' => (int) $wallet->user_id,
                    'balance' => (float) $wallet->balance,
                    'currency' => (string) $wallet->currency,
                    'created_at' => $wallet->created_at->toIso8601String(),
                    'updated_at' => $wallet->updated_at->toIso8601String(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve wallet balance',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get transaction history with filters
     * 
     * GET /api/v1/wallet/transactions
     * Query parameters: type, start_date, end_date, page, per_page
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getTransactions(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Get or create wallet for the user
            $wallet = $this->walletService->getOrCreateWallet($user);

            // Validate query parameters
            $validated = $request->validate([
                'type' => 'nullable|string|in:credit,debit,order_payment,top_up,signup_bonus,admin_credit,admin_debit,refund,withdrawal,withdrawal_reversal,referral_reward,points_redemption',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            // Build filters array
            $filters = [];
            if (isset($validated['type'])) {
                $filters['type'] = $validated['type'];
            }
            if (isset($validated['start_date'])) {
                $filters['start_date'] = $validated['start_date'];
            }
            if (isset($validated['end_date'])) {
                $filters['end_date'] = $validated['end_date'];
            }

            // Get per_page value (default: 20)
            $perPage = $validated['per_page'] ?? 20;

            // Get transaction history with filters
            $transactions = $this->walletService->getTransactionHistory(
                $wallet,
                $filters,
                $perPage
            );

            return response()->json([
                'success' => true,
                'data' => $transactions->items(),
                'meta' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve transaction history',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Initiate wallet top-up
     * 
     * POST /api/v1/wallet/top-up
     * Body: { amount: float, gateway: string }
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function initiateTopUp(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Validate request inputs
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'gateway' => 'required|string|in:razorpay,paystack,stripe,flutterwave,paytm,phonepe',
            ]);

            // Initiate top-up via WalletService
            $result = $this->walletService->initiateTopUp(
                $user,
                (float) $validated['amount'],
                $validated['gateway']
            );

            return response()->json([
                'success' => true,
                'message' => 'Top-up initiated successfully',
                'data' => $result,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (InvalidAmountException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (InvalidPaymentMethodException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate top-up',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle payment gateway callback for top-up
     * 
     * POST /api/v1/wallet/top-up/callback
     * Body: { transaction_id: string, amount: float, gateway: string, status: string, ...payment_data }
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function topUpCallback(Request $request): JsonResponse
    {
        try {
            // Validate request inputs
            $validated = $request->validate([
                'transaction_id' => 'required|string',
                'amount' => 'required|numeric|min:0.01',
                'gateway' => 'required|string',
                'status' => 'required|string',
                'user_id' => 'required|integer|exists:users,id',
            ]);

            // Get the user
            $user = User::findOrFail($validated['user_id']);

            // Only process if payment status is successful
            if ($validated['status'] !== 'success' && $validated['status'] !== 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not successful',
                ], 400);
            }

            // Complete top-up via WalletService
            $transaction = $this->walletService->completeTopUp(
                $user,
                (float) $validated['amount'],
                $validated['transaction_id'],
                $request->all() // Pass all payment data for verification
            );

            return response()->json([
                'success' => true,
                'message' => 'Top-up completed successfully',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'amount' => (float) $transaction->amount,
                    'balance_after' => (float) $transaction->balance_after,
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (InvalidAmountException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete top-up',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Request withdrawal (sellers and delivery boys only)
     * 
     * POST /api/v1/wallet/withdrawal
     * Body: { amount: float, bank_details: { bank_name, account_number, account_holder_name, ifsc_code } }
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function requestWithdrawal(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Validate request inputs
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'bank_details' => 'required|array',
                'bank_details.bank_name' => 'required|string|max:255',
                'bank_details.account_number' => 'required|string|max:50',
                'bank_details.account_holder_name' => 'required|string|max:255',
                'bank_details.ifsc_code' => 'nullable|string|max:20',
            ]);

            // Request withdrawal via WalletService
            $withdrawal = $this->walletService->requestWithdrawal(
                $user,
                (float) $validated['amount'],
                $validated['bank_details']
            );

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal request submitted successfully',
                'data' => [
                    'id' => $withdrawal->id,
                    'wallet_id' => $withdrawal->wallet_id,
                    'amount' => (float) $withdrawal->amount,
                    'bank_name' => $withdrawal->bank_name,
                    'account_number' => $withdrawal->account_number,
                    'account_holder_name' => $withdrawal->account_holder_name,
                    'ifsc_code' => $withdrawal->ifsc_code,
                    'status' => $withdrawal->status,
                    'created_at' => $withdrawal->created_at,
                ],
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (UnauthorizedWithdrawalException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        } catch (InvalidAmountException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (InsufficientBalanceException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to request withdrawal',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get withdrawal history
     * 
     * GET /api/v1/wallet/withdrawals
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getWithdrawals(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Get or create wallet for the user
            $wallet = $this->walletService->getOrCreateWallet($user);

            // Get withdrawals for this wallet
            $withdrawals = WalletWithdrawal::where('wallet_id', $wallet->id)
                ->orderBy('created_at', 'desc')
                ->paginate($request->query('per_page', 20));

            return response()->json([
                'success' => true,
                'data' => $withdrawals->items(),
                'meta' => [
                    'current_page' => $withdrawals->currentPage(),
                    'last_page' => $withdrawals->lastPage(),
                    'per_page' => $withdrawals->perPage(),
                    'total' => $withdrawals->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve withdrawal history',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
