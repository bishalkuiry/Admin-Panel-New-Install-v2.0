@extends('admin.layouts.app')
@section('title', 'Create Tax Rule')
@section('content')

<form action="{{ route('admin.taxes.store') }}" method="POST" x-data="{ calcType: '{{ old('calculation_type', 'percentage') }}' }" class="space-y-6">
    @csrf

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.taxes.index') }}" class="p-2 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-500 shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900 tracking-wide">Create Customer Tax</h1>
                <p class="text-sm text-gray-500 mt-1">Add a new tax that will be applied to customer orders at checkout</p>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.taxes.index') }}" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 rounded-lg text-sm font-bold uppercase tracking-wider transition-all shadow-sm">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2.5 bg-gray-900 text-white rounded-lg text-sm font-bold uppercase tracking-wider hover:bg-black transition-all shadow-md active:scale-[0.98] flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Create Rule
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Core Configuration -->
        <div class="card bg-white border border-gray-200 shadow-sm rounded-xl p-6 h-fit">
            <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider mb-6 border-b border-gray-100 pb-4">Tax Configuration</h3>
            
            <div class="space-y-5">
                {{-- Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tax Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all" placeholder="e.g. GST 18%, Service Tax" required>
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Calculation Type & Value --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Calculation Method <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <select name="calculation_type" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all appearance-none cursor-pointer" x-model="calcType" required>
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed">Fixed Amount ({{ $currencySymbol }})</option>
                            </select>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                        @error('calculation_type')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tax Value <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-medium text-sm" x-text="calcType === 'percentage' ? '%' : '{{ $currencySymbol }}'"></span>
                            <input type="number" name="value" value="{{ number_format(old('value', 0), $priceDecimals, '.', '') }}" class="w-full px-8 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold" step="{{ $priceStep }}" min="0" required>
                        </div>
                        @error('value')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Inclusive --}}
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-xl bg-gray-50/50">
                    <div>
                        <h4 class="font-semibold text-gray-900 text-sm">Tax Inclusive</h4>
                        <p class="text-xs text-gray-500 mt-0.5">Price already includes this tax (won't add extra to total)</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_inclusive" value="1" {{ old('is_inclusive') ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gray-900"></div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Order Value Conditions -->
        <div class="card bg-white border border-gray-200 shadow-sm rounded-xl p-6 h-fit">
            <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider mb-6 border-b border-gray-100 pb-4">Order Value Conditions</h3>
            
            <div class="space-y-5">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Min Order Value</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">{{ $currencySymbol }}</span>
                            <input type="number" name="min_order_value" value="{{ old('min_order_value') ? number_format(old('min_order_value'), $priceDecimals, '.', '') : '' }}" class="w-full px-7 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all" step="{{ $priceStep }}" min="0" placeholder="0.00">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Max Order Value</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">{{ $currencySymbol }}</span>
                            <input type="number" name="max_order_value" value="{{ old('max_order_value') ? number_format(old('max_order_value'), $priceDecimals, '.', '') : '' }}" class="w-full px-7 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all" step="{{ $priceStep }}" min="0" placeholder="No Limit">
                        </div>
                    </div>
                </div>
                <p class="text-xs text-gray-400">Apply this tax only when the order subtotal falls within this range. Leave both empty to apply to all orders.</p>

                <!-- Info Card -->
                <div class="p-4 bg-blue-50 rounded-xl border border-blue-100">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <p class="text-sm font-semibold text-blue-900">How Customer Tax Works</p>
                            <p class="text-xs text-blue-700 mt-1">Customer taxes are added on top of the order subtotal at checkout. If set as "inclusive", the tax is shown as part of the price but not added extra. Multiple tax rules can stack.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
