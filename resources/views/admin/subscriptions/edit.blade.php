@extends('admin.layouts.app')

@section('title', 'Edit Subscription Plan')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Edit Subscription Plan: {{ $plan->name }}</h1>
            <p class="text-xs text-gray-500 mt-1">Update plan parameters, pricing, and limits.</p>
        </div>
        <a href="{{ route('admin.subscriptions.index') }}" class="btn-secondary">Back to Plans</a>
    </div>

    <form action="{{ route('admin.subscriptions.update', $plan) }}" method="POST" class="card p-6 space-y-6">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="label text-xs">Plan Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required value="{{ old('name', $plan->name) }}" class="input text-xs">
            </div>
            <div>
                <label class="label text-xs">Billing Cycle <span class="text-red-500">*</span></label>
                <select name="billing_cycle" required class="input text-xs">
                    <option value="monthly" {{ old('billing_cycle', $plan->billing_cycle) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                    <option value="yearly" {{ old('billing_cycle', $plan->billing_cycle) == 'yearly' ? 'selected' : '' }}>Yearly</option>
                    <option value="trial" {{ old('billing_cycle', $plan->billing_cycle) == 'trial' ? 'selected' : '' }}>Trial Period</option>
                </select>
            </div>
            <div>
                <label class="label text-xs">Price (₹) <span class="text-red-500">*</span></label>
                <input type="number" step="0.01" name="price" required value="{{ old('price', $plan->price) }}" class="input text-xs">
            </div>
            <div>
                <label class="label text-xs">Duration (Days) <span class="text-red-500">*</span></label>
                <input type="number" name="duration_days" required value="{{ old('duration_days', $plan->duration_days) }}" class="input text-xs">
            </div>
            <div>
                <label class="label text-xs">Max Products Limit <span class="text-red-500">*</span></label>
                <input type="number" name="max_products" required value="{{ old('max_products', $plan->max_products) }}" class="input text-xs">
            </div>
            <div>
                <label class="label text-xs">Commission Rate (%) <span class="text-red-500">*</span></label>
                <input type="number" step="0.1" name="commission_rate" required value="{{ old('commission_rate', $plan->commission_rate) }}" class="input text-xs">
            </div>
        </div>

        <div>
            <label class="label text-xs">Plan Description</label>
            <textarea name="description" rows="3" class="input text-xs">{{ old('description', $plan->description) }}</textarea>
        </div>

        <div class="flex items-center gap-6 p-4 bg-gray-50 rounded-xl border border-gray-200">
            <label class="flex items-center gap-2 text-xs font-bold cursor-pointer">
                <input type="checkbox" name="auto_renew" value="1" {{ old('auto_renew', $plan->auto_renew) ? 'checked' : '' }} class="rounded text-orange-600">
                <span>Auto-Renewal Enabled</span>
            </label>
            <label class="flex items-center gap-2 text-xs font-bold cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $plan->is_active) ? 'checked' : '' }} class="rounded text-orange-600">
                <span>Active Status</span>
            </label>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.subscriptions.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Update Plan</button>
        </div>
    </form>
</div>
@endsection
