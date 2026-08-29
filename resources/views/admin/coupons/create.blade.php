@extends('admin.layouts.app')
@section('title', 'Add Coupon')
@section('content')
<div class="space-y-5">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.coupons.index') }}" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Add Coupon</h1>
            <p class="text-sm text-gray-500 mt-1">Create a new discount coupon</p>
        </div>
    </div>

    <form action="{{ route('admin.coupons.store') }}" method="POST" class="card">
        @csrf
        <div class="card-body space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="form-group">
                    <label class="label">Coupon Code <span class="text-red-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code') }}" class="input font-mono uppercase" placeholder="e.g. SAVE20" required>
                    <p class="form-hint">Unique code customers will enter at checkout</p>
                </div>
                <div class="form-group">
                    <label class="label">Coupon Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="input" placeholder="e.g. Summer Sale 20% Off" required>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="form-group">
                    <label class="label">Discount Type <span class="text-red-500">*</span></label>
                    <select name="type" class="input" required>
                        <option value="percentage" {{ old('type') === 'percentage' ? 'selected' : '' }}>Percentage Discount</option>
                        <option value="fixed" {{ old('type') === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                        <option value="free_delivery" {{ old('type') === 'free_delivery' ? 'selected' : '' }}>Free Delivery</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="label">Discount Value <span class="text-red-500">*</span></label>
                    <input type="number" name="value" value="{{ old('value') }}" class="input" step="0.01" min="0" required>
                    <p class="form-hint">Enter % for percentage or $ amount</p>
                </div>
                <div class="form-group">
                    <label class="label">Max Discount</label>
                    <input type="number" name="max_discount" value="{{ old('max_discount') }}" class="input" step="0.01" min="0">
                    <p class="form-hint">Cap for percentage discounts</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="form-group">
                    <label class="label">Min Order Amount</label>
                    <input type="number" name="min_order_amount" value="{{ old('min_order_amount') }}" class="input" step="0.01" min="0">
                </div>
                <div class="form-group">
                    <label class="label">Usage Limit (Total)</label>
                    <input type="number" name="usage_limit" value="{{ old('usage_limit') }}" class="input" min="1">
                    <p class="form-hint">Leave empty for unlimited</p>
                </div>
                <div class="form-group">
                    <label class="label">Usage Limit Per User</label>
                    <input type="number" name="usage_limit_per_user" value="{{ old('usage_limit_per_user') }}" class="input" min="1">
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="form-group">
                    <label class="label">Start Date</label>
                    <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" class="input">
                </div>
                <div class="form-group">
                    <label class="label">Expiry Date</label>
                    <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}" class="input">
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="form-group">
                    <label class="label">Applicable Categories</label>
                    <select name="applicable_categories[]" multiple class="input" style="min-height: 100px;">
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <p class="form-hint">Leave empty for all categories</p>
                </div>
                <div class="form-group">
                    <label class="label">Applicable Stores</label>
                    <select name="applicable_stores[]" multiple class="input" style="min-height: 100px;">
                        @foreach($stores as $store)
                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                        @endforeach
                    </select>
                    <p class="form-hint">Leave empty for all stores</p>
                </div>
            </div>

            <div class="flex items-center gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" checked class="checkbox">
                    <span class="text-sm text-gray-700">Active</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_first_order_only" value="1" class="checkbox">
                    <span class="text-sm text-gray-700">First Order Only</span>
                </label>
            </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
            <a href="{{ route('admin.coupons.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Create Coupon</button>
        </div>
    </form>
</div>
@endsection
