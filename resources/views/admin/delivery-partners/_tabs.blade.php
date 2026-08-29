<div class="flex gap-2 border-b border-gray-200 mb-6 font-medium">
    <a href="{{ route('admin.delivery-partners.show', $deliveryPartner) }}" 
       class="px-5 py-2.5 text-sm transition-all duration-200 {{ request()->routeIs('admin.delivery-partners.show') ? 'text-primary-600 border-b-2 border-primary-600' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
        Overview
    </a>
    <a href="{{ route('admin.delivery-partners.deliveries', $deliveryPartner) }}" 
       class="px-5 py-2.5 text-sm transition-all duration-200 {{ request()->routeIs('admin.delivery-partners.deliveries') ? 'text-primary-600 border-b-2 border-primary-600' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
        Deliveries
        <span class="ml-1.5 px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded-full group-hover:bg-gray-200 transition-colors">
            {{ $deliveryPartner->deliveries_count ?? $deliveryPartner->deliveries()->count() }}
        </span>
    </a>
    <a href="{{ route('admin.delivery-partners.payouts', $deliveryPartner) }}" 
       class="px-5 py-2.5 text-sm transition-all duration-200 {{ request()->routeIs('admin.delivery-partners.payouts') ? 'text-primary-600 border-b-2 border-primary-600' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
        Payouts
    </a>
    <a href="{{ route('admin.delivery-partners.edit', $deliveryPartner) }}" 
       class="px-5 py-2.5 text-sm transition-all duration-200 {{ request()->routeIs('admin.delivery-partners.edit') ? 'text-primary-600 border-b-2 border-primary-600' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
        Settings
    </a>
</div>
