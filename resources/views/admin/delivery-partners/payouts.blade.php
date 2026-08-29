@extends('admin.layouts.app')
@section('title', $deliveryPartner->name . ' - Payouts')
@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.delivery-partners.index') }}" class="p-2 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900 tracking-wide">{{ $deliveryPartner->name }}</h1>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mt-1">Payout Management</p>
            </div>
        </div>
        <x-permission-btn 
            permission="wallets.manage" 
            onclick="openPayoutModal()" 
            class="px-5 py-2.5 bg-gray-900 text-white rounded-lg text-sm font-bold uppercase tracking-widest hover:bg-black transition-all shadow-md active:scale-[0.98]" 
            label="Create Payout"
            disabled="{{ $deliveryPartner->payout_balance <= 0 ? 'true' : 'false' }}"
        />
    </div>

    @include('admin.delivery-partners._tabs', ['deliveryPartner' => $deliveryPartner])

    <!-- Balance Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="card bg-white border border-gray-200 shadow-sm rounded-xl p-6">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Available Balance</h3>
            <div class="text-3xl font-bold text-gray-900"><x-currency :amount="$deliveryPartner->payout_balance" /></div>
            @if($deliveryPartner->payout_balance > 0)
                <div class="mt-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                    <span class="text-xs font-medium text-green-600">Ready for payout</span>
                </div>
            @else
                <div class="mt-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-gray-300"></span>
                    <span class="text-xs font-medium text-gray-500">No funds available</span>
                </div>
            @endif
        </div>

        <div class="card bg-white border border-gray-200 shadow-sm rounded-xl p-6">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Total Paid</h3>
            <div class="text-3xl font-bold text-gray-900"><x-currency :amount="$deliveryPartner->payouts()->where('status', 'completed')->sum('amount')" /></div>
            <p class="text-xs text-gray-500 mt-2">Lifetime earnings paid out</p>
        </div>

        <div class="card bg-white border border-gray-200 shadow-sm rounded-xl p-6">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Pending Requests</h3>
            <div class="text-3xl font-bold text-gray-900"><x-currency :amount="$deliveryPartner->payouts()->where('status', 'pending')->sum('amount')" /></div>
             <p class="text-xs text-gray-500 mt-2">Currently processing</p>
        </div>
    </div>

    <!-- Payout History -->
    <div class="card bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
            <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider">Payout History</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-white border-b border-gray-100 text-[10px] text-gray-400 font-bold uppercase tracking-widest">
                    <tr>
                        <th class="px-6 py-4">Transaction ID</th>
                        <th class="px-6 py-4">Amount</th>
                        <th class="px-6 py-4">Method</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($payouts as $payout)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 font-mono text-xs font-semibold text-gray-600">
                            {{ $payout->transaction_id ?? 'TRANS-'.strtoupper(Str::random(8)) }}
                        </td>
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                            <x-currency :amount="$payout->amount" />
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-medium text-gray-700 uppercase tracking-wide px-2 py-1 bg-gray-100 rounded">
                                {{ $payout->payment_method }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($payout->status == 'completed')
                                <span class="inline-flex px-2.5 py-1 rounded border border-green-200 text-[10px] font-bold uppercase tracking-wider bg-green-50 text-green-700">Completed</span>
                            @else
                                <span class="inline-flex px-2.5 py-1 rounded border border-gray-100 text-[10px] font-bold uppercase tracking-wider bg-gray-50 text-gray-600">{{ ucfirst($payout->status) }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right text-xs text-gray-500">
                            {{ \App\Helpers\DateHelper::format($payout->created_at, true) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-xs text-gray-500 italic">
                            No payout history found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payouts->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $payouts->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Payout Modal -->
<div id="payoutModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" onclick="closePayoutModal()"></div>
        
        <div class="relative bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-md sm:w-full border border-gray-100">
            <div class="bg-gray-50/80 px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900 tracking-wide">Create Payout</h3>
                <button type="button" onclick="closePayoutModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <form action="{{ route('admin.delivery-partners.payouts.process', $deliveryPartner) }}" method="POST" class="p-6 space-y-5">
                @csrf
                <div>
                     <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                     <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-bold">₹</span>
                        <input type="number" name="amount" step="0.01" max="{{ $deliveryPartner->payout_balance }}" value="{{ $deliveryPartner->payout_balance }}" required class="w-full pl-8 pr-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-bold">
                     </div>
                     <p class="text-xs text-gray-500 mt-1">Available: <x-currency :amount="$deliveryPartner->payout_balance" /></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                    <select name="payment_method" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all appearance-none cursor-pointer">
                        <option value="bank">Direct Bank Transfer</option>
                        <option value="upi">UPI Transfer</option>
                        <option value="cash">Cash Payment</option>
                    </select>
                </div>

                @if($deliveryPartner->bank_account_number)
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-100 space-y-2">
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500">Bank Name</span>
                        <span class="font-semibold text-gray-900">{{ $deliveryPartner->bank_name }}</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500">Account No</span>
                        <span class="font-mono font-semibold text-gray-900">•••• {{ substr($deliveryPartner->bank_account_number, -4) }}</span>
                    </div>
                </div>
                @else
                <div class="p-3 bg-amber-50 text-amber-700 rounded-lg text-xs font-medium border border-amber-100">
                    Warning: No bank details linked. Verify manually.
                </div>
                @endif

                <div class="flex gap-3 pt-2">
                    <x-permission-btn 
                        permission="wallets.manage" 
                        type="submit"
                        class="flex-1 py-2.5 bg-gray-900 text-white rounded-lg text-sm font-bold uppercase tracking-widest hover:bg-black transition-all shadow-md active:scale-[0.98]" 
                        label="Process Payout"
                    />
                    <button type="button" onclick="closePayoutModal()" class="flex-1 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-lg text-sm font-bold uppercase tracking-widest hover:bg-gray-50 transition-all">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openPayoutModal() {
        const modal = document.getElementById('payoutModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closePayoutModal() {
         const modal = document.getElementById('payoutModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endsection
