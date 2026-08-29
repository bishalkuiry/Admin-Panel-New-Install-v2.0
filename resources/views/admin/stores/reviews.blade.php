@extends('admin.layouts.app')

@section('title', $store->name . ' - Ratings & Reviews')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">{{ $store->name }}</h1>
            <p class="text-xs text-gray-500 mt-1">Customer Ratings &amp; Reviews Overview</p>
        </div>
    </div>

    @include('admin.stores._tabs')

    <!-- Summary Card -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="card p-5 bg-gradient-to-br from-amber-50 to-orange-50 border-amber-200">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-amber-500 text-white rounded-xl">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                </div>
                <div>
                    <span class="text-xs text-amber-700 font-semibold block">Average Rating</span>
                    <span class="text-2xl font-bold text-gray-900">{{ number_format($avgRating, 1) }} / 5.0</span>
                </div>
            </div>
        </div>

        <div class="card p-5 bg-white">
            <span class="text-xs text-gray-500 font-medium block">Total Customer Reviews</span>
            <span class="text-2xl font-bold text-gray-900 mt-1 block">{{ number_format($totalReviews) }}</span>
        </div>
    </div>

    <!-- Reviews List -->
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900">All Reviews ({{ $totalReviews }})</h3>
        </div>

        @if($reviews->count() > 0)
        <div class="divide-y divide-gray-200">
            @foreach($reviews as $rev)
            <div class="p-4 hover:bg-gray-50/50 transition">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-orange-100 text-orange-600 font-bold flex items-center justify-center text-sm">
                            {{ strtoupper(substr($rev->user->name ?? 'C', 0, 1)) }}
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-gray-900">{{ $rev->user->name ?? 'Customer' }}</div>
                            <div class="text-xs text-gray-400 mt-0.5">
                                {{ $rev->created_at ? $rev->created_at->format('d M Y, h:i A') : '' }}
                                @if($rev->product)
                                 • Product: <span class="font-medium text-gray-700">{{ $rev->product->name }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-1 text-amber-500 font-bold text-sm bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200">
                        ⭐ {{ $rev->rating }}.0
                    </div>
                </div>

                @if($rev->comment)
                <p class="text-xs text-gray-600 mt-3 leading-relaxed pl-13">
                    "{{ $rev->comment }}"
                </p>
                @endif
            </div>
            @endforeach
        </div>

        <div class="p-4 border-t border-gray-200">
            {{ $reviews->links() }}
        </div>
        @else
        <div class="p-12 text-center text-gray-500 text-xs">
            No customer reviews found for this store yet.
        </div>
        @endif
    </div>
</div>
@endsection
