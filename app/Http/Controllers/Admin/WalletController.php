<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\WalletWithdrawal;
use App\Services\WalletService;
use App\Helpers\CurrencyHelper;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function __construct(
        private WalletService $walletService
    ) {}

    /**
     * List all wallets with search and filters
     * 
     * GET /admin/wallets
     * 
     * Filters:
     * - role: Filter by user role
     * - balance_min: Minimum balance
     * - balance_max: Maximum balance
     * - search: Search by user name/email
     * 
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $query = Wallet::with('user');

        // Filter by user role
        if ($request->query('role')) {
            $role = $request->query('role');
            $query->whereHas('user', function ($q) use ($role) {
                $q->where('role', $role);
            });
        }

        // Filter by minimum balance
        if ($request->query('balance_min')) {
            $query->where('balance', '>=', $request->query('balance_min'));
        }

        // Filter by maximum balance
        if ($request->query('balance_max')) {
            $query->where('balance', '<=', $request->query('balance_max'));
        }

        // Search by user name or email
        if ($request->query('search')) {
            $search = $request->query('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Order by balance descending by default
        $query->orderBy('balance', 'desc');

        $wallets = $query->paginate(20);

        // Calculate statistics for dashboard cards
        $stats = [
            'total_wallets' => Wallet::count(),
            'total_balance' => Wallet::sum('balance'),
            'active_users' => Wallet::where('balance', '>', 0)->count(),
            'pending_withdrawals' => WalletWithdrawal::where('status', 'pending')->count(),
        ];

        return view('admin.wallets.index', compact('wallets', 'stats'));
    }

    /**
     * Display wallet details and transaction history
     * 
     * GET /admin/wallets/{wallet}
     * 
     * @param Wallet $wallet
     * @return View
     */
    public function show(Wallet $wallet): View
    {
        // Load user relationship
        $wallet->load('user');

        // Get transaction history with pagination
        $transactions = $this->walletService->getTransactionHistory($wallet, null, 20);

        // Get withdrawal history for this wallet
        $withdrawals = WalletWithdrawal::where('wallet_id', $wallet->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.wallets.show', compact('wallet', 'transactions', 'withdrawals'));
    }

    /**
     * Manual credit with reason validation
     * 
     * POST /admin/wallets/{wallet}/credit
     * 
     * @param Request $request
     * @param Wallet $wallet
     * @return RedirectResponse
     */
    public function credit(Request $request, Wallet $wallet): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:1000000',
            'reason' => 'required|string|max:500',
        ]);

        try {
            $this->walletService->creditBalance(
                $wallet,
                $validated['amount'],
                'admin_credit',
                $validated['reason'],
                ['admin_action' => 'manual_credit'],
                Auth::user()
            );

            $formattedAmount = CurrencyHelper::format($validated['amount']);
            return redirect()
                ->route('admin.wallets.show', $wallet)
                ->with('success', "Successfully credited {$formattedAmount} to wallet");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to credit wallet: ' . $e->getMessage());
        }
    }

    /**
     * Manual debit with reason validation
     * 
     * POST /admin/wallets/{wallet}/debit
     * 
     * @param Request $request
     * @param Wallet $wallet
     * @return RedirectResponse
     */
    public function debit(Request $request, Wallet $wallet): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:1000000',
            'reason' => 'required|string|max:500',
            'allow_negative' => 'boolean',
        ]);

        try {
            $this->walletService->debitBalance(
                $wallet,
                $validated['amount'],
                'admin_debit',
                $validated['reason'],
                ['admin_action' => 'manual_debit'],
                Auth::user(),
                $request->boolean('allow_negative', false)
            );

            $formattedAmount = CurrencyHelper::format($validated['amount']);
            return redirect()
                ->route('admin.wallets.show', $wallet)
                ->with('success', "Successfully debited {$formattedAmount} from wallet");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to debit wallet: ' . $e->getMessage());
        }
    }

    /**
     * List pending withdrawals with filters
     * 
     * GET /admin/wallets/withdrawals
     * 
     * @param Request $request
     * @return View
     */
    public function withdrawals(Request $request): View
    {
        $query = WalletWithdrawal::with(['wallet.user', 'processedBy']);

        // Filter by status (default to pending)
        $status = $request->query('status', 'pending');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Filter by date range
        if ($request->query('start_date')) {
            $query->where('created_at', '>=', $request->query('start_date'));
        }

        if ($request->query('end_date')) {
            $query->where('created_at', '<=', $request->query('end_date'));
        }

        // Search by user name or email
        if ($request->query('search')) {
            $search = $request->query('search');
            $query->whereHas('wallet.user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Order by created_at descending (newest first)
        $query->orderBy('created_at', 'desc');

        $withdrawals = $query->paginate(20);

        // Calculate statistics
        $stats = [
            'pending_count' => WalletWithdrawal::where('status', 'pending')->count(),
            'pending_amount' => WalletWithdrawal::where('status', 'pending')->sum('amount'),
            'approved_today' => WalletWithdrawal::where('status', 'approved')
                ->whereDate('processed_at', today())
                ->count(),
            'rejected_today' => WalletWithdrawal::where('status', 'rejected')
                ->whereDate('processed_at', today())
                ->count(),
        ];

        return view('admin.wallets.withdrawals', compact('withdrawals', 'stats', 'status'));
    }

    /**
     * Approve withdrawal
     * 
     * POST /admin/wallets/withdrawals/{withdrawal}/approve
     * 
     * @param WalletWithdrawal $withdrawal
     * @return RedirectResponse
     */
    public function approveWithdrawal(WalletWithdrawal $withdrawal): RedirectResponse
    {
        // Check if withdrawal is still pending
        if ($withdrawal->status !== 'pending') {
            return redirect()
                ->back()
                ->with('error', 'This withdrawal has already been processed');
        }

        try {
            $this->walletService->approveWithdrawal($withdrawal, Auth::user());

            $formattedAmount = CurrencyHelper::format($withdrawal->amount);
            return redirect()
                ->route('admin.wallets.withdrawals')
                ->with('success', "Withdrawal of {$formattedAmount} approved successfully");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to approve withdrawal: ' . $e->getMessage());
        }
    }

    /**
     * Reject withdrawal with reason
     * 
     * POST /admin/wallets/withdrawals/{withdrawal}/reject
     * 
     * @param Request $request
     * @param WalletWithdrawal $withdrawal
     * @return RedirectResponse
     */
    public function rejectWithdrawal(Request $request, WalletWithdrawal $withdrawal): RedirectResponse
    {
        // Check if withdrawal is still pending
        if ($withdrawal->status !== 'pending') {
            return redirect()
                ->back()
                ->with('error', 'This withdrawal has already been processed');
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $this->walletService->rejectWithdrawal(
                $withdrawal,
                Auth::user(),
                $validated['reason']
            );

            $formattedAmount = CurrencyHelper::format($withdrawal->amount);
            return redirect()
                ->route('admin.wallets.withdrawals')
                ->with('success', "Withdrawal of {$formattedAmount} rejected successfully");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to reject withdrawal: ' . $e->getMessage());
        }
    }

    /**
     * Show wallet settings page
     * 
     * GET /admin/settings/wallet
     * 
     * @return View
     */
    public function settings(): View
    {
        $settings = [
            'signup_bonus_enabled' => \App\Models\Setting::get('signup_bonus_enabled', false),
            'signup_bonus_amount' => \App\Models\Setting::get('signup_bonus_amount', 0),
            'min_topup_amount' => \App\Models\Setting::get('min_topup_amount', 10),
            'max_topup_amount' => \App\Models\Setting::get('max_topup_amount', 10000),
            'min_withdrawal_amount' => \App\Models\Setting::get('min_withdrawal_amount', 50),
            'withdrawal_processing_days' => \App\Models\Setting::get('withdrawal_processing_days', 3),
            'max_transaction_amount' => \App\Models\Setting::get('max_transaction_amount', 100000),
            'allow_negative_balance' => \App\Models\Setting::get('allow_negative_balance', false),
            'send_transaction_notifications' => \App\Models\Setting::get('send_transaction_notifications', true),
            'send_withdrawal_notifications' => \App\Models\Setting::get('send_withdrawal_notifications', true),
        ];

        return view('admin.settings.wallet', compact('settings'));
    }

    /**
     * Update wallet settings
     * 
     * PUT /admin/settings/wallet
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'signup_bonus_enabled' => 'boolean',
            'signup_bonus_amount' => 'required|numeric|min:0|max:1000',
            'min_topup_amount' => 'required|numeric|min:0.01|max:10000',
            'max_topup_amount' => 'required|numeric|min:1|max:1000000',
            'min_withdrawal_amount' => 'required|numeric|min:0.01|max:10000',
            'withdrawal_processing_days' => 'required|integer|min:1|max:30',
            'max_transaction_amount' => 'required|numeric|min:1|max:1000000',
            'allow_negative_balance' => 'boolean',
            'send_transaction_notifications' => 'boolean',
            'send_withdrawal_notifications' => 'boolean',
        ]);

        // Validate that max is greater than min
        if ($validated['max_topup_amount'] < $validated['min_topup_amount']) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['max_topup_amount' => 'Maximum top-up amount must be greater than minimum']);
        }

        // Update settings
        foreach ($validated as $key => $value) {
            \App\Models\Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return redirect()
            ->route('admin.settings.wallet')
            ->with('success', 'Wallet settings updated successfully');
    }

    /**
     * Export wallet data to CSV
     * 
     * GET /admin/wallets/export
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request): \Illuminate\Http\Response
    {
        $query = Wallet::with('user');

        // Apply same filters as index method
        if ($request->filled('role')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('role', $request->role);
            });
        }

        if ($request->filled('balance_min')) {
            $query->where('balance', '>=', $request->balance_min);
        }

        if ($request->filled('balance_max')) {
            $query->where('balance', '<=', $request->balance_max);
        }

        if ($request->filled('search')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $wallets = $query->get();

        // Generate CSV content
        $csvData = [];
        
        // CSV Header
        $csvData[] = [
            'Wallet ID',
            'User ID',
            'User Name',
            'User Email',
            'User Role',
            'Balance',
            'Currency',
            'Transaction Count',
            'Created At',
            'Updated At',
        ];

        // CSV Rows
        foreach ($wallets as $wallet) {
            $csvData[] = [
                $wallet->id,
                $wallet->user_id,
                $wallet->user->name ?? 'N/A',
                $wallet->user->email ?? 'N/A',
                $wallet->user->role ?? 'N/A',
                $wallet->balance,
                $wallet->currency,
                $wallet->transactions()->count(),
                $wallet->created_at->format('Y-m-d H:i:s'),
                $wallet->updated_at->format('Y-m-d H:i:s'),
            ];
        }

        // Convert to CSV string
        $output = fopen('php://temp', 'r+');
        foreach ($csvData as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        // Generate filename with timestamp
        $filename = 'wallets_export_' . now()->format('Y-m-d_His') . '.csv';

        // Return CSV response
        return Response::make($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
