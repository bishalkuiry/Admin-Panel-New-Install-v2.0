@extends('admin.layouts.app')

@section('title', 'Loyalty Settings')

@section('content')
<div>
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
        <div class="min-w-0">
            <h1 class="text-lg sm:text-2xl font-bold text-gray-900 truncate">Loyalty Settings</h1>
            <p class="text-xs sm:text-sm text-gray-500 hidden sm:block">Configure cashback points and referral rewards</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.loyalty.index') }}" class="btn-secondary text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span>Analytics Dashboard</span>
            </a>
            <a href="{{ route('admin.settings.index') }}" class="btn-secondary text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                <span>Back</span>
            </a>
        </div>
    </div>

    <form action="{{ route('admin.settings.loyalty.update') }}" method="POST" id="loyaltySettingsForm">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Cashback Settings -->
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 text-lg">Cashback Points System</h3>
                            <p class="text-sm text-gray-500">Reward customers with points on every order</p>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" name="cashback_enabled" value="1" id="cashback_enabled" {{ ($settings['cashback_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Min Order for Cashback</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">{{ \App\Helpers\CurrencyHelper::getSymbol() }}</span>
                                </div>
                                <input type="number" name="cashback_min_order_amount" step="1" min="0" class="w-full h-10 pl-10 pr-3 text-sm border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none" value="{{ $settings['cashback_min_order_amount'] ?? '500' }}">
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Users earn points only if order exceeds this</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cashback Percentage</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">%</span>
                                </div>
                                <input type="number" name="cashback_percentage" step="0.5" min="0" max="100" class="w-full h-10 pl-10 pr-3 text-sm border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none" value="{{ $settings['cashback_percentage'] ?? '10' }}">
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Order Total * % = Points Earned</p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Point Conversion Rate</label>
                            <div class="flex items-center gap-3">
                                <div class="flex-1 relative">
                                    <input type="number" name="cashback_points_per_currency" step="1" min="1" class="w-full h-10 px-3 text-sm border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none" value="{{ $settings['cashback_points_per_currency'] ?? '100' }}">
                                </div>
                                <span class="text-gray-500 font-bold">POINTS =</span>
                                <div class="flex-1 relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">{{ \App\Helpers\CurrencyHelper::getSymbol() }}</span>
                                    </div>
                                    <input type="text" readonly class="w-full h-10 pl-10 pr-3 text-sm border border-gray-100 bg-gray-50 rounded-lg outline-none" value="1.00">
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">E.g., 100 points = {{ \App\Helpers\CurrencyHelper::getSymbol() }}1.00 wallet credit</p>
                        </div>
                    </div>
                </div>

                <!-- Referral Settings -->
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 text-lg">Refer & Earn System</h3>
                            <p class="text-sm text-gray-500">Reward users for inviting their friends</p>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" name="referral_enabled" value="1" id="referral_enabled" {{ ($settings['referral_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Referrer Reward (Flat money)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">{{ \App\Helpers\CurrencyHelper::getSymbol() }}</span>
                                </div>
                                <input type="number" name="referral_referrer_reward" step="1" min="0" class="w-full h-10 pl-10 pr-3 text-sm border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none" value="{{ $settings['referral_referrer_reward'] ?? '50' }}">
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Money credited to person who shared code</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Referee Reward (Welcome money)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">{{ \App\Helpers\CurrencyHelper::getSymbol() }}</span>
                                </div>
                                <input type="number" name="referral_referee_reward" step="1" min="0" class="w-full h-10 pl-10 pr-3 text-sm border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none" value="{{ $settings['referral_referee_reward'] ?? '25' }}">
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Money credited to the new user</p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Free Deliveries for New User</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                    </svg>
                                </div>
                                <input type="number" name="referral_free_deliveries" step="1" min="0" class="w-full h-10 pl-10 pr-3 text-sm border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none" value="{{ $settings['referral_free_deliveries'] ?? '3' }}">
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Number of orders with free delivery for the new user</p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white border border-gray-200 rounded-lg p-6 sticky top-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Quick Overview</h3>
                    
                    <div class="space-y-4">
                        <div class="p-3 bg-blue-50 border border-blue-100 rounded-lg">
                            <div class="flex gap-2">
                                <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div class="text-xs text-blue-800">
                                    <p class="font-medium mb-1">Cashback Logic</p>
                                    <p>Points are awarded when order is marked <strong>Delivered</strong>. Points can be converted to wallet balance by the user.</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-3 bg-purple-50 border border-purple-100 rounded-lg">
                            <div class="flex gap-2">
                                <svg class="w-5 h-5 text-purple-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12z"/>
                                </svg>
                                <div class="text-xs text-purple-800">
                                    <p class="font-medium mb-1">Referral Logic</p>
                                    <p>Referral rewards are processed only after the new user completes their <strong>First Order</strong> to prevent fraud.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <x-permission-btn 
                            permission="settings.manage" 
                            type="submit"
                            class="btn-primary w-full justify-center" 
                            label="Save Loyalty Settings"
                            icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                        />
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
// Form submission
document.getElementById('loyaltySettingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const alert = document.createElement('div');
            alert.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
            alert.textContent = data.message || 'Loyalty settings saved successfully';
            document.body.appendChild(alert);
            setTimeout(() => alert.remove(), 3000);
        } else {
            throw new Error(data.message || 'Failed to save settings');
        }
    })
    .catch(error => {
        const alert = document.createElement('div');
        alert.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
        alert.textContent = error.message || 'An error occurred';
        document.body.appendChild(alert);
        setTimeout(() => alert.remove(), 3000);
    });
});
</script>
@endpush
@endsection
