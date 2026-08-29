<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->title }} - {{ $settings['app_name'] }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    maxWidth: {
                        'screen-2xl': '1536px',
                    },
                    colors: {
                        primary: {
                            DEFAULT: '{{ $settings["primary_color"] }}',
                            500: '{{ $settings["primary_color"] }}',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .btn-primary { background-color: {{ $settings['primary_color'] }}; color: #fff; }
        .btn-primary:hover { filter: brightness(0.9); }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
    @if($settings['app_favicon'])
    <link rel="icon" href="{{ storage_url($settings['app_favicon']) }}">
    @endif
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen flex flex-col">

    {{-- Navbar — identical structure to website plugin layout --}}
    <nav class="sticky top-0 z-50 bg-white shadow-sm border-b border-gray-100">
        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 gap-4">

                {{-- Logo --}}
                <a href="{{ url('/') }}" class="flex items-center gap-2 flex-shrink-0">
                    @if($settings['app_logo'])
                        <img src="{{ storage_url($settings['app_logo']) }}" alt="{{ $settings['app_name'] }}" class="h-9 w-auto object-contain">
                    @else
                        <div class="h-9 w-9 rounded-lg flex items-center justify-center text-white font-bold text-lg"
                             style="background:{{ $settings['primary_color'] }}">
                            {{ substr($settings['app_name'], 0, 1) }}
                        </div>
                        <span class="font-bold text-gray-900 text-lg hidden sm:block">{{ $settings['app_name'] }}</span>
                    @endif
                </a>

                {{-- Nav links --}}
                <div class="flex items-center gap-3">
                    <a href="{{ url('/') }}"
                       class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Home
                    </a>
                    <a href="{{ url('/shop') }}"
                       class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors">
                        Shop
                    </a>
                </div>
            </div>
        </div>
    </nav>

    {{-- Hero banner --}}
    <div class="bg-white border-b border-gray-100">
        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <nav class="flex items-center gap-2 text-xs text-gray-400 mb-4">
                <a href="{{ url('/') }}" class="hover:text-gray-600 transition-colors">Home</a>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-gray-600 font-medium">{{ $page->title }}</span>
            </nav>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 leading-tight">{{ $page->title }}</h1>
        </div>
    </div>

    {{-- Content --}}
    <main class="flex-1">
        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-6 sm:px-10 py-8 sm:py-12">
                <div class="prose prose-gray max-w-none
                    prose-headings:font-bold prose-headings:text-gray-900
                    prose-h1:text-2xl prose-h2:text-xl prose-h3:text-lg
                    prose-p:text-gray-600 prose-p:leading-relaxed
                    prose-a:font-medium prose-a:no-underline hover:prose-a:underline
                    prose-strong:text-gray-800
                    prose-ul:text-gray-600 prose-ol:text-gray-600
                    prose-li:leading-relaxed
                    prose-hr:border-gray-100">
                    {!! $page->content !!}
                </div>
            </div>

            <div class="mt-8 text-center">
                <a href="{{ url('/') }}"
                   class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-gray-900 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Home
                </a>
            </div>
        </div>
    </main>

    {{-- Footer — identical structure to website plugin layout --}}
    <footer class="bg-gray-900 text-gray-300 mt-16">
        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    @if($settings['app_logo'])
                        <img src="{{ storage_url($settings['app_logo']) }}" alt="{{ $settings['app_name'] }}"
                             class="h-8 w-auto object-contain brightness-0 invert">
                    @endif
                    <span class="text-white font-bold text-lg">{{ $settings['app_name'] }}</span>
                </div>
                <div class="flex items-center gap-6 text-sm">
                    <a href="{{ url('/') }}" class="hover:text-white transition-colors">Home</a>
                    <a href="{{ url('/shop') }}" class="hover:text-white transition-colors">Shop</a>
                    <a href="{{ url('/categories') }}" class="hover:text-white transition-colors">Categories</a>
                    <a href="{{ url('/cart') }}" class="hover:text-white transition-colors">Cart</a>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-6 text-center text-xs text-gray-500">
                @if($settings['footer_text'])
                    {!! $settings['footer_text'] !!}
                @else
                    &copy; {{ date('Y') }} {{ $settings['app_name'] }}. All rights reserved.
                @endif
            </div>
        </div>
    </footer>

    <style>
        .prose table { width: 100%; border-collapse: collapse; }
        .prose table th, .prose table td { padding: 0.75rem 1rem; border: 1px solid #e5e7eb; text-align: left; }
        .prose table th { background: #f9fafb; font-weight: 600; color: #111827; }
        .prose img { border-radius: 0.75rem; max-width: 100%; }
        .prose pre { background: #1f2937; color: #f9fafb; padding: 1.25rem; border-radius: 0.75rem; overflow-x: auto; }
        .prose code { background: #f3f4f6; color: #111827; padding: 0.15em 0.4em; border-radius: 0.25rem; font-size: 0.875em; }
        .prose pre code { background: transparent; color: inherit; padding: 0; }
        .prose a { color: {{ $settings['primary_color'] }}; }
    </style>

</body>
</html>
