@extends('admin.layouts.app')
@section('title', 'Notification Templates')
@section('content')

<form action="{{ route('admin.settings.notification-templates.update') }}" method="POST" id="notificationTemplatesForm" class="space-y-6">
    @csrf
    @method('PUT')

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.dashboard') }}" class="p-2 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900 tracking-wide">Notification Templates</h1>
                <p class="text-sm text-gray-500 mt-1">Customize push notification messages for each order status</p>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.dashboard') }}" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 rounded-lg text-sm font-bold uppercase tracking-wider transition-all shadow-sm">
                Cancel
            </a>
            <x-permission-btn 
                permission="settings.manage" 
                type="submit"
                class="px-5 py-2.5 bg-gray-900 text-white rounded-lg text-sm font-bold uppercase tracking-wider hover:bg-black transition-all shadow-md active:scale-[0.98] flex items-center gap-2" 
                label="Save Changes"
                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
            />
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Available Keywords (Sidebar) -->
        <div class="lg:col-span-1 space-y-6">
            <div class="card bg-white border border-gray-200 shadow-sm rounded-xl p-6 sticky top-24">
                <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider mb-4 border-b border-gray-100 pb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Available Keywords
                </h3>
                
                <p class="text-xs text-gray-500 mb-4">Click any keyword to copy it to your clipboard. You can paste these into the Title or Body fields.</p>

                <div class="flex flex-wrap gap-2">
                    @foreach([
                        '{order_id}' => 'Order ID',
                        '{order_number}' => 'Order Number',
                        '{customer_name}' => 'Customer Name',
                        '{store_name}' => 'Store Name',
                        '{store_address}' => 'Store Address',
                        '{delivery_address}' => 'Delivery Address',
                        '{total}' => 'Order Total',
                        '{items_count}' => 'No. of Items',
                        '{status}' => 'Current Status',
                        '{delivery_partner_name}' => 'Delivery Partner Name',
                    ] as $keyword => $description)
                        <div class="group relative cursor-pointer keyword-chip" data-keyword="{{ $keyword }}">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-50 border border-gray-200 text-xs font-mono text-blue-600 hover:bg-blue-50 hover:border-blue-200 transition-all">
                                {{ $keyword }}
                            </span>
                            <!-- Tooltip -->
                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-[10px] rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
                                {{ $description }}
                                <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-gray-900"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Templates Configuration -->
        <div class="lg:col-span-2 space-y-6">
            @php
                $statuses = [
                    'pending' => ['label' => 'Pending (New Order)', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'text-amber-500', 'bg' => 'bg-amber-50'],
                    'confirmed' => ['label' => 'Confirmed', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'text-blue-500', 'bg' => 'bg-blue-50'],
                    'packed' => ['label' => 'Packed', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'color' => 'text-purple-500', 'bg' => 'bg-purple-50'],
                    'picked_up' => ['label' => 'Picked Up', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'color' => 'text-indigo-500', 'bg' => 'bg-indigo-50'],
                    'out_for_delivery' => ['label' => 'Out for Delivery', 'icon' => 'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z', 'color' => 'text-orange-500', 'bg' => 'bg-orange-50'],
                    'delivered' => ['label' => 'Delivered', 'icon' => 'M5 13l4 4L19 7', 'color' => 'text-green-500', 'bg' => 'bg-green-50'],
                    'cancelled' => ['label' => 'Cancelled', 'icon' => 'M6 18L18 6M6 6l12 12', 'color' => 'text-red-500', 'bg' => 'bg-red-50'],
                ];
                $recipients = ['customer' => 'Customer', 'store' => 'Store Owner', 'delivery' => 'Delivery Partner'];
            @endphp

            @foreach($statuses as $statusKey => $statusInfo)
                <div class="card bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden" x-data="{ expanded: {{ $loop->first ? 'true' : 'false' }} }">
                    <!-- Status Header -->
                    <div @click="expanded = !expanded" class="px-6 py-4 bg-white hover:bg-gray-50 transition-colors cursor-pointer flex items-center justify-between border-b border-gray-100">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 {{ $statusInfo['bg'] }} {{ $statusInfo['color'] }} rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $statusInfo['icon'] }}"/></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 text-sm">{{ $statusInfo['label'] }}</h3>
                                <p class="text-xs text-gray-500 mt-0.5">Configure notifications for this status</p>
                            </div>
                        </div>
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-5 h-5 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>

                    <!-- Templates Body -->
                    <div x-show="expanded" x-collapse class="p-6 bg-gray-50/50 space-y-6">
                        @foreach($recipients as $recipientKey => $recipientLabel)
                            <div class="bg-white border border-gray-200 rounded-xl p-5 hover:border-blue-300 hover:ring-1 hover:ring-blue-100 transition-all">
                                <div class="flex items-center gap-2 mb-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-gray-100 text-xs font-semibold text-gray-700 uppercase tracking-wide">
                                        @if($recipientKey === 'customer')
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        @elseif($recipientKey === 'store')
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @else
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        @endif
                                        {{ $recipientLabel }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 gap-4">
                                    <div class="form-group">
                                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1.5">Notification Title</label>
                                        <input type="text"
                                            name="templates[{{ $statusKey }}][{{ $recipientKey }}][title]"
                                            value="{{ $templates[$statusKey][$recipientKey]['title'] ?? '' }}"
                                            class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder-gray-400"
                                            placeholder="e.g. Order #{{ '{order_number}' }} Updates">
                                    </div>
                                    <div class="form-group">
                                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1.5">Message Body</label>
                                        <textarea
                                            name="templates[{{ $statusKey }}][{{ $recipientKey }}][body]"
                                            rows="2"
                                            class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder-gray-400 resize-none"
                                            placeholder="e.g. Your order from {{ '{store_name}' }} is now {{ strtolower($statusInfo['label']) }}.">{{ $templates[$statusKey][$recipientKey]['body'] ?? '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</form>

@push('scripts')
<script>
    // Copy Keyword functionality
    document.querySelectorAll('.keyword-chip').forEach(chip => {
        chip.addEventListener('click', () => {
            const keyword = chip.dataset.keyword;
            navigator.clipboard.writeText(keyword).then(() => {
                // Show toast using global function
                if (typeof showToast === 'function') {
                    showToast('Keyword copied: ' + keyword);
                } else {
                    // Fallback
                    const span = chip.querySelector('span');
                    const originalBg = span.className;
                    span.classList.remove('bg-gray-50', 'text-blue-600');
                    span.classList.add('bg-green-100', 'text-green-700', 'border-green-200');
                    setTimeout(() => {
                        span.className = originalBg; 
                    }, 500);
                }
            });
        });
    });

    // AJAX Form Submit
    document.getElementById('notificationTemplatesForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const form = this;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success || data.message) { // Handle various success responses
                // Show success toast
                const toast = document.createElement('div');
                toast.className = 'fixed top-4 right-4 bg-green-900 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-3 animate-fade-in-down transform transition-all duration-300';
                toast.innerHTML = `<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>${data.message || 'Templates saved successfully'}</span>`;
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    toast.classList.add('opacity-0', '-translate-y-4');
                    setTimeout(() => toast.remove(), 300);
                }, 3000);
            } else {
                throw new Error(data.message || 'Something went wrong');
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
</script>
<style>
    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-down {
        animation: fadeInDown 0.3s ease-out forwards;
    }
</style>
@endpush
@endsection
