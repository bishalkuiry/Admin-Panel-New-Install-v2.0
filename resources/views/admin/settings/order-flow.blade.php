@extends('admin.layouts.app')

@section('title', 'Order Flow Settings')

@section('content')
<div>
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
        <div class="min-w-0">
            <h1 class="text-lg sm:text-2xl font-bold text-gray-900 truncate">Order Flow Settings</h1>
            <p class="text-xs sm:text-sm text-gray-500 hidden sm:block">Configure order confirmation, timeout, and auto-action behavior</p>
        </div>
        <a href="{{ route('admin.settings.index') }}" class="btn-secondary text-sm flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            <span class="hidden sm:inline">Back to Settings</span>
            <span class="sm:hidden">Back</span>
        </a>
    </div>

    <form action="{{ route('admin.settings.order-flow.update') }}" method="POST" id="orderFlowForm">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Order Confirmation By -->
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <div class="mb-4">
                        <h3 class="font-semibold text-gray-900 text-lg">Order Confirmation By</h3>
                        <p class="text-sm text-gray-500">Choose who receives and confirms new orders</p>
                    </div>

                    <!-- Info Banner -->
                    <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex gap-2">
                            <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="text-xs text-blue-800">
                                <p class="font-medium mb-1">How This Works:</p>
                                <ul class="space-y-1 ml-4 list-disc">
                                    <li><strong>Store:</strong> Store partner app receives the order notification and can accept/reject it</li>
                                    <li><strong>Delivery Partner:</strong> Delivery partner app receives the order and can accept/reject it</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <!-- Store -->
                        <label class="cursor-pointer">
                            <input type="radio" name="order_confirm_by" value="store" class="peer sr-only" {{ ($settings['order_confirm_by'] ?? 'store') == 'store' ? 'checked' : '' }} onchange="updateConfirmByStatus()">
                            <div class="p-4 border-2 border-gray-200 rounded-lg peer-checked:border-primary-500 peer-checked:bg-primary-50 hover:border-gray-300 transition-all">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="font-medium text-gray-900">Store</span>
                                            <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-xs font-medium rounded">RECOMMENDED</span>
                                        </div>
                                        <p class="text-sm text-gray-600">Store owner receives, reviews, and accepts/rejects orders via Store Partner app</p>
                                    </div>
                                </div>
                            </div>
                        </label>

                        <!-- Delivery Partner -->
                        <label class="cursor-pointer">
                            <input type="radio" name="order_confirm_by" value="delivery_partner" class="peer sr-only" {{ ($settings['order_confirm_by'] ?? 'store') == 'delivery_partner' ? 'checked' : '' }} onchange="updateConfirmByStatus()">
                            <div class="p-4 border-2 border-gray-200 rounded-lg peer-checked:border-primary-500 peer-checked:bg-primary-50 hover:border-gray-300 transition-all">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="font-medium text-gray-900">Delivery Partner</span>
                                            <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs font-medium rounded">FAST</span>
                                        </div>
                                        <p class="text-sm text-gray-600">Delivery partner receives, accepts/rejects orders directly via Delivery Partner app</p>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Timeout Duration -->
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <div class="mb-4">
                        <h3 class="font-semibold text-gray-900 text-lg">Order Timeout</h3>
                        <p class="text-sm text-gray-500">How long to wait for acceptance before taking automatic action</p>
                    </div>

                    <div class="max-w-md">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Timeout Duration</label>
                        <select name="order_timeout_minutes" id="order_timeout_minutes" class="w-full h-10 px-3 text-sm border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none" onchange="updateTimeoutStatus()">
                            <option value="1" {{ ($settings['order_timeout_minutes'] ?? '3') == '1' ? 'selected' : '' }}>1 minute</option>
                            <option value="2" {{ ($settings['order_timeout_minutes'] ?? '3') == '2' ? 'selected' : '' }}>2 minutes</option>
                            <option value="3" {{ ($settings['order_timeout_minutes'] ?? '3') == '3' ? 'selected' : '' }}>3 minutes</option>
                            <option value="5" {{ ($settings['order_timeout_minutes'] ?? '3') == '5' ? 'selected' : '' }}>5 minutes</option>
                            <option value="10" {{ ($settings['order_timeout_minutes'] ?? '3') == '10' ? 'selected' : '' }}>10 minutes</option>
                            <option value="15" {{ ($settings['order_timeout_minutes'] ?? '3') == '15' ? 'selected' : '' }}>15 minutes</option>
                            <option value="30" {{ ($settings['order_timeout_minutes'] ?? '3') == '30' ? 'selected' : '' }}>30 minutes</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">If no action is taken within this time, the system will automatically process the order</p>
                    </div>
                </div>

                <!-- Timeout Action -->
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <div class="mb-4">
                        <h3 class="font-semibold text-gray-900 text-lg">Timeout Action</h3>
                        <p class="text-sm text-gray-500">What happens when no one accepts/rejects the order within the timeout period</p>
                    </div>

                    <div class="space-y-3">
                        <!-- Auto Cancel -->
                        <label class="cursor-pointer">
                            <input type="radio" name="order_timeout_action" value="auto_cancel" class="peer sr-only" {{ ($settings['order_timeout_action'] ?? 'auto_cancel') == 'auto_cancel' ? 'checked' : '' }} onchange="updateTimeoutActionStatus()">
                            <div class="p-4 border-2 border-gray-200 rounded-lg peer-checked:border-red-400 peer-checked:bg-red-50 hover:border-gray-300 transition-all">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="font-medium text-gray-900">Auto Cancel & Refund</span>
                                            <span class="px-2 py-0.5 bg-red-100 text-red-700 text-xs font-medium rounded">SAFE</span>
                                        </div>
                                        <p class="text-sm text-gray-600">Order is automatically cancelled and customer receives a full refund to their wallet</p>
                                    </div>
                                </div>
                            </div>
                        </label>

                        <!-- Auto Accept -->
                        <label class="cursor-pointer">
                            <input type="radio" name="order_timeout_action" value="auto_accept" class="peer sr-only" {{ ($settings['order_timeout_action'] ?? 'auto_cancel') == 'auto_accept' ? 'checked' : '' }} onchange="updateTimeoutActionStatus()">
                            <div class="p-4 border-2 border-gray-200 rounded-lg peer-checked:border-green-400 peer-checked:bg-green-50 hover:border-gray-300 transition-all">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="font-medium text-gray-900">Auto Accept</span>
                                            <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-medium rounded">FAST</span>
                                        </div>
                                        <p class="text-sm text-gray-600">Order is automatically confirmed and moves forward in the order flow</p>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white border border-gray-200 rounded-lg p-6 sticky top-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Current Configuration</h3>

                    <div class="space-y-4">
                        <!-- Confirm By Status -->
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Orders Confirmed By</p>
                            <div id="confirm_by_status">
                                @if(($settings['order_confirm_by'] ?? 'store') == 'store')
                                <span class="px-2 py-1 bg-emerald-100 text-emerald-700 text-xs font-medium rounded">Store</span>
                                @else
                                <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded">Delivery Partner</span>
                                @endif
                            </div>
                        </div>

                        <!-- Timeout Status -->
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Timeout Duration</p>
                            <p id="timeout_status" class="font-medium text-gray-900">{{ $settings['order_timeout_minutes'] ?? '3' }} minutes</p>
                        </div>

                        <!-- Timeout Action Status -->
                        <div>
                            <p class="text-xs text-gray-500 mb-1">On Timeout</p>
                            <div id="timeout_action_status">
                                @if(($settings['order_timeout_action'] ?? 'auto_cancel') == 'auto_cancel')
                                <span class="px-2 py-1 bg-red-100 text-red-700 text-xs font-medium rounded">Auto Cancel + Refund</span>
                                @else
                                <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-medium rounded">Auto Accept</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <x-permission-btn 
                            permission="settings.manage" 
                            type="submit"
                            class="btn-primary w-full justify-center" 
                            label="Save Settings"
                            icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                        />
                    </div>

                    <!-- Cron Info -->
                    <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                        <div class="flex gap-2">
                            <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <p class="text-xs text-amber-800">
                                Auto-action requires a cron job: <code class="bg-amber-100 px-1 rounded">* * * * * php artisan schedule:run</code>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function updateConfirmByStatus() {
    const value = document.querySelector('input[name="order_confirm_by"]:checked').value;
    const statusDiv = document.getElementById('confirm_by_status');
    if (value === 'store') {
        statusDiv.innerHTML = '<span class="px-2 py-1 bg-emerald-100 text-emerald-700 text-xs font-medium rounded">Store</span>';
    } else {
        statusDiv.innerHTML = '<span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded">Delivery Partner</span>';
    }
}

function updateTimeoutStatus() {
    const value = document.getElementById('order_timeout_minutes').value;
    document.getElementById('timeout_status').textContent = value + ' minutes';
}

function updateTimeoutActionStatus() {
    const value = document.querySelector('input[name="order_timeout_action"]:checked').value;
    const statusDiv = document.getElementById('timeout_action_status');
    if (value === 'auto_cancel') {
        statusDiv.innerHTML = '<span class="px-2 py-1 bg-red-100 text-red-700 text-xs font-medium rounded">Auto Cancel + Refund</span>';
    } else {
        statusDiv.innerHTML = '<span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-medium rounded">Auto Accept</span>';
    }
}

document.getElementById('orderFlowForm').addEventListener('submit', function(e) {
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
            alert.textContent = data.message || 'Settings saved successfully';
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
