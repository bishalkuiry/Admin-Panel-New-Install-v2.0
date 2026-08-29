@extends('admin.layouts.app')
@section('title', 'Wallet Details')
@section('content')
<div x-data="{ showCreditModal: false, showDebitModal: false }" class="space-y-5">
    {{-- Header with wallet info inline --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.wallets.index') }}" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div class="flex items-center gap-3">
                <div class="avatar bg-gray-200 text-gray-600 font-semibold">{{ substr($wallet->user->name ?? 'U', 0, 1) }}</div>
                <div>
                    <h1 class="text-lg font-semibold text-gray-900">{{ $wallet->user->name ?? 'Unknown' }}</h1>
                    <div class="flex items-center gap-3 text-sm text-gray-500">
                        <span>{{ $wallet->user->email ?? 'N/A' }}</span>
                        @if($wallet->user->phone)<span>• {{ $wallet->user->phone }}</span>@endif
                        @php $roleValue = $wallet->user->role instanceof \BackedEnum ? $wallet->user->role->value : $wallet->user->role; @endphp
                        <span class="badge badge-blue">{{ ucwords(str_replace('_', ' ', $roleValue)) }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <div class="text-right mr-4">
                <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Balance</p>
                <p class="text-2xl font-bold text-gray-900"><x-currency :amount="$wallet->balance" /></p>
            </div>
            <button @click="showCreditModal = true" class="btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Credit
            </button>
            <button @click="showDebitModal = true" class="btn-secondary" style="border-color: #ef4444; color: #ef4444;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                Debit
            </button>
        </div>
    </div>

    {{-- Wallet meta info bar --}}
    <div class="flex items-center gap-6 text-sm text-gray-500 bg-white rounded-lg border border-gray-100 px-5 py-3">
        <div class="flex items-center gap-1.5">
            <span class="text-gray-400">Wallet ID:</span>
            <span class="font-mono text-gray-700">#{{ $wallet->id }}</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="text-gray-400">Currency:</span>
            <span class="text-gray-700">{{ $wallet->currency }}</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="text-gray-400">Created:</span>
            <span class="text-gray-700">{{ \App\Helpers\DateHelper::format($wallet->created_at) }}</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="text-gray-400">Last Updated:</span>
            <span class="text-gray-700">{{ \App\Helpers\DateHelper::format($wallet->updated_at) }}</span>
        </div>
    </div>

    {{-- Transaction History --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Transaction History</h3>
            <span class="badge badge-blue">{{ $transactions->total() }} transactions</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="table-header">Type</th>
                        <th class="table-header">Description</th>
                        <th class="table-header">Amount</th>
                        <th class="table-header">Balance After</th>
                        <th class="table-header">Date</th>
                        <th class="table-header">Created By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($transactions as $transaction)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="table-cell">
                            @php
                            $typeColors = [
                                'signup_bonus' => 'badge-green',
                                'top_up' => 'badge-blue',
                                'order_payment' => 'badge-purple',
                                'refund' => 'badge-green',
                                'withdrawal' => 'badge-orange',
                                'withdrawal_reversal' => 'badge-orange',
                                'admin_credit' => 'badge-blue',
                                'admin_debit' => 'badge-red',
                            ];
                            @endphp
                            <span class="badge {{ $typeColors[$transaction->type] ?? 'badge-gray' }}">{{ ucwords(str_replace('_', ' ', $transaction->type)) }}</span>
                        </td>
                        <td class="table-cell">
                            <p class="text-gray-900">{{ $transaction->description }}</p>
                            @if($transaction->reference_type && $transaction->reference_id)
                            <p class="text-xs text-gray-500">Ref: {{ class_basename($transaction->reference_type) }} #{{ $transaction->reference_id }}</p>
                            @endif
                        </td>
                        <td class="table-cell">
                            @php
                                $isDebit = in_array($transaction->type, ['admin_debit', 'order_payment', 'withdrawal']) || $transaction->amount < 0;
                            @endphp
                            <span class="font-semibold {{ $isDebit ? 'text-red-600' : 'text-green-600' }}">
                                {{ $isDebit ? '-' : '+' }}<x-currency :amount="abs($transaction->amount)" />
                            </span>
                        </td>
                        <td class="table-cell">
                            <span class="text-gray-900"><x-currency :amount="$transaction->balance_after" /></span>
                        </td>
                        <td class="table-cell">
                            <span class="text-gray-500 text-sm">{{ \App\Helpers\DateHelper::format($transaction->created_at) }}</span>
                            <p class="text-xs text-gray-400">{{ \App\Helpers\DateHelper::formatTime($transaction->created_at) }}</p>
                        </td>
                        <td class="table-cell">
                            @if($transaction->created_by_id)
                            <span class="text-sm text-gray-600">{{ $transaction->createdBy->name ?? 'N/A' }}</span>
                            @else
                            <span class="text-sm text-gray-400">System</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-16 text-center">
                            <div class="empty-icon mx-auto">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <p class="font-medium text-gray-900 mt-4">No transactions yet</p>
                            <p class="text-sm text-gray-500 mt-1">Transaction history will appear here</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())<div class="px-5 py-4 border-t border-gray-100">{{ $transactions->links() }}</div>@endif
    </div>

    {{-- Withdrawal History --}}
    @if($withdrawals->isNotEmpty())
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Withdrawal History</h3>
            <span class="badge badge-orange">{{ $withdrawals->total() }} withdrawals</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="table-header">Amount</th>
                        <th class="table-header">Bank Details</th>
                        <th class="table-header">Status</th>
                        <th class="table-header">Requested</th>
                        <th class="table-header">Processed</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($withdrawals as $withdrawal)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="table-cell">
                            <span class="font-semibold text-gray-900"><x-currency :amount="$withdrawal->amount" /></span>
                        </td>
                        <td class="table-cell">
                            <p class="text-sm text-gray-900">{{ $withdrawal->bank_name }}</p>
                            <p class="text-xs text-gray-500">{{ $withdrawal->account_number }}</p>
                            <p class="text-xs text-gray-500">{{ $withdrawal->account_holder_name }}</p>
                        </td>
                        <td class="table-cell">
                            @php
                            $statusColors = [
                                'pending' => 'badge-orange',
                                'approved' => 'badge-green',
                                'rejected' => 'badge-red',
                            ];
                            @endphp
                            <span class="badge {{ $statusColors[$withdrawal->status] ?? 'badge-gray' }}">{{ ucfirst($withdrawal->status) }}</span>
                            @if($withdrawal->status === 'rejected' && $withdrawal->rejection_reason)
                            <p class="text-xs text-red-600 mt-1">{{ $withdrawal->rejection_reason }}</p>
                            @endif
                        </td>
                        <td class="table-cell">
                            <span class="text-gray-500 text-sm">{{ \App\Helpers\DateHelper::format($withdrawal->created_at) }}</span>
                        </td>
                        <td class="table-cell">
                            @if($withdrawal->processed_at)
                            <span class="text-gray-500 text-sm">{{ \App\Helpers\DateHelper::format($withdrawal->processed_at) }}</span>
                            <p class="text-xs text-gray-400">{{ $withdrawal->processedBy->name ?? 'N/A' }}</p>
                            @else
                            <span class="text-gray-400 text-sm">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($withdrawals->hasPages())<div class="px-5 py-4 border-t border-gray-100">{{ $withdrawals->links() }}</div>@endif
    </div>
    @endif

    {{-- Credit Modal --}}
    <div x-show="showCreditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="showCreditModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/50" @click="showCreditModal = false"></div>
        <div x-show="showCreditModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="relative bg-white rounded-xl shadow-2xl w-full max-w-md z-10">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-base font-semibold text-gray-900">Credit Wallet</h3>
                <button @click="showCreditModal = false" class="p-1 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('admin.wallets.credit', $wallet) }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="bg-green-50 border border-green-200 rounded-lg p-3 flex items-center justify-between">
                    <span class="text-sm text-green-700">Current Balance</span>
                    <span class="text-lg font-bold text-green-800"><x-currency :amount="$wallet->balance" /></span>
                </div>
                <div>
                    <label class="label">Amount</label>
                    <input type="number" name="amount" step="0.01" min="0.01" max="1000000" required class="input" placeholder="0.00">
                </div>
                <div>
                    <label class="label">Reason</label>
                    <textarea name="reason" rows="2" required class="input" placeholder="Enter reason for credit"></textarea>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" @click="showCreditModal = false" class="btn-secondary flex-1">Cancel</button>
                    <button type="submit" class="btn-primary flex-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Credit Wallet
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Debit Modal --}}
    <div x-show="showDebitModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="showDebitModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/50" @click="showDebitModal = false"></div>
        <div x-show="showDebitModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="relative bg-white rounded-xl shadow-2xl w-full max-w-md z-10">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-base font-semibold text-gray-900">Debit Wallet</h3>
                <button @click="showDebitModal = false" class="p-1 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('admin.wallets.debit', $wallet) }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="bg-red-50 border border-red-200 rounded-lg p-3 flex items-center justify-between">
                    <span class="text-sm text-red-700">Current Balance</span>
                    <span class="text-lg font-bold text-red-800"><x-currency :amount="$wallet->balance" /></span>
                </div>
                <div>
                    <label class="label">Amount</label>
                    <input type="number" name="amount" step="0.01" min="0.01" max="1000000" required class="input" placeholder="0.00">
                </div>
                <div>
                    <label class="label">Reason</label>
                    <textarea name="reason" rows="2" required class="input" placeholder="Enter reason for debit"></textarea>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" @click="showDebitModal = false" class="btn-secondary flex-1">Cancel</button>
                    <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                        Debit Wallet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
