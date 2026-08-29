@extends('admin.layouts.app')
@section('title', 'Edit Coupon')
@section('content')
<div class="space-y-5">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.coupons.index') }}" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Edit Coupon</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $coupon->code }}</p>
        </div>
    </div>

    <form action="{{ route('admin.coupons.update', $coupon) }}" method="POST" class="card">
        @csrf @method('PUT')
        <div class="card-body space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="form-group">
                    <label class="label">Coupon Code <span class="text-red-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code', $coupon->code) }}" class="input font-mono uppercase" required>
                </div>
                <div class="form-group">
                    <label class="label">Coupon Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $coupon->name) }}" class="input" required>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="form-group">
                    <label class="label">Discount Type <span class="text-red-500">*</span></label>
                    <select name="type" class="input" required>
                        <option value="percentage" {{ $coupon->type === 'percentage' ? 'selected' : '' }}>Percentage Discount</option>
                        <option value="fixed" {{ $coupon->type === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                        <option value="free_delivery" {{ $coupon->type === 'free_delivery' ? 'selected' : '' }}>Free Delivery</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="label">Discount Value <span class="text-red-500">*</span></label>
                    <input type="number" name="value" value="{{ old('value', $coupon->value) }}" class="input" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label class="label">Max Discount</label>
                    <input type="number" name="max_discount" value="{{ old('max_discount', $coupon->max_discount) }}" class="input" step="0.01" min="0">
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="form-group">
                    <label class="label">Min Order Amount</label>
                    <input type="number" name="min_order_amount" value="{{ old('min_order_amount', $coupon->min_order_amount) }}" class="input" step="0.01" min="0">
                </div>
                <div class="form-group">
                    <label class="label">Usage Limit (Total)</label>
                    <input type="number" name="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit) }}" class="input" min="1">
                    <p class="form-hint">Used: {{ $coupon->used_count }} times</p>
                </div>
                <div class="form-group">
                    <label class="label">Usage Limit Per User</label>
                    <input type="number" name="usage_limit_per_user" value="{{ old('usage_limit_per_user', $coupon->usage_limit_per_user) }}" class="input" min="1">
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="form-group">
                    <label class="label">Start Date</label>
                    <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $coupon->starts_at?->format('Y-m-d\TH:i')) }}" class="input">
                </div>
                <div class="form-group">
                    <label class="label">Expiry Date</label>
                    <input type="datetime-local" name="expires_at" value="{{ old('expires_at', $coupon->expires_at?->format('Y-m-d\TH:i')) }}" class="input">
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="form-group">
                    <label class="label">Applicable Categories</label>
                    <select name="applicable_categories[]" multiple class="input" style="min-height: 100px;">
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ in_array($category->id, $coupon->applicable_categories ?? []) ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="label">Applicable Stores</label>
                    <select name="applicable_stores[]" multiple class="input" style="min-height: 100px;">
                        @foreach($stores as $store)
                        <option value="{{ $store->id }}" {{ in_array($store->id, $coupon->applicable_stores ?? []) ? 'selected' : '' }}>{{ $store->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ $coupon->is_active ? 'checked' : '' }} class="checkbox">
                    <span class="text-sm text-gray-700">Active</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_first_order_only" value="1" {{ $coupon->is_first_order_only ? 'checked' : '' }} class="checkbox">
                    <span class="text-sm text-gray-700">First Order Only</span>
                </label>
            </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
            <a href="{{ route('admin.coupons.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Update Coupon</button>
        </div>
    </form>
</div>
@endsection
