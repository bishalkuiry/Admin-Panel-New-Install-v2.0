@extends('admin.layouts.app')
@section('title', 'Wallet Settings')
@section('content')
<div class="space-y-5">
    <div>
        <h1 class="text-xl font-semibold text-gray-900">Wallet Settings</h1>
        <p class="text-sm text-gray-500 mt-1">Configure wallet system settings and limits</p>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center gap-3">
        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
        <p class="font-medium mb-2">Please fix the following errors:</p>
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
            <li class="text-sm">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.settings.wallet.update') }}" method="POST" class="space-y-5">
        @csrf
        @method('PUT')

        <!-- Signup Bonus Settings -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Signup Bonus</h3>
            </div>
            <div class="card-body space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="font-medium text-gray-900">Enable Signup Bonus</label>
                        <p class="text-sm text-gray-500 mt-1">Give new customers a bonus when they register</p>
                    </div>
                    <label class="toggle">
                        <input type="checkbox" name="signup_bonus_enabled" value="1" {{ old('signup_bonus_enabled', $settings['signup_bonus_enabled'] ?? false) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Signup Bonus Amount</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">{{ \App\Helpers\CurrencyHelper::getSymbol() }}</span>
                        <input type="number" name="signup_bonus_amount" step="0.01" min="0" max="1000" value="{{ old('signup_bonus_amount', $settings['signup_bonus_amount'] ?? 0) }}" class="input pl-8" placeholder="0.00">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Amount credited to new customer wallets upon registration</p>
                </div>
            </div>
        </div>

        <!-- Top-Up Settings -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Top-Up Limits</h3>
            </div>
            <div class="card-body space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Top-Up Amount</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">{{ \App\Helpers\CurrencyHelper::getSymbol() }}</span>
                        <input type="number" name="min_topup_amount" step="0.01" min="0.01" max="10000" value="{{ old('min_topup_amount', $settings['min_topup_amount'] ?? 10) }}" required class="input pl-8" placeholder="10.00">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Minimum amount users can add to their wallet</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Maximum Top-Up Amount</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">{{ \App\Helpers\CurrencyHelper::getSymbol() }}</span>
                        <input type="number" name="max_topup_amount" step="0.01" min="1" max="1000000" value="{{ old('max_topup_amount', $settings['max_topup_amount'] ?? 10000) }}" required class="input pl-8" placeholder="10000.00">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Maximum amount users can add to their wallet in a single transaction</p>
                </div>
            </div>
        </div>

        <!-- Withdrawal Settings -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Withdrawal Settings</h3>
            </div>
            <div class="card-body space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Withdrawal Amount</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">{{ \App\Helpers\CurrencyHelper::getSymbol() }}</span>
                        <input type="number" name="min_withdrawal_amount" step="0.01" min="0.01" max="10000" value="{{ old('min_withdrawal_amount', $settings['min_withdrawal_amount'] ?? 50) }}" required class="input pl-8" placeholder="50.00">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Minimum amount sellers and delivery boys can withdraw</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Withdrawal Processing Days</label>
                    <input type="number" name="withdrawal_processing_days" min="1" max="30" value="{{ old('withdrawal_processing_days', $settings['withdrawal_processing_days'] ?? 3) }}" required class="input" placeholder="3">
                    <p class="text-xs text-gray-500 mt-1">Estimated number of business days to process withdrawals</p>
                </div>
            </div>
        </div>

        <!-- Transaction Settings -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Transaction Settings</h3>
            </div>
            <div class="card-body space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Maximum Transaction Amount</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">{{ \App\Helpers\CurrencyHelper::getSymbol() }}</span>
                        <input type="number" name="max_transaction_amount" step="0.01" min="1" max="1000000" value="{{ old('max_transaction_amount', $settings['max_transaction_amount'] ?? 100000) }}" required class="input pl-8" placeholder="100000.00">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Maximum amount allowed in a single wallet transaction</p>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <label class="font-medium text-gray-900">Allow Negative Balance</label>
                        <p class="text-sm text-gray-500 mt-1">Allow admin to debit wallet below zero balance</p>
                    </div>
                    <label class="toggle">
                        <input type="checkbox" name="allow_negative_balance" value="1" {{ old('allow_negative_balance', $settings['allow_negative_balance'] ?? false) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Notification Settings -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Notification Settings</h3>
            </div>
            <div class="card-body space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="font-medium text-gray-900">Send Transaction Notifications</label>
                        <p class="text-sm text-gray-500 mt-1">Notify users about wallet transactions via push notifications</p>
                    </div>
                    <label class="toggle">
                        <input type="checkbox" name="send_transaction_notifications" value="1" {{ old('send_transaction_notifications', $settings['send_transaction_notifications'] ?? true) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <label class="font-medium text-gray-900">Send Withdrawal Notifications</label>
                        <p class="text-sm text-gray-500 mt-1">Notify users about withdrawal status updates</p>
                    </div>
                    <label class="toggle">
                        <input type="checkbox" name="send_withdrawal_notifications" value="1" {{ old('send_withdrawal_notifications', $settings['send_withdrawal_notifications'] ?? true) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.dashboard') }}" class="btn-secondary">Cancel</a>
            <x-permission-btn 
                permission="settings.manage" 
                type="submit"
                class="btn-primary" 
                label="Save Settings"
                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
            />
        </div>
    </form>
</div>

<style>
.toggle {
    @apply relative inline-block w-12 h-6 cursor-pointer;
}
.toggle input {
    @apply opacity-0 w-0 h-0;
}
.toggle-slider {
    @apply absolute inset-0 bg-gray-300 rounded-full transition-colors duration-200;
}
.toggle-slider:before {
    @apply absolute content-[''] h-5 w-5 left-0.5 bottom-0.5 bg-white rounded-full transition-transform duration-200;
}
.toggle input:checked + .toggle-slider {
    @apply bg-orange-500;
}
.toggle input:checked + .toggle-slider:before {
    @apply translate-x-6;
}
</style>
@endsection
