@extends('admin.layouts.app')

@section('title', 'Create Seller Advertisement Plan')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Create Paid Seller Ad Plan</h1>
            <p class="text-xs text-gray-500 mt-1">Configure advertisement package specifications for vendors.</p>
        </div>
        <a href="{{ route('admin.ad-plans.index') }}" class="btn-secondary">Back to Ad Plans</a>
    </div>

    <form action="{{ route('admin.ad-plans.store') }}" method="POST" class="card p-6 space-y-6">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="label text-xs">Plan Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required value="{{ old('name') }}" class="input text-xs" placeholder="e.g. Premium Home Banner 7 Days">
            </div>
            <div>
                <label class="label text-xs">Ad Type <span class="text-red-500">*</span></label>
                <select name="ad_type" required class="input text-xs">
                    <option value="banner_ad">Banner Ad</option>
                    <option value="featured_store">Featured Store Listing</option>
                    <option value="sponsored_product">Sponsored Product</option>
                </select>
            </div>
            <div>
                <label class="label text-xs">Price (₹) <span class="text-red-500">*</span></label>
                <input type="number" step="0.01" name="price" required value="{{ old('price', 999) }}" class="input text-xs">
            </div>
            <div>
                <label class="label text-xs">Billing Type <span class="text-red-500">*</span></label>
                <select name="billing_type" required class="input text-xs">
                    <option value="one_time">One Time</option>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                </select>
            </div>
            <div>
                <label class="label text-xs">Duration (Days) <span class="text-red-500">*</span></label>
                <input type="number" name="duration_days" required value="{{ old('duration_days', 7) }}" class="input text-xs">
            </div>
            <div>
                <label class="label text-xs">Placement Slot <span class="text-red-500">*</span></label>
                <input type="text" name="placement" required value="{{ old('placement', 'home_top_banner') }}" class="input text-xs" placeholder="home_top_banner, category_sponsor, product_feed">
            </div>
            <div>
                <label class="label text-xs">Impression Limit (Leave blank for unlimited)</label>
                <input type="number" name="impression_limit" value="{{ old('impression_limit') }}" class="input text-xs" placeholder="e.g. 50000">
            </div>
            <div>
                <label class="label text-xs">Click Limit (Leave blank for unlimited)</label>
                <input type="number" name="click_limit" value="{{ old('click_limit') }}" class="input text-xs" placeholder="e.g. 1000">
            </div>
            <div>
                <label class="label text-xs">Priority Level <span class="text-red-500">*</span></label>
                <input type="number" name="priority_level" required value="{{ old('priority_level', 1) }}" class="input text-xs" placeholder="1 = High, 10 = Low">
            </div>
            <div>
                <label class="label text-xs">Banner Image Recommended Dimensions</label>
                <input type="text" name="banner_size" value="{{ old('banner_size', '1200x400 px') }}" class="input text-xs">
            </div>
            <div>
                <label class="label text-xs">Max Stores / Products Limit <span class="text-red-500">*</span></label>
                <input type="number" name="max_items" required value="{{ old('max_items', 1) }}" class="input text-xs">
            </div>
            <div>
                <label class="label text-xs">Sort Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" class="input text-xs">
            </div>
        </div>

        <div>
            <label class="label text-xs">Plan Description</label>
            <textarea name="description" rows="3" class="input text-xs" placeholder="Describe where this ad will display and expected impression reach...">{{ old('description') }}</textarea>
        </div>

        <div class="flex items-center gap-6 p-4 bg-gray-50 rounded-xl border border-gray-200">
            <label class="flex items-center gap-2 text-xs font-bold cursor-pointer">
                <input type="checkbox" name="auto_renew" value="1" class="rounded text-orange-600">
                <span>Auto-Renewal Supported</span>
            </label>
            <label class="flex items-center gap-2 text-xs font-bold cursor-pointer">
                <input type="checkbox" name="status" value="1" checked class="rounded text-orange-600">
                <span>Active Plan</span>
            </label>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.ad-plans.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Create Ad Plan</button>
        </div>
    </form>
</div>
@endsection
