<!-- Brand Widget Configuration -->

<!-- 🏷️ Widget Purpose Header -->
<div class="bg-pink-50 border border-pink-100 rounded-xl p-4 mb-4">
    <div class="flex items-start gap-3">
        <div class="w-10 h-10 bg-pink-500 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
        </div>
        <div>
            <h4 class="font-semibold text-gray-800">Brand Showcase Widget</h4>
            <p class="text-xs text-pink-600 mt-1">Display trusted brands to build credibility. Perfect for stores with multiple brand partnerships or exclusive brand collections.</p>
        </div>
    </div>
</div>

@include('admin.settings.partials._common-widget-fields')

<!-- Grid Configuration -->
@include('admin.settings.partials._grid-config')

<!-- Horizontal Animation -->
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
            </div>
            <p class="text-xs text-gray-500 mt-1">Adds a "View All" link that takes users to the brand listing page</p>
        </div>
    </div>
</div>
