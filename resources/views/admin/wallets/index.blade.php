@extends('admin.layouts.app')
@section('title', 'Wallets')
@section('content')
<div class="space-y-5">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Wallets</h1>
            <p class="text-sm text-gray-500 mt-1">Manage user wallet balances and transactions</p>
        </div>
        <div class="flex gap-2">
            <button onclick="openAddMoneyModal()" class="btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Add/Deduct Money
            </button>
            <button class="btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Export
            </button>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card">
            <p class="stat-value text-xl">{{ $stats['total_wallets'] ?? 0 }}</p>
            <p class="stat-label">Total Wallets</p>
        </div>
        <div class="stat-card">
            <p class="stat-value text-xl text-green-600"><x-currency :amount="$stats['total_balance'] ?? 0" /></p>
            <p class="stat-label">Total Balance</p>
        </div>
        <div class="stat-card">
            <p class="stat-value text-xl text-blue-600">{{ $stats['active_users'] ?? 0 }}</p>
            <p class="stat-label">Active Users</p>
        </div>
        <div class="stat-card">
            <p class="stat-value text-xl text-orange-600">{{ $stats['pending_withdrawals'] ?? 0 }}</p>
            <p class="stat-label">Pending Withdrawals</p>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.wallets.index') }}" class="card">
        <div class="p-4 flex flex-col lg:flex-row gap-4">
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by user name or email..." class="input pl-10">
            </div>
            <select name="role" class="input lg:w-40" onchange="this.form.submit()">
                <option value="">All Roles</option>
                <option value="customer" {{ request('role') === 'customer' ? 'selected' : '' }}>Customer</option>
                <option value="delivery_boy" {{ request('role') === 'delivery_boy' ? 'selected' : '' }}>Delivery Boy</option>
                <option value="store_owner" {{ request('role') === 'store_owner' ? 'selected' : '' }}>Store Owner</option>
                <option value="store_manager" {{ request('role') === 'store_manager' ? 'selected' : '' }}>Store Manager</option>
                <option value="store_staff" {{ request('role') === 'store_staff' ? 'selected' : '' }}>Store Staff</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="super_admin" {{ request('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
            </select>
            <input type="number" name="balance_min" value="{{ request('balance_min') }}" placeholder="Min Balance" step="0.01" class="input lg:w-32">
            <input type="number" name="balance_max" value="{{ request('balance_max') }}" placeholder="Max Balance" step="0.01" class="input lg:w-32">
            <button type="submit" class="btn-secondary">Filter</button>
            @if(request()->hasAny(['search', 'role', 'balance_min', 'balance_max']))
            <a href="{{ route('admin.wallets.index') }}" class="btn-secondary">Clear</a>
            @endif
        </div>
    </form>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="table-header">User</th>
                        <th class="table-header">Role</th>
                        <th class="table-header">Balance</th>
                        <th class="table-header">Transaction Count</th>
                        <th class="table-header">Created Date</th>
                        <th class="table-header text-center w-24">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($wallets as $wallet)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="table-cell">
                            <div class="flex items-center gap-3">
                                <div class="avatar avatar-sm bg-gray-200 text-gray-600 text-xs font-semibold">
                                    {{ substr($wallet->user->name ?? 'U', 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $wallet->user->name ?? 'Unknown' }}</p>
                                    <p class="text-xs text-gray-500">{{ $wallet->user->email ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="table-cell">
                            @php
                            $roleColors = [
                                'customer' => 'badge-blue',
                                'delivery_boy' => 'badge-purple',
                                'store_owner' => 'badge-green',
                                'store_manager' => 'badge-green',
                                'store_staff' => 'badge-green',
                                'admin' => 'badge-orange',
                                'super_admin' => 'badge-red',
                            ];
                            $roleValue = $wallet->user->role instanceof \BackedEnum ? $wallet->user->role->value : $wallet->user->role;
                            @endphp
                            <span class="badge {{ $roleColors[$roleValue] ?? 'badge-gray' }}">{{ ucwords(str_replace('_', ' ', $roleValue)) }}</span>
                        </td>
                        <td class="table-cell">
                            <span class="font-semibold text-gray-900"><x-currency :amount="$wallet->balance" /></span>
                        </td>
                        <td class="table-cell">
                            <span class="badge badge-gray">{{ $wallet->transactions_count ?? $wallet->transactions->count() }} transactions</span>
                        </td>
                        <td class="table-cell">
                            <span class="text-gray-500 text-sm">{{ \App\Helpers\DateHelper::format($wallet->created_at) }}</span>
                            <p class="text-xs text-gray-400">{{ \App\Helpers\DateHelper::formatTime($wallet->created_at) }}</p>
                        </td>
                        <td class="table-cell">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('admin.wallets.show', $wallet) }}" class="action-btn action-btn-view">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-16 text-center">
                            <div class="empty-icon mx-auto">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                            </div>
                            <p class="font-medium text-gray-900 mt-4">No wallets found</p>
                            <p class="text-sm text-gray-500 mt-1">Wallets will appear here when users register on the platform</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($wallets->hasPages())<div class="px-5 py-4 border-t border-gray-100">{{ $wallets->appends(request()->query())->links() }}</div>@endif
    </div>
</div>

<!-- Add/Deduct Money Modal -->
<div id="addMoneyModal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Add/Deduct Money</h3>
            <button onclick="closeAddMoneyModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="addMoneyForm" method="POST" class="p-6 space-y-5">
            @csrf
            
            <!-- User Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Select User</label>
                <select name="user_id" id="userSelect" required class="input" onchange="loadUserWallet()">
                    <option value="">-- Select a user --</option>
                    @foreach(\App\Models\User::with('wallet')->orderBy('name')->get() as $user)
                        <option value="{{ $user->id }}" data-wallet-id="{{ $user->wallet?->id }}" data-balance="{{ $user->wallet?->balance ?? 0 }}">
                            {{ $user->name }} ({{ $user->email }}) - {{ ucwords(str_replace('_', ' ', $user->role->value ?? $user->role)) }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">Search and select the user whose wallet you want to modify</p>
            </div>

            <!-- Current Balance Display -->
            <div id="currentBalanceDisplay" class="hidden bg-gradient-to-r from-indigo-50 to-blue-50 rounded-lg p-4 border border-indigo-100">
                <p class="text-xs font-medium text-indigo-600 uppercase tracking-wide mb-1">Current Balance</p>
                <p class="text-2xl font-bold text-indigo-900" id="currentBalanceAmount">-</p>
            </div>

            <!-- Amount Input -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium">{{ \App\Helpers\CurrencyHelper::getSymbol() }}</span>
                    <input type="number" name="amount" id="amountInput" step="0.01" min="0.01" max="1000000" required class="input pl-10" placeholder="0.00">
                </div>
                <p class="text-xs text-gray-500 mt-1">Enter the amount to add or deduct</p>
            </div>

            <!-- Reason Input -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
                <textarea name="reason" rows="3" required class="input resize-none" placeholder="Enter reason for this transaction..."></textarea>
                <p class="text-xs text-gray-500 mt-1">Provide a clear reason for audit purposes</p>
            </div>

            <!-- Allow Negative Balance (for debit only) -->
            <div id="allowNegativeContainer" class="hidden">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="allow_negative" value="1" class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <span class="text-sm font-medium text-gray-700">Allow Negative Balance</span>
                        <p class="text-xs text-gray-500 mt-0.5">Check this to allow debit even if it results in negative balance</p>
                    </div>
                </label>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="submitTransaction('credit')" class="flex-1 bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white font-medium py-2.5 px-4 rounded-lg transition-all duration-200 shadow-sm hover:shadow flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Credit (Add Money)
                </button>
                <button type="button" onclick="submitTransaction('debit')" class="flex-1 bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white font-medium py-2.5 px-4 rounded-lg transition-all duration-200 shadow-sm hover:shadow flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                    Debit (Deduct Money)
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddMoneyModal() {
    document.getElementById('addMoneyModal').classList.remove('hidden');
    document.getElementById('addMoneyModal').classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeAddMoneyModal() {
    document.getElementById('addMoneyModal').classList.add('hidden');
    document.getElementById('addMoneyModal').classList.remove('flex');
    document.body.style.overflow = '';
    document.getElementById('addMoneyForm').reset();
    document.getElementById('currentBalanceDisplay').classList.add('hidden');
    document.getElementById('allowNegativeContainer').classList.add('hidden');
}

function loadUserWallet() {
    const select = document.getElementById('userSelect');
    const selectedOption = select.options[select.selectedIndex];
    const balance = selectedOption.getAttribute('data-balance');
    
    if (balance !== null && balance !== '') {
        const formattedBalance = new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: '{{ \App\Helpers\CurrencyHelper::getDefault() }}',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(parseFloat(balance));
        
        document.getElementById('currentBalanceAmount').textContent = formattedBalance;
        document.getElementById('currentBalanceDisplay').classList.remove('hidden');
    } else {
        document.getElementById('currentBalanceDisplay').classList.add('hidden');
    }
}

function submitTransaction(action) {
    const form = document.getElementById('addMoneyForm');
    const select = document.getElementById('userSelect');
    const selectedOption = select.options[select.selectedIndex];
    const walletId = selectedOption.getAttribute('data-wallet-id');
    
    if (!walletId) {
        alert('This user does not have a wallet yet. Please select another user.');
        return;
    }
    
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Show/hide allow negative checkbox based on action
    if (action === 'debit') {
        document.getElementById('allowNegativeContainer').classList.remove('hidden');
    }
    
    // Set form action based on credit or debit
    form.action = `/admin/wallets/${walletId}/${action}`;
    form.submit();
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAddMoneyModal();
    }
});

// Close modal on backdrop click
document.getElementById('addMoneyModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeAddMoneyModal();
    }
});
</script>
@endsection
