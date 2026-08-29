@php
    $isAdmin = request()->is('admin*') || request()->is('seller*');
@endphp
@extends($isAdmin ? 'admin.layouts.app' : 'website::layout')

@section('title', '404 - Page Not Found')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-xl w-full text-center space-y-8 bg-white p-8 sm:p-12 rounded-3xl border border-gray-100 shadow-xl relative overflow-hidden">
        {{-- Background Glow --}}
        <div class="absolute -top-16 -right-16 w-32 h-32 rounded-full bg-indigo-500/10 blur-2xl pointer-events-none"></div>
        <div class="absolute -bottom-16 -left-16 w-32 h-32 rounded-full bg-orange-500/10 blur-2xl pointer-events-none"></div>

        {{-- 404 Badge & Graphic --}}
        <div class="relative">
            <div class="text-8xl sm:text-9xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 via-purple-600 to-orange-500 tracking-tight select-none">
                404
            </div>
            <div class="w-16 h-16 bg-white shadow-lg rounded-2xl border border-gray-100 flex items-center justify-center mx-auto -mt-8 relative z-10">
                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        {{-- Title & Message --}}
        <div class="space-y-3">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Oops! Page Not Found</h1>
            <p class="text-sm sm:text-base text-gray-500 max-w-md mx-auto leading-relaxed">
                The page you are looking for might have been moved, renamed, or is temporarily unavailable. Let's get you back on track!
            </p>
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
            @if($isAdmin)
                <a href="{{ route('admin.dashboard') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full text-sm font-bold text-white shadow-lg hover:shadow-xl transition-all duration-200 bg-indigo-600 hover:bg-indigo-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Return to Admin Dashboard
                </a>
            @else
                <a href="{{ url('/') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full text-sm font-bold text-white shadow-lg hover:shadow-xl transition-all duration-200" style="background: {{ $settings['primary_color'] ?? '#f97316' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Return Home
                </a>

                @if(Route::has('website.shop'))
                <a href="{{ route('website.shop') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full text-sm font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 transition-all duration-200">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    Browse Shop
                </a>
                @endif
            @endif
        </div>

        @if(!$isAdmin)
            {{-- Top Categories Quick Links --}}
            @php
                $quickCats = \App\Models\Category::where('is_active', true)->whereNull('parent_id')->orderBy('sort_order')->take(4)->get();
            @endphp
            @if($quickCats->isNotEmpty() && Route::has('website.category'))
            <div class="pt-6 border-t border-gray-100">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-3">Popular Categories</span>
                <div class="flex flex-wrap items-center justify-center gap-2">
                    @foreach($quickCats as $cat)
                    <a href="{{ route('website.category', $cat->slug) }}" class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-medium text-gray-600 bg-gray-50 border border-gray-200/60 hover:border-indigo-500 hover:text-indigo-600 transition-all">
                        {{ $cat->name }}
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        @endif
    </div>
</div>
@endsection
