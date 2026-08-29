@extends('admin.layouts.app')

@section('title', 'Website Manage & SEO Settings')

@push('styles')
<style>
    .tab-btn { transition: all 0.2s ease; }
    .tab-btn.active { color: #f97316; border-color: #f97316; background: #fff7ed; font-weight: 600; }
    .tab-panel { display: none; }
    .tab-panel.active { display: block; }
    .color-picker-wrapper { display: flex; align-items: center; gap: 0.75rem; }
    .color-preview-box { width: 42px; height: 42px; border-radius: 10px; border: 2px solid #e5e7eb; box-shadow: inset 0 2px 4px rgba(0,0,0,0.05); }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Top Navigation & Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold text-gray-900">Website Management</h1>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Plugin Active
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-gray-500">Configure desktop & mobile header logos, footer logo, description, primary & secondary colors, and search engine SEO metadata.</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <form action="{{ route('admin.website-settings.clear-cache') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-700 hover:bg-gray-50 font-medium text-xs sm:text-sm flex items-center gap-2 transition">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Clear Website Cache</span>
                </button>
            </form>
            <a href="{{ url('/') }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-orange-50 text-orange-600 hover:bg-orange-100 font-semibold text-xs sm:text-sm flex items-center gap-2 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                <span>View Live Site</span>
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center justify-between">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-medium flex items-center gap-2">
        <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Main Form --}}
    <form action="{{ route('admin.website-settings.update') }}" method="POST" enctype="multipart/form-data" id="websiteSettingsForm">
        @csrf
        @method('PUT')

        {{-- Section Tabs --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-6">
            <div class="flex border-b border-gray-100 bg-gray-50/50 p-1.5 gap-1 overflow-x-auto scrollbar-hide">
                <button type="button" onclick="switchTab('branding')" class="tab-btn active flex-1 sm:flex-initial px-5 py-3 rounded-xl text-xs sm:text-sm font-medium text-gray-600 flex items-center justify-center gap-2 whitespace-nowrap" data-tab="branding">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Logos & Colors</span>
                </button>
                <button type="button" onclick="switchTab('content')" class="tab-btn flex-1 sm:flex-initial px-5 py-3 rounded-xl text-xs sm:text-sm font-medium text-gray-600 flex items-center justify-center gap-2 whitespace-nowrap" data-tab="content">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                    <span>Footer & Copyright</span>
                </button>
                <button type="button" onclick="switchTab('seo')" class="tab-btn flex-1 sm:flex-initial px-5 py-3 rounded-xl text-xs sm:text-sm font-medium text-gray-600 flex items-center justify-center gap-2 whitespace-nowrap" data-tab="seo">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>SEO & Social Meta</span>
                </button>
            </div>

            <div class="p-6">
                {{-- TAB 1: LOGOS & COLORS --}}
                <div id="panel-branding" class="tab-panel active space-y-8">
                    
                    {{-- Logos Grid --}}
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 mb-1">Header & Footer Logos</h3>
                        <p class="text-xs text-gray-500 mb-6">Upload distinct logo images for laptop/desktop header, mobile header, and site footer.</p>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            
                            {{-- 1. Laptop Header Logo --}}
                            <div class="p-5 rounded-2xl border border-gray-200 bg-gray-50/50 flex flex-col justify-between space-y-4">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block text-sm font-semibold text-gray-900">Laptop Header Logo</label>
                                        <span class="text-[10px] uppercase font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">Desktop View</span>
                                    </div>
                                    <p class="text-xs text-gray-500">Displayed in top navigation bar on desktop/laptop screens.</p>
                                </div>

                                <div class="space-y-3">
                                    @php $laptopLogo = $settings['website_header_logo_desktop'] ?? ''; @endphp
                                    <div class="h-24 rounded-xl bg-white border border-dashed border-gray-300 p-2 flex items-center justify-center relative overflow-hidden group">
                                        @if($laptopLogo)
                                            <img id="prev_laptop_logo" src="{{ storage_url($laptopLogo) }}" class="max-h-full max-w-full object-contain">
                                        @else
                                            <img id="prev_laptop_logo" class="max-h-full max-w-full object-contain hidden">
                                            <div id="placeholder_laptop_logo" class="text-center text-gray-400">
                                                <svg class="w-8 h-8 mx-auto mb-1 stroke-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                <span class="text-xs">No Desktop Logo</span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <input type="file" name="website_header_logo_desktop" id="input_laptop_logo" accept="image/*" class="hidden" onchange="previewImage(this, 'prev_laptop_logo', 'placeholder_laptop_logo')">
                                        <button type="button" onclick="document.getElementById('input_laptop_logo').click()" class="flex-1 py-2 px-3 text-xs font-semibold rounded-lg bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 shadow-sm text-center">
                                            Upload Logo
                                        </button>
                                        @if($laptopLogo)
                                        <label class="flex items-center gap-1 text-xs text-rose-600 hover:text-rose-700 cursor-pointer py-2 px-2">
                                            <input type="checkbox" name="remove_website_header_logo_desktop" value="1" class="rounded text-rose-600 focus:ring-rose-500">
                                            <span>Remove</span>
                                        </label>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- 2. Mobile Header Logo --}}
                            <div class="p-5 rounded-2xl border border-gray-200 bg-gray-50/50 flex flex-col justify-between space-y-4">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block text-sm font-semibold text-gray-900">Mobile Header Logo</label>
                                        <span class="text-[10px] uppercase font-bold text-purple-600 bg-purple-50 px-2 py-0.5 rounded-full">Mobile View</span>
                                    </div>
                                    <p class="text-xs text-gray-500">Displayed in top navigation bar on smartphones and small screens.</p>
                                </div>

                                <div class="space-y-3">
                                    @php $mobileLogo = $settings['website_header_logo_mobile'] ?? ''; @endphp
                                    <div class="h-24 rounded-xl bg-white border border-dashed border-gray-300 p-2 flex items-center justify-center relative overflow-hidden">
                                        @if($mobileLogo)
                                            <img id="prev_mobile_logo" src="{{ storage_url($mobileLogo) }}" class="max-h-full max-w-full object-contain">
                                        @else
                                            <img id="prev_mobile_logo" class="max-h-full max-w-full object-contain hidden">
                                            <div id="placeholder_mobile_logo" class="text-center text-gray-400">
                                                <svg class="w-8 h-8 mx-auto mb-1 stroke-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                                <span class="text-xs">No Mobile Logo</span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <input type="file" name="website_header_logo_mobile" id="input_mobile_logo" accept="image/*" class="hidden" onchange="previewImage(this, 'prev_mobile_logo', 'placeholder_mobile_logo')">
                                        <button type="button" onclick="document.getElementById('input_mobile_logo').click()" class="flex-1 py-2 px-3 text-xs font-semibold rounded-lg bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 shadow-sm text-center">
                                            Upload Logo
                                        </button>
                                        @if($mobileLogo)
                                        <label class="flex items-center gap-1 text-xs text-rose-600 hover:text-rose-700 cursor-pointer py-2 px-2">
                                            <input type="checkbox" name="remove_website_header_logo_mobile" value="1" class="rounded text-rose-600 focus:ring-rose-500">
                                            <span>Remove</span>
                                        </label>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- 3. Footer Logo --}}
                            <div class="p-5 rounded-2xl border border-gray-200 bg-gray-50/50 flex flex-col justify-between space-y-4">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block text-sm font-semibold text-gray-900">Footer Logo</label>
                                        <span class="text-[10px] uppercase font-bold text-slate-700 bg-slate-200 px-2 py-0.5 rounded-full">Site Footer</span>
                                    </div>
                                    <p class="text-xs text-gray-500">Displayed in the main footer section at the bottom of pages.</p>
                                </div>

                                <div class="space-y-3">
                                    @php $footerLogo = $settings['website_footer_logo'] ?? ''; @endphp
                                    <div class="h-24 rounded-xl bg-slate-900 border border-dashed border-slate-700 p-2 flex items-center justify-center relative overflow-hidden">
                                        @if($footerLogo)
                                            <img id="prev_footer_logo" src="{{ storage_url($footerLogo) }}" class="max-h-full max-w-full object-contain">
                                        @else
                                            <img id="prev_footer_logo" class="max-h-full max-w-full object-contain hidden">
                                            <div id="placeholder_footer_logo" class="text-center text-gray-400">
                                                <svg class="w-8 h-8 mx-auto mb-1 stroke-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                <span class="text-xs text-gray-400">No Footer Logo</span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <input type="file" name="website_footer_logo" id="input_footer_logo" accept="image/*" class="hidden" onchange="previewImage(this, 'prev_footer_logo', 'placeholder_footer_logo')">
                                        <button type="button" onclick="document.getElementById('input_footer_logo').click()" class="flex-1 py-2 px-3 text-xs font-semibold rounded-lg bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 shadow-sm text-center">
                                            Upload Logo
                                        </button>
                                        @if($footerLogo)
                                        <label class="flex items-center gap-1 text-xs text-rose-600 hover:text-rose-700 cursor-pointer py-2 px-2">
                                            <input type="checkbox" name="remove_website_footer_logo" value="1" class="rounded text-rose-600 focus:ring-rose-500">
                                            <span>Remove</span>
                                        </label>
                                        @endif
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <hr class="border-gray-100">

                    {{-- Colors Section --}}
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 mb-1">Color Palette Theme</h3>
                        <p class="text-xs text-gray-500 mb-6">Choose the primary and secondary colors for your website buttons, badges, links, and layout accents.</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            {{-- Primary Color --}}
                            <div class="p-5 rounded-2xl border border-gray-200 bg-white shadow-sm space-y-3">
                                <label class="block text-sm font-semibold text-gray-900">Website Primary Color</label>
                                <p class="text-xs text-gray-500">Used for main action buttons (Add to Cart, Buy Now), active navigation tabs, links, and search highlights.</p>
                                
                                @php $priColor = $settings['website_primary_color'] ?? '#f97316'; @endphp
                                <div class="color-picker-wrapper">
                                    <input type="color" id="picker_primary" value="{{ $priColor }}" oninput="syncColor('picker_primary', 'input_primary', 'box_primary')" class="w-11 h-11 rounded-xl border border-gray-300 cursor-pointer p-0.5 bg-white">
                                    <input type="text" name="website_primary_color" id="input_primary" value="{{ $priColor }}" oninput="syncColor('input_primary', 'picker_primary', 'box_primary')" class="w-36 px-3 py-2 border border-gray-300 rounded-xl text-sm font-mono focus:ring-2 focus:ring-orange-500 uppercase">
                                    <div id="box_primary" class="color-preview-box" style="background-color: {{ $priColor }};"></div>
                                </div>
                            </div>

                            {{-- Secondary Color --}}
                            <div class="p-5 rounded-2xl border border-gray-200 bg-white shadow-sm space-y-3">
                                <label class="block text-sm font-semibold text-gray-900">Website Secondary Color</label>
                                <p class="text-xs text-gray-500">Used for secondary buttons, site footer background, dark top header banners, and sub-badges.</p>
                                
                                @php $secColor = $settings['website_secondary_color'] ?? '#1e293b'; @endphp
                                <div class="color-picker-wrapper">
                                    <input type="color" id="picker_secondary" value="{{ $secColor }}" oninput="syncColor('picker_secondary', 'input_secondary', 'box_secondary')" class="w-11 h-11 rounded-xl border border-gray-300 cursor-pointer p-0.5 bg-white">
                                    <input type="text" name="website_secondary_color" id="input_secondary" value="{{ $secColor }}" oninput="syncColor('input_secondary', 'picker_secondary', 'box_secondary')" class="w-36 px-3 py-2 border border-gray-300 rounded-xl text-sm font-mono focus:ring-2 focus:ring-orange-500 uppercase">
                                    <div id="box_secondary" class="color-preview-box" style="background-color: {{ $secColor }};"></div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>

                {{-- TAB 2: FOOTER & COPYRIGHT CONTENT --}}
                <div id="panel-content" class="tab-panel space-y-6">
                    <div>
                        <label for="website_description" class="block text-sm font-semibold text-gray-900 mb-1">Website Description</label>
                        <p class="text-xs text-gray-500 mb-2">Short paragraph displayed in the brand section of the website footer.</p>
                        <textarea name="website_description" id="website_description" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500" placeholder="Fast delivery of groceries and daily essentials right to your doorstep.">{{ $settings['website_description'] ?? '' }}</textarea>
                    </div>

                    <div>
                        <label for="website_copyright" class="block text-sm font-semibold text-gray-900 mb-1">Copyright Notice</label>
                        <p class="text-xs text-gray-500 mb-2">Copyright text displayed at the bottom of every page in the footer.</p>
                        <input type="text" name="website_copyright" id="website_copyright" value="{{ $settings['website_copyright'] ?? '' }}" class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500" placeholder="© 2026 InAllCart. All rights reserved.">
                    </div>
                </div>

                {{-- TAB 3: SEO & SOCIAL METADATA --}}
                <div id="panel-seo" class="tab-panel space-y-6">
                    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs sm:text-sm flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <strong class="font-semibold">Search Engine Optimization (SEO) & Social Sharing</strong>
                            <p class="mt-0.5 text-amber-800">These default meta tags apply to your homepage and fallback across all storefront pages. Individual product, category, and brand pages automatically generate clean, structured JSON-LD and OpenGraph tags.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div>
                            <label for="website_seo_title" class="block text-sm font-semibold text-gray-900 mb-1">Default SEO Meta Title</label>
                            <p class="text-xs text-gray-500 mb-2">Recommended length: 50–60 characters.</p>
                            <input type="text" name="website_seo_title" id="website_seo_title" value="{{ $settings['website_seo_title'] ?? '' }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-orange-500" placeholder="InAllCart - Online Grocery & Essentials Store">
                        </div>

                        <div>
                            <label for="website_seo_twitter_handle" class="block text-sm font-semibold text-gray-900 mb-1">Twitter / X Username</label>
                            <p class="text-xs text-gray-500 mb-2">Used for Twitter Card attribution (e.g. @inallcart).</p>
                            <input type="text" name="website_seo_twitter_handle" id="website_seo_twitter_handle" value="{{ $settings['website_seo_twitter_handle'] ?? '' }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-orange-500" placeholder="@inallcart">
                        </div>

                    </div>

                    <div>
                        <label for="website_seo_description" class="block text-sm font-semibold text-gray-900 mb-1">Default Meta Description</label>
                        <p class="text-xs text-gray-500 mb-2">Recommended length: 150–160 characters. Appears in search engine snippets.</p>
                        <textarea name="website_seo_description" id="website_seo_description" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-orange-500" placeholder="Order fresh groceries, daily essentials, snacks, and household items online for instant doorstep delivery.">{{ $settings['website_seo_description'] ?? '' }}</textarea>
                    </div>

                    <div>
                        <label for="website_seo_keywords" class="block text-sm font-semibold text-gray-900 mb-1">Default Meta Keywords</label>
                        <p class="text-xs text-gray-500 mb-2">Comma-separated key phrases relevant to your website.</p>
                        <input type="text" name="website_seo_keywords" id="website_seo_keywords" value="{{ $settings['website_seo_keywords'] ?? '' }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-orange-500" placeholder="grocery delivery, online supermarket, fresh produce, daily essentials, online shopping">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                        
                        {{-- OpenGraph Social Image --}}
                        <div class="p-5 rounded-2xl border border-gray-200 bg-gray-50/50 space-y-3">
                            <label class="block text-sm font-semibold text-gray-900">Social Share Image (OpenGraph / Twitter)</label>
                            <p class="text-xs text-gray-500">Image shown when sharing your site link on WhatsApp, Facebook, X, or LinkedIn. Recommended: 1200x630px.</p>
                            
                            @php $ogImg = $settings['website_seo_og_image'] ?? ''; @endphp
                            <div class="h-28 rounded-xl bg-white border border-dashed border-gray-300 p-2 flex items-center justify-center relative overflow-hidden">
                                @if($ogImg)
                                    <img id="prev_og_img" src="{{ storage_url($ogImg) }}" class="max-h-full max-w-full object-contain">
                                @else
                                    <img id="prev_og_img" class="max-h-full max-w-full object-contain hidden">
                                    <div id="placeholder_og_img" class="text-center text-gray-400">
                                        <svg class="w-8 h-8 mx-auto mb-1 stroke-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span class="text-xs">No Social Image</span>
                                    </div>
                                @endif
                            </div>

                            <div class="flex items-center gap-2">
                                <input type="file" name="website_seo_og_image" id="input_og_img" accept="image/*" class="hidden" onchange="previewImage(this, 'prev_og_img', 'placeholder_og_img')">
                                <button type="button" onclick="document.getElementById('input_og_img').click()" class="flex-1 py-2 px-3 text-xs font-semibold rounded-lg bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 shadow-sm text-center">
                                    Upload Social Banner
                                </button>
                                @if($ogImg)
                                <label class="flex items-center gap-1 text-xs text-rose-600 hover:text-rose-700 cursor-pointer py-2 px-2">
                                    <input type="checkbox" name="remove_website_seo_og_image" value="1" class="rounded text-rose-600 focus:ring-rose-500">
                                    <span>Remove</span>
                                </label>
                                @endif
                            </div>
                        </div>

                        {{-- Search Engine Indexing Switch --}}
                        <div class="p-5 rounded-2xl border border-gray-200 bg-white shadow-sm space-y-4 flex flex-col justify-between">
                            <div>
                                <label class="block text-sm font-semibold text-gray-900 mb-1">Search Engine Indexing</label>
                                <p class="text-xs text-gray-500">Allow Google, Bing, and search engines to index and rank your website storefront.</p>
                            </div>

                            <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 border border-gray-200">
                                <span class="text-xs font-semibold text-gray-700">Allow Indexing (robots: index, follow)</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="website_seo_indexing_enabled" value="1" class="sr-only peer" {{ ($settings['website_seo_indexing_enabled'] ?? '1') !== '0' ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"></div>
                                </label>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            {{-- Form Submit Bar --}}
            <div class="p-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                <span class="text-xs text-gray-500">Changes will reflect on the live storefront immediately after saving.</span>
                <button type="submit" class="px-6 py-3 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-semibold text-sm shadow-sm transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Save Website Settings</span>
                </button>
            </div>
        </div>

    </form>

</div>
@endsection

@push('scripts')
<script>
    function switchTab(tabId) {
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('data-tab') === tabId);
        });
        document.querySelectorAll('.tab-panel').forEach(panel => {
            panel.classList.toggle('active', panel.id === 'panel-' + tabId);
        });
    }

    function previewImage(input, imgId, placeholderId) {
        const file = input.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById(imgId);
                const ph = document.getElementById(placeholderId);
                if (img) {
                    img.src = e.target.result;
                    img.classList.remove('hidden');
                }
                if (ph) {
                    ph.classList.add('hidden');
                }
            };
            reader.readAsDataURL(file);
        }
    }

    function syncColor(srcId, targetId, boxId) {
        const val = document.getElementById(srcId).value;
        document.getElementById(targetId).value = val;
        const box = document.getElementById(boxId);
        if (box) {
            box.style.backgroundColor = val;
        }
    }
</script>
@endpush
