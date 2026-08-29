<!-- Product Widget Configuration -->

<!-- 🎯 Widget Purpose Header -->
<div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-4">
    <div class="flex items-start gap-3">
        <div class="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
        </div>
        <div>
            <h4 class="font-semibold text-gray-800">Product Showcase Widget</h4>
            <p class="text-xs text-blue-600 mt-1">Display products in an attractive grid or carousel. Perfect for featured items, new arrivals, or promotional collections.</p>
        </div>
    </div>
</div>

@include('admin.settings.partials._common-widget-fields')

<!-- Grid Configuration -->
@include('admin.settings.partials._grid-config')

<!-- Horizontal Scroll / Auto-Animation -->
@include('admin.settings.partials._horizontal-animation')

<!-- Background Configuration -->
@include('admin.settings.partials._background-config')

<!-- View All Button Toggle -->
<div class="form-group border-t pt-4 mt-4">
    <div class="flex items-center gap-3 p-3 bg-gradient-to-r from-green-50 to-white border border-green-100 rounded-xl">
        <label class="toggle"><input type="checkbox" x-model="formData.show_view_all"><span class="toggle-slider"></span></label>
        <div class="flex-1">
            <div class="flex items-center gap-2">
                <span class="text-sm font-semibold text-gray-800">Show "View All" Button</span>
                <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Recommended</span>
            </div>
            <p class="text-xs text-gray-500 mt-1">Adds a "View All" link that takes users to the complete product listing</p>
        </div>
    </div>
</div>
