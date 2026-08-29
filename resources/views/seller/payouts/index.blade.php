@extends('seller.layouts.app')

@section('title', 'Treasury & Settlements: ' . $store->name)

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Compact Page Header -->
    <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('seller.dashboard') }}" class="group w-8 h-8 rounded border border-gray-200 flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:border-indigo-200 transition-all">
                <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-tight">Payout Ledger</h1>
                <p class="text-[10px] text-gray-500 font-medium uppercase tracking-wider">Financial settlement and net earnings history</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('seller.payouts.export') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded text-[10px] font-black text-gray-600 hover:bg-gray-50 uppercase tracking-widest transition-all shadow-sm">
                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export Statement
            </a>
            <form action="{{ route('seller.payouts.request') }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex items-center px-6 py-2 bg-indigo-600 border border-transparent rounded text-[10px] font-black text-white hover:bg-indigo-700 uppercase tracking-widest transition-all shadow-sm">
                    Request Settlement
                </button>
            </form>
        </div>

    </div>

    <!-- Settlement Metrics Card -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-gray-900 rounded-sm p-5 shadow-lg border border-gray-800">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] mb-3">Available Liquidity</p>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black text-white leading-none">{{ \App\Helpers\CurrencyHelper::format($availableBalance) }}</span>
            </div>
            <div class="mt-4 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-[9px] font-black text-emerald-500 uppercase tracking-widest">
                    {{ $availableBalance >= 100 ? 'Immediate Withdrawal Active' : 'Below Minimum Threshold' }}
                </span>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-sm p-5 shadow-sm">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Net Revenue (Delivered Items)</p>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-gray-900 leading-none">{{ \App\Helpers\CurrencyHelper::format($deliveredRevenue) }}</span>
            </div>
            <p class="text-[9px] text-gray-400 font-bold mt-4 uppercase tracking-tighter">Settled Unit Count: {{ $payouts->total() }}</p>
        </div>


        <div class="bg-white border border-gray-200 rounded-sm p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Verified Remittance Bank</p>
                <a href="{{ route('seller.settings.edit') }}" class="text-[9px] font-black text-indigo-600 uppercase tracking-widest hover:underline">Revise</a>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded bg-gray-50 border border-gray-200 flex items-center justify-center text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 033 3z"/></svg>
                </div>
                <div>
                    <h4 class="text-xs font-black text-gray-900 uppercase tracking-tight">{{ $store->bank_name ?? 'NOT REGISTERED' }}</h4>
                    <p class="text-[9px] font-bold text-gray-400 tracking-widest uppercase">ID: **** {{ substr($store->bank_account_number, -4) ?: '0000' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Settlement Record -->
    <div class="bg-white border border-gray-200 rounded-sm overflow-hidden shadow-sm">
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-[10px] font-black text-gray-700 uppercase tracking-widest">Remittance Log (Historical)</h3>
            <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Last Updated: {{ now()->format('H:i:s T') }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-white border-b border-gray-100">
                        <th class="px-4 py-3 text-[9px] font-black text-gray-400 uppercase tracking-widest">Reference ID</th>
                        <th class="px-4 py-3 text-[9px] font-black text-gray-400 uppercase tracking-widest">Execution Date</th>
                        <th class="px-4 py-3 text-[9px] font-black text-gray-400 uppercase tracking-widest">Net Amount</th>
                        <th class="px-4 py-3 text-[9px] font-black text-gray-400 uppercase tracking-widest">Lifecycle Status</th>
                        <th class="px-4 py-3 text-[9px] font-black text-gray-400 uppercase tracking-widest text-right">Entity Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 font-medium">
                    @forelse($payouts as $payout)
                    <tr class="hover:bg-gray-50/50 transition-all">
                        <td class="px-4 py-3">
                            <span class="text-xs font-black text-gray-900 uppercase tracking-widest">#{{ $payout->payout_id }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-xs font-bold text-gray-900">{{ $payout->created_at->format('d M Y') }}</p>
                            <p class="text-[8px] text-gray-400 font-black uppercase tracking-widest">{{ $payout->created_at->format('H:i') }} HRS</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-black text-gray-900">{{ \App\Helpers\CurrencyHelper::format($payout->amount) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($payout->status === 'completed')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-700 border border-emerald-100">Success</span>
                            @elseif($payout->status === 'pending')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest bg-amber-50 text-amber-700 border border-amber-100">In Review</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest bg-rose-50 text-rose-700 border border-rose-100">{{ $payout->status }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button class="inline-flex items-center justify-center w-6 h-6 rounded border border-gray-200 text-gray-400 hover:border-indigo-200 hover:text-indigo-600 transition-all">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-[10px] font-black text-gray-300 uppercase tracking-widest">No previous settlements registered</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payouts->hasPages())
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
            {{ $payouts->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
