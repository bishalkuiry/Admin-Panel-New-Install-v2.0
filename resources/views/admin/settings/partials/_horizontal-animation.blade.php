<!-- Horizontal Animation Toggle -->
<div class="form-group border-t pt-4 mt-4">
    <div class="bg-gradient-to-r from-cyan-50 to-white border border-cyan-100 rounded-xl p-4">
        <div class="flex items-start gap-3">
            <label class="toggle flex-shrink-0 mt-0.5"><input type="checkbox" x-model="formData.enable_horizontal_animation"><span class="toggle-slider"></span></label>
            <div class="flex-1">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm font-semibold text-gray-800">Enable Auto-Scroll Animation</span>
                </div>
                <p class="text-xs text-gray-500 mt-1">Items will automatically slide sideways every few seconds, creating an engaging carousel effect that draws attention to your content.</p>

                <!-- Visual Preview -->
                <div class="mt-3 flex items-center gap-2 p-2 bg-cyan-100/50 rounded-lg">
                    <div class="flex gap-1 overflow-hidden w-32">
                        <div class="w-8 h-8 bg-cyan-400 rounded animate-pulse"></div>
                        <div class="w-8 h-8 bg-cyan-300 rounded"></div>
                        <div class="w-8 h-8 bg-cyan-200 rounded"></div>
                        <div class="w-4 h-8 bg-cyan-100 rounded"></div>
                    </div>
                    <span class="text-xs text-cyan-700">← Auto-scroll preview</span>
                </div>
            </div>
        </div>
    </div>
</div>
