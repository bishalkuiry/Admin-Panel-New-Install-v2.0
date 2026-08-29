@extends('seller.layouts.app')

@section('title', 'Feedback Registry: ' . $store->name)

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Compact Page Header -->
    <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('seller.dashboard') }}" class="group w-8 h-8 rounded border border-gray-200 flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:border-indigo-200 transition-all">
                <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-tight">Customer Feedback Registry</h1>
                <p class="text-[10px] text-gray-500 font-medium uppercase tracking-wider">Public sentiment analysis and product quality indices</p>
            </div>
        </div>
        
        <div class="flex items-center gap-4 bg-white border border-gray-200 rounded-sm shadow-sm overflow-hidden">
            <div class="px-4 py-2 text-center border-r border-gray-100">
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Entity Rating</p>
                <p class="text-lg font-black text-gray-900 leading-none">{{ number_format($store->rating, 1) }}</p>
            </div>
            <div class="px-4 py-2 text-center">
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Entry Vol</p>
                <p class="text-lg font-black text-gray-900 leading-none">{{ $store->rating_count }}</p>
            </div>
        </div>
    </div>

    <!-- Reviews Ledger -->
    <div class="space-y-4">
        @forelse($reviews as $review)
        <div class="bg-white border border-gray-200 rounded-sm p-5 shadow-sm hover:border-indigo-200 transition-all flex flex-col md:flex-row gap-6">
            <!-- Counterparty Identity -->
            <div class="md:w-48 shrink-0 flex md:flex-col items-center md:items-start md:border-r border-gray-100 pr-0 md:pr-6 gap-3 md:gap-2">
                <div class="w-10 h-10 rounded-sm bg-gray-900 text-white flex items-center justify-center font-black text-sm">
                    {{ substr($review->user->name, 0, 1) }}
                </div>
                <div>
                    <p class="text-xs font-black text-gray-900 uppercase tracking-tight truncate max-w-[140px]">{{ $review->user->name }}</p>
                    <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest">{{ $review->created_at->format('d M Y') }}</p>
                </div>
            </div>

            <!-- Feedback Parameters -->
            <div class="flex-1 space-y-3">
                <div class="flex items-center gap-1.5">
                    <div class="flex items-center gap-0.5">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="w-3 h-3 {{ $i <= $review->rating ? 'text-amber-500 fill-amber-500' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    @if(!$review->is_approved)
                        <span class="px-2 py-0.5 bg-amber-50 text-amber-700 text-[8px] font-black uppercase tracking-widest border border-amber-100 rounded">Pending Verification</span>
                    @endif
                </div>
                
                <blockquote class="text-xs font-medium text-gray-700 leading-relaxed border-l-2 border-gray-100 pl-4 py-1 italic">
                    "{{ $review->comment }}"
                </blockquote>
                
                @if($review->product)
                <div class="flex items-center gap-2 pt-2">
                    <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Linked Article:</span>
                    <a href="{{ route('seller.products.edit', $review->product) }}" class="text-[9px] font-black text-indigo-600 hover:text-indigo-800 uppercase tracking-widest underline decoration-indigo-200 underline-offset-4">{{ $review->product->name }}</a>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="bg-white border border-gray-200 rounded-sm p-12 text-center shadow-sm">
            <p class="text-[10px] font-black text-gray-300 uppercase tracking-[0.3em]">No qualitative data available</p>
        </div>
        @endforelse
    </div>

    @if($reviews->hasPages())
    <div class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-sm">
        {{ $reviews->links() }}
    </div>
    @endif
</div>
@endsection
