@extends('admin.layouts.app')

@section('title', 'Ratings & Review Moderation')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Ratings & Review Moderation</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">Moderate customer reviews for Products, Stores & Delivery Partners with text & image attachments</p>
        </div>
    </div>

    <!-- Display & Visibility Controls -->
    <div class="card p-5 bg-gradient-to-r from-orange-50/60 to-amber-50/60 border border-orange-200/80">
        <form action="{{ route('admin.reviews.settings.update') }}" method="POST" class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            @csrf
            <div>
                <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    <span>App Customer Display Settings (Product Rating & Review Badges)</span>
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Control whether Star Ratings and Review Counts are shown on Product Details pages in the Customer Mobile App</p>
            </div>
            <div class="flex items-center gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="show_star_rating" value="1" {{ \App\Models\Setting::get('show_star_rating', '1') == '1' ? 'checked' : '' }} class="rounded text-orange-600 focus:ring-orange-500">
                    <span class="text-xs font-semibold text-gray-800">Show Star Rating (e.g. ★ 4.5)</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="show_review_count" value="1" {{ \App\Models\Setting::get('show_review_count', '1') == '1' ? 'checked' : '' }} class="rounded text-orange-600 focus:ring-orange-500">
                    <span class="text-xs font-semibold text-gray-800">Show Review Count (e.g. 128 Reviews)</span>
                </label>
                <button type="submit" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-semibold text-xs rounded-lg transition-colors shadow-sm">
                    Save Settings
                </button>
            </div>
        </form>
    </div>

    <!-- Filters -->
    <div class="card p-4">
        <form action="{{ route('admin.reviews.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <select name="type" class="input text-xs">
                    <option value="">All Review Targets (Product / Store / Rider)</option>
                    <option value="product" {{ request('type') == 'product' ? 'selected' : '' }}>Product Reviews</option>
                    <option value="store" {{ request('type') == 'store' ? 'selected' : '' }}>Store Ratings</option>
                    <option value="driver" {{ request('type') == 'driver' ? 'selected' : '' }}>Delivery Partner Ratings</option>
                </select>
            </div>
            <div>
                <select name="status" class="input text-xs">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Review</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="btn btn-primary text-xs w-full">Filter</button>
                <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary text-xs">Reset</a>
            </div>
        </form>
    </div>

    <!-- Reviews Table -->
    <div class="card overflow-hidden p-4 sm:p-5">
        <div class="overflow-x-auto">
            <table class="table w-full text-xs min-w-[650px]">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Customer</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Target</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Rating</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Review & Media</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Status</th>
                        <th class="text-right py-3 px-4 font-bold text-gray-700">Moderation Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($reviews as $rev)
                        <tr class="hover:bg-gray-50/50">
                            <td class="py-3 px-4">
                                <p class="font-bold text-gray-900">{{ $rev->user?->name ?? 'User #'.$rev->user_id }}</p>
                                <p class="text-[10px] text-gray-500">Order #{{ $rev->order?->order_number ?? $rev->order_id }}</p>
                            </td>
                            <td class="py-3 px-4">
                                @if($rev->product)
                                    <p class="font-semibold text-gray-900 text-xs">{{ $rev->product->name }}</p>
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-blue-50 text-blue-700 border border-blue-100">PRODUCT #{{ $rev->product_id }}</span>
                                @elseif($rev->delivery_rating)
                                    <p class="font-semibold text-gray-900 text-xs">Delivery Experience</p>
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-purple-50 text-purple-700 border border-purple-100">DELIVERY ({{ $rev->delivery_rating }}★)</span>
                                @else
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-gray-100 text-gray-700">GENERAL</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-bold text-amber-500">
                                {{ str_repeat('★', max(1, min(5, (int)$rev->rating))) }}{{ str_repeat('☆', 5 - max(1, min(5, (int)$rev->rating))) }}
                                <span class="text-gray-900 text-xs ml-1">({{ $rev->rating }}/5)</span>
                            </td>
                            <td class="py-3 px-4 max-w-xs">
                                <p class="text-gray-800 text-xs">{{ $rev->comment ?? 'No comment written' }}</p>
                                @if(!empty($rev->images) && is_array($rev->images))
                                    <div class="flex gap-1.5 mt-2">
                                        @foreach($rev->images as $img)
                                            <a href="{{ storage_url($img) }}" target="_blank" class="w-8 h-8 rounded border overflow-hidden block">
                                                <img src="{{ storage_url($img) }}" class="w-full h-full object-cover">
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $rev->is_approved ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $rev->is_approved ? 'Approved' : 'Pending' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <form action="{{ route('admin.reviews.update-status', $rev->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        <select name="status" onchange="this.form.submit()" class="input text-[10px] py-0.5 px-2">
                                            <option value="pending" {{ !$rev->is_approved ? 'selected' : '' }}>Pending</option>
                                            <option value="approved" {{ $rev->is_approved ? 'selected' : '' }}>Approve</option>
                                        </select>
                                    </form>
                                    <form action="{{ route('admin.reviews.destroy', $rev->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this review?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 p-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-gray-400">No customer reviews submitted yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reviews->hasPages())
            <div class="pt-3 border-t">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
