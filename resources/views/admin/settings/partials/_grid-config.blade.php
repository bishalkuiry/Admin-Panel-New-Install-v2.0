<!-- Grid Configuration Component -->
<div class="space-y-4 border-t pt-4 mt-4">
    <div class="flex items-center gap-2 mb-3">
        <svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
        </svg>
        <label class="label mb-0">Grid Layout Settings</label>
    </div>
    <p class="text-xs text-gray-500 mb-4 ml-7">Configure how many items to display and their arrangement</p>

    <!-- Device Visibility (Mobile vs Laptop) -->
    <div class="form-group bg-slate-50 border border-slate-200 rounded-xl p-3.5 mb-4">
        <label class="label flex items-center gap-2 mb-2 font-semibold text-gray-800 text-xs">
            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            Device Target Visibility
        </label>
        <div class="grid grid-cols-2 gap-3">
            <label class="flex items-center gap-2 p-2.5 bg-white border border-gray-200 rounded-lg cursor-pointer hover:border-indigo-300">
                <input type="checkbox" x-model="formData.show_on_mobile" class="rounded text-indigo-600 focus:ring-indigo-500">
                <span class="text-xs font-semibold text-gray-700">📱 Mobile View</span>
            </label>
            <label class="flex items-center gap-2 p-2.5 bg-white border border-gray-200 rounded-lg cursor-pointer hover:border-indigo-300">
                <input type="checkbox" x-model="formData.show_on_laptop" class="rounded text-indigo-600 focus:ring-indigo-500">
                <span class="text-xs font-semibold text-gray-700">💻 Laptop / Desktop</span>
            </label>
        </div>
    </div>

    <!-- Mobile Grid Columns (1-3) -->
    <div class="form-group" x-show="formData.style !== 'style_4'">
        <label class="label flex items-center justify-between">
            <span class="flex items-center gap-1.5 font-semibold text-gray-800 text-xs">
                📱 Mobile Items per Row
            </span>
            <span class="text-[11px] text-gray-400">Mobile screens (&lt;768px)</span>
        </label>
        <div class="grid grid-cols-3 gap-2">
            <label class="flex flex-col items-center gap-1 p-2.5 border rounded-xl cursor-pointer transition-all" :class="(formData.grid_columns_mobile || 2) === 1 ? 'border-teal-500 bg-teal-50 text-teal-700' : 'border-gray-200 hover:border-teal-300'">
                <input type="radio" x-model.number="formData.grid_columns_mobile" :value="1" class="hidden">
                <span class="text-xs font-bold">1 per Row</span>
            </label>
            <label class="flex flex-col items-center gap-1 p-2.5 border rounded-xl cursor-pointer transition-all" :class="(formData.grid_columns_mobile || 2) === 2 ? 'border-teal-500 bg-teal-50 text-teal-700' : 'border-gray-200 hover:border-teal-300'">
                <input type="radio" x-model.number="formData.grid_columns_mobile" :value="2" class="hidden">
                <span class="text-xs font-bold">2 per Row</span>
            </label>
            <label class="flex flex-col items-center gap-1 p-2.5 border rounded-xl cursor-pointer transition-all" :class="(formData.grid_columns_mobile || 2) === 3 ? 'border-teal-500 bg-teal-50 text-teal-700' : 'border-gray-200 hover:border-teal-300'">
                <input type="radio" x-model.number="formData.grid_columns_mobile" :value="3" class="hidden">
                <span class="text-xs font-bold">3 per Row</span>
            </label>
        </div>
    </div>

    <!-- Laptop / Desktop Grid Columns (2-7 per Row) -->
    <div class="form-group mt-4" x-show="formData.style !== 'style_4'">
        <label class="label flex items-center justify-between">
            <span class="flex items-center gap-1.5 font-semibold text-gray-800 text-xs">
                💻 Laptop / Desktop Items per Row
            </span>
            <span class="text-[11px] text-indigo-600 font-bold">Desktop screens (&ge;768px)</span>
        </label>
        <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
            <template x-for="cols in [2, 3, 4, 5, 6, 7]" :key="cols">
                <label class="flex flex-col items-center justify-center p-2.5 border rounded-xl cursor-pointer transition-all text-center"
                       :class="(formData.grid_columns_laptop || formData.grid_columns || 5) === cols ? 'border-indigo-600 bg-indigo-50 text-indigo-700 font-bold shadow-sm' : 'border-gray-200 hover:border-indigo-300 text-gray-600'">
                    <input type="radio" x-model.number="formData.grid_columns_laptop" :value="cols" @change="formData.grid_columns = cols" class="hidden">
                    <span class="text-xs font-bold" x-text="cols + ' / Row'"></span>
                </label>
            </template>
        </div>
        <p class="text-[11px] text-gray-500 mt-2">
            Tip: 4 to 6 items per row look best on laptops and high-resolution monitors.
        </p>
    </div>

    <!-- Info for style_4 -->
    <div class="form-group" x-show="formData.style === 'style_4'">
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="font-medium text-amber-900">Special Highlight Layout</p>
                    <p class="text-sm text-amber-800 mt-1">This layout has a fixed structure:</p>
                    <ul class="text-sm text-amber-700 mt-2 space-y-1 ml-4 list-disc">
                        <li><strong>Row 1:</strong> 1 large featured item + 2 smaller items</li>
                        <li><strong>Other rows:</strong> 4 items each in a grid</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Number of Rows -->
    <div class="form-group bg-gray-50 rounded-xl p-4 border border-gray-100">
        <label class="label flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
            </svg>
            How Many Rows <span class="text-xs text-gray-400 font-normal">(Total Lines)</span>
        </label>
        <div class="flex items-center gap-4">
            <input type="range" x-model.number="formData.grid_rows" min="1" max="10" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-teal-500">
            <div class="w-16">
                <input type="number" x-model.number="formData.grid_rows" min="1" max="10" class="input text-center font-semibold">
            </div>
        </div>

        <!-- Dynamic Preview Calculation -->
        <div class="mt-3 p-3 bg-white rounded-lg border border-gray-200">
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-500">Total items displayed:</span>
                <span class="text-lg font-bold text-teal-600" x-text="formData.style === 'style_4' ? (3 + (formData.grid_rows - 1) * 4) : (formData.grid_columns || 2) * (formData.grid_rows || 1)"></span>
            </div>
            <p class="text-xs text-gray-400 mt-1" x-show="formData.style !== 'style_4'">
                <span x-text="(formData.grid_columns || 2)"></span> items per row × <span x-text="formData.grid_rows || 1"></span> rows
            </p>
            <p class="text-xs text-gray-400 mt-1" x-show="formData.style === 'style_4'">
                Row 1: 3 items + <span x-text="(formData.grid_rows || 1) - 1"></span> rows × 4 items
            </p>
        </div>

        <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
            <svg class="w-3 h-3 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span x-show="formData.style !== 'style_4'">Extra items beyond this grid will show a "View All" button</span>
            <span x-show="formData.style === 'style_4'">Only these items will be displayed (no "View All")</span>
        </p>
    </div>
</div>
