<div class="flex gap-2 border-b border-gray-200 mb-5">
    <a href="{{ route('admin.stores.show', $store) }}" class="px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.stores.show') ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500 hover:text-gray-700' }}">Overview</a>
    <a href="{{ route('admin.stores.products', $store) }}" class="px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.stores.products') ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500 hover:text-gray-700' }}">Products</a>
    <a href="{{ route('admin.stores.orders', $store) }}" class="px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.stores.orders') ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500 hover:text-gray-700' }}">Orders</a>
    <a href="{{ route('admin.stores.staff', $store) }}" class="px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.stores.staff') ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500 hover:text-gray-700' }}">Staff</a>
    <a href="{{ route('admin.stores.payouts', $store) }}" class="px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.stores.payouts') ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500 hover:text-gray-700' }}">Payouts</a>
    <a href="{{ route('admin.stores.reviews', $store) }}" class="px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.stores.reviews') ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500 hover:text-gray-700' }}">Ratings &amp; Reviews</a>
    <a href="{{ route('admin.stores.activity', $store) }}" class="px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.stores.activity') ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500 hover:text-gray-700' }}">Activity</a>
</div>
