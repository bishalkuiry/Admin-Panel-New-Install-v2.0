<!-- Category Widget Configuration -->

<!-- 📂 Widget Purpose Header -->
<div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 mb-4">
    <div class="flex items-start gap-3">
        <div class="w-10 h-10 bg-indigo-500 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
        </div>
        <div>
            <h4 class="font-semibold text-gray-800">Category Browse Widget</h4>
            <p class="text-xs text-indigo-600 mt-1">Showcase product categories to help users discover items. Great for navigation and browsing-focused layouts.</p>
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
            <p class="text-xs text-gray-500 mt-1">Adds a "View All" link that takes users to the category listing page</p>
        </div>
    </div>
</div>

<!-- Style 3 Info - Category Tabs -->
<template x-if="formData.style === 'style_3'">
    <div class="form-group border-t pt-4 mt-4">
        <div class="bg-gradient-to-r from-blue-50 to-blue-100/50 border border-blue-200 rounded-xl p-4">
            <div class="flex gap-3">
                <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-blue-900 mb-1">📑 Category Tabs with Products</p>
                    <p class="text-sm text-blue-800 leading-relaxed">Selected categories appear as <strong>horizontal tabs</strong>. When users tap a category tab, products from that category display below automatically.</p>
                    <div class="mt-3 flex items-center gap-2 text-xs text-blue-700 bg-white/60 rounded-lg px-3 py-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Enable <strong>"Auto-Scroll Animation"</strong> above to make products slide automatically</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- Style 4 Info - Highlight Layout -->
<template x-if="formData.style === 'style_4'">
    <div class="form-group border-t pt-4 mt-4">
        <div class="bg-gradient-to-r from-purple-50 to-purple-100/50 border border-purple-200 rounded-xl p-4">
            <div class="flex gap-3">
                <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-purple-900 mb-1">✨ Highlight Layout</p>
                    <p class="text-sm text-purple-800 leading-relaxed">A magazine-style layout that highlights your first category with a large image, followed by smaller grid items.</p>
                    <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                        <div class="bg-white/60 rounded-lg px-3 py-2 text-purple-700">
                            <strong>1st Row:</strong> 1 large + 2 small
                        </div>
                        <div class="bg-white/60 rounded-lg px-3 py-2 text-purple-700">
                            <strong>Other Rows:</strong> 4 items each
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
