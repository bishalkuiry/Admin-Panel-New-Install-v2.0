@extends('admin.layouts.app')
@section('title', $store->name . ' - Payouts')
@section('content')
<div class="space-y-5">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.stores.show', $store) }}" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-gray-900">{{ $store->name }} - Payouts</h1>
                <p class="text-sm text-gray-500 mt-1">Manage store payouts</p>
            </div>
        </div>
        <button onclick="document.getElementById('payout-modal').classList.remove('hidden')" class="btn-primary">Create Payout</button>
    </div>

    @include('admin.stores._tabs', ['store' => $store])

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="stat-card"><p class="stat-value text-xl"><x-currency :amount="$store->available_balance ?? 0" /></p><p class="stat-label">Available Balance</p></div>
        <div class="stat-card"><p class="stat-value text-xl"><x-currency :amount="$store->pending_balance ?? 0" /></p><p class="stat-label">Pending</p></div>
        <div class="stat-card"><p class="stat-value text-xl"><x-currency :amount="$store->total_paid ?? 0" /></p><p class="stat-label">Total Paid</p></div>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="table-header">Payout ID</th>
                        <th class="table-header">Amount</th>
                        <th class="table-header">Method</th>
                        <th class="table-header">Status</th>
                        <th class="table-header">Date</th>
                        <th class="table-header text-center w-24">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($payouts as $payout)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="table-cell font-mono text-gray-900">#{{ $payout->id }}</td>
                        <td class="table-cell font-semibold text-gray-900"><x-currency :amount="$payout->amount" /></td>
                        <td class="table-cell">
                            <button type="button" 
                                    onclick="document.getElementById('bank-details-modal').classList.remove('hidden')" 
                                    class="text-blue-600 hover:text-blue-700 hover:underline font-medium focus:outline-none text-left">
                                {{ ucfirst($payout->payment_method ?? 'bank') }}
                            </button>
                        </td>
                        <td class="table-cell">
                            @php $statusColors = ['pending' => 'badge-orange', 'processing' => 'badge-blue', 'completed' => 'badge-green', 'failed' => 'badge-red']; @endphp
                            <span class="badge {{ $statusColors[$payout->status] ?? 'badge-gray' }}">{{ ucfirst($payout->status) }}</span>
                        </td>
                        <td class="table-cell text-gray-500 text-sm">{{ \App\Helpers\DateHelper::format($payout->created_at) }}</td>
                        <td class="table-cell">
                            @if($payout->status === 'pending' || $payout->status === 'processing')
                            <button type="button" 
                                    onclick="openProcessModal('{{ route('admin.stores.process-payout', [$store, $payout->id]) }}', '{{ $payout->amount }}')" 
                                    class="btn-primary btn-sm">
                                Process
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-16 text-center">
                            <p class="font-medium text-gray-900">No payouts yet</p>
                            <p class="text-sm text-gray-500 mt-1">Payouts will appear here when created</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payouts->hasPages())<div class="px-5 py-4 border-t border-gray-100">{{ $payouts->links() }}</div>@endif
    </div>
</div>

<div id="payout-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Create Payout</h3>
        </div>
        <form action="{{ route('admin.stores.create-payout', $store) }}" method="POST">
            @csrf
            <div class="p-6 space-y-4">
                <div class="form-group">
                    <label class="label">Amount</label>
                    <input type="number" name="amount" step="0.01" min="0" max="{{ $store->available_balance ?? 0 }}" class="input" required>
                    <p class="form-hint">Available: <x-currency :amount="$store->available_balance ?? 0" /></p>
                </div>
                <div class="form-group">
                    <label class="label">Notes</label>
                    <textarea name="notes" class="input" rows="2"></textarea>
                </div>
            </div>
            <div class="p-6 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('payout-modal').classList.add('hidden')" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Create Payout</button>
            </div>
        </form>
    </div>
</div>
<div id="process-payout-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Process Payout</h3>
        </div>
        <form id="process-payout-form" method="POST">
            @csrf
            <div class="p-6 space-y-4">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <p class="text-sm text-blue-800">Processing payout of <span id="payout-amount-display" class="font-bold"></span></p>
                </div>
                <div class="form-group">
                    <label class="label">Transaction ID / Reference Number</label>
                    <input type="text" name="transaction_id" class="input" placeholder="Enter bank/UPI transaction ID" required>
                    <p class="form-hint">Enter the transaction ID from your bank or payment gateway.</p>
                </div>
            </div>
            <div class="p-6 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('process-payout-modal').classList.add('hidden')" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Mark as Completed</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openProcessModal(url, amount) {
        const modal = document.getElementById('process-payout-modal');
        const form = document.getElementById('process-payout-form');
        const display = document.getElementById('payout-amount-display');
        
        form.action = url;
        display.innerText = '₹' + amount; // Simple currency prefix for Indian context based on seeder
        modal.classList.remove('hidden');
    }
</script>

<!-- Bank Details Modal -->
<div id="bank-details-modal" class="hidden fixed inset-0 bg-black/50 z-[60] flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-white">
            <h3 class="text-xl font-bold text-gray-900">Store Bank Details</h3>
            <button onclick="document.getElementById('bank-details-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 transition-colors p-1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-8 space-y-6">
            <div class="grid grid-cols-1 gap-6">
                <div class="space-y-1">
                    <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">Account Holder Name</p>
                    <p class="text-base text-gray-900 font-semibold tracking-tight">{{ $store->bank_account_holder ?? 'Not Provided' }}</p>
                </div>
                <div class="space-y-1">
                    <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">Bank Name</p>
                    <p class="text-base text-gray-900 font-semibold tracking-tight">{{ $store->bank_name ?? 'Not Provided' }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-xl border border-gray-100 space-y-4">
                    <div class="space-y-1">
                        <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">Account Number</p>
                        <p class="text-lg text-gray-900 font-mono font-bold tracking-wider">{{ $store->bank_account_number ?? 'Not Provided' }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">IFSC Code</p>
                        <p class="text-lg text-gray-900 font-mono font-bold tracking-wider text-blue-600">{{ $store->bank_ifsc ?? 'Not Provided' }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="p-6 bg-gray-50 border-t border-gray-100 flex items-center justify-center">
            <button type="button" onclick="document.getElementById('bank-details-modal').classList.add('hidden')" class="btn-primary w-full py-3 shadow-lg shadow-blue-500/20">Close Details</button>
        </div>
    </div>
</div>
@endsection
