{{-- Modern Payment Form Wrapper --}}
<div class="space-y-6">
    <div class="flex items-start justify-between">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 {{ $iconBg ?? 'bg-blue-100' }} rounded-xl flex items-center justify-center">
                {!! $icon ?? '<svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>' !!}
            </div>
            <div>
                <h4 class="text-lg font-semibold text-gray-900">{{ $title }}</h4>
                <p class="text-sm text-gray-500">{{ $description }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            @if(isset($badge))
                <span class="px-3 py-1 text-xs font-medium rounded-full {{ $badgeClass ?? 'bg-blue-100 text-blue-700' }}">
                    {{ $badge }}
                </span>
            @endif
        </div>
    </div>

    {{ $slot }}
</div>
