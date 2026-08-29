@extends('admin.layouts.app')

@section('title', 'Edit Seller Advertisement Plan')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Edit Paid Seller Ad Plan: {{ $plan->name }}</h1>
            <p class="text-xs text-gray-500 mt-1">Update package specifications and pricing.</p>
        </div>
        <a href="{{ route('admin.ad-plans.index') }}" class="btn-secondary">Back to Ad Plans</a>
    </div>

    <form action="{{ route('admin.ad-plans.update', $plan) }}" method="POST" class="card p-6 space-y-6">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="label text-xs">Plan Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required value="{{ old('name', $plan->name) }}" class="input text-xs">
            </div>
            <div>
                <label class="label text-xs">Ad Type <span class="text-red-500">*</span></label>
                <select name="ad_type" required class="input text-xs">
                    <option value="banner_ad" {{ old('ad_type', $plan->ad_type) == 'banner_ad' ? 'selected' : '' }}>Banner Ad</option>
                    <option value="featured_store" {{ old('ad_type', $plan->ad_type) == 'featured_store' ? 'selected' : '' }}>Featured Store Listing</option>
                    <option value="sponsored_product" {{ old('ad_type', $plan->ad_type) == 'sponsored_product' ? 'selected' : '' }}>Sponsored Product</option>
                </select>
            </div>
            <div>
                <label class="label text-xs">Price (₹) <span class="text-red-500">*</span></label>
                <input type="number" step="0.01" name="price" required value="{{ old('price', $plan->price) }}" class="input text-xs">
            </div>
            <div>
                <label class="label text-xs">Billing Type <span class="text-red-500">*</span></label>
                <select name="billing_type" required class="input text-xs">
                    <option value="one_time" {{ old('billing_type', $plan->billing_type) == 'one_time' ? 'selected' : '' }}>One Time</option>
                    <option value="daily" {{ old('billing_type', $plan->billing_type) == 'daily' ? 'selected' : '' }}>Daily</option>
                    <option value="weekly" {{ old('billing_type', $plan->billing_type) == 'weekly' ? 'selected' : '' }}>Weekly</option>
                    <option value="monthly" {{ old('billing_type', $plan->billing_type) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                </select>
            </div>
            <div>
                <label class="label text-xs">Duration (Days) <span class="text-red-500">*</span></label>
                <input type="number" name="duration_days" required value="{{ old('duration_days', $plan->duration_days) }}" class="input text-xs">
            </div>
            <div>
                <label class="label text-xs">Placement Slot <span class="text-red-500">*</span></label>
                <input type="text" name="placement" required value="{{ old('placement', $plan->placement) }}" class="input text-xs">
            </div>
            <div>
                <label class="label text-xs">Impression Limit</label>
                <input type="number" name="impression_limit" value="{{ old('impression_limit', $plan->impression_limit) }}" class="input text-xs">
            </div>
            <div>
                <label class="label text-xs">Click Limit</label>
                <input type="number" name="click_limit" value="{{ old('click_limit', $plan->click_limit) }}" class="input text-xs">
            </div>
            <div>
                <label class="label text-xs">Priority Level <span class="text-red-500">*</span></label>
                <input type="number" name="priority_level" required value="{{ old('priority_level', $plan->priority_level) }}" class="input text-xs">
            </div>
            <div>
                <label class="label text-xs">Banner Image Recommended Dimensions</label>
                <input type="text" name="banner_size" value="{{ old('banner_size', $plan->banner_size) }}" class="input text-xs">
            </div>
            <div>
                <label class="label text-xs">Max Stores / Products Limit <span class="text-red-500">*</span></label>
                <input type="number" name="max_items" required value="{{ old('max_items', $plan->max_items) }}" class="input text-xs">
            </div>
            <div>
                <label class="label text-xs">Sort Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $plan->sort_order) }}" class="input text-xs">
            </div>
        </div>

        <div>
            <label class="label text-xs">Plan Description</label>
            <textarea name="description" rows="3" class="input text-xs">{{ old('description', $plan->description) }}</textarea>
        </div>

        <div class="flex items-center gap-6 p-4 bg-gray-50 rounded-xl border border-gray-200">
            <label class="flex items-center gap-2 text-xs font-bold cursor-pointer">
                <input type="checkbox" name="auto_renew" value="1" {{ old('auto_renew', $plan->auto_renew) ? 'checked' : '' }} class="rounded text-orange-600">
                <span>Auto-Renewal Supported</span>
            </label>
            <label class="flex items-center gap-2 text-xs font-bold cursor-pointer">
                <input type="checkbox" name="status" value="1" {{ old('status', $plan->status) ? 'checked' : '' }} class="rounded text-orange-600">
                <span>Active Plan</span>
            </label>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.ad-plans.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Update Ad Plan</button>
        </div>
    </form>
</div>
@endsection
