@extends('admin.layouts.app')

@section('title', 'Splash Screen Builder')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 pb-4 border-b border-gray-200">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <span>🎨</span> Splash Screen Builder
            </h1>
            <p class="text-sm text-gray-500 mt-1">Configure customer app splash screen style: With Logo & Branding OR Full Screen Image/GIF.</p>
        </div>
        <div class="mt-4 md:mt-0 flex gap-3">
            <a href="{{ url('admin/splash-screen/builder') }}" class="btn-secondary">
                Reset
            </a>
            <button type="submit" form="splashBuilderForm" class="btn-primary flex items-center gap-2">
                <span>💾</span> Save & Publish Splash Config
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium flex items-center gap-2 shadow-sm">
        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <form id="splashBuilderForm" action="{{ url('admin/splash-screen/builder') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Left Column: Settings & Controls (7 Cols) -->
            <div class="lg:col-span-7 space-y-6">
                
                <!-- 1. Master Activation Status -->
                <div class="card p-6 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 mb-1 flex items-center gap-2">
                            <span class="text-amber-500">1.</span> Splash Screen Activation
                        </h2>
                        <p class="text-xs text-gray-500">Enable or disable custom dynamic splash screen in Customer App.</p>
                    </div>
                    <label class="flex items-center gap-2 px-4 py-2 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-bold cursor-pointer hover:bg-emerald-100 transition">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ ($setting->is_active ?? true) ? 'checked' : '' }} onchange="updateLivePreview()" class="checkbox text-emerald-600">
                        <span>Enable Custom Splash</span>
                    </label>
                </div>

                <!-- 2. Select Splash Screen Style -->
                <div class="card p-6 space-y-4">
                    <h2 class="text-base font-bold text-gray-900 mb-1 flex items-center gap-2">
                        <span class="text-amber-500">2.</span> Select Splash Screen Style
                    </h2>
                    <p class="text-xs text-gray-500">Choose between a Logo & Typography Splash OR a Full Screen Image/GIF Splash.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Style 1 Radio Option -->
                        <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all" id="style_card_1">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-bold text-sm text-gray-900">1. With Logo & Branding</span>
                                <input type="radio" name="active_screen_style" value="1" id="style_radio_1" {{ ($setting->active_screen_style ?? 1) == 1 ? 'checked' : '' }} onchange="toggleSplashStyle(1)" class="w-4 h-4 text-indigo-600">
                            </div>
                            <p class="text-xs text-gray-500">Animated logo, optional app title, motto, and custom background colors.</p>
                        </label>

                        <!-- Style 2 Radio Option -->
                        <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all" id="style_card_2">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-bold text-sm text-gray-900">2. Full Screen Image / GIF</span>
                                <input type="radio" name="active_screen_style" value="2" id="style_radio_2" {{ ($setting->active_screen_style ?? 1) == 2 ? 'checked' : '' }} onchange="toggleSplashStyle(2)" class="w-4 h-4 text-indigo-600">
                            </div>
                            <p class="text-xs text-gray-500">Upload a full screen poster image or animated GIF to cover the entire splash.</p>
                        </label>
                    </div>
                </div>

                <!-- STYLE 1 CONTROLS: WITH LOGO -->
                <div id="style_1_controls" class="space-y-6">
                    <!-- Logo & Animation Setup -->
                    <div class="card p-6 space-y-4">
                        <h2 class="text-base font-bold text-gray-900 mb-1 flex items-center gap-2">
                            <span class="text-amber-500">3.</span> Logo & Size Setup
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="label">Upload Logo Image</label>
                                <input type="file" name="logo_file" accept="image/*" onchange="previewLogo(this)" class="input p-1 bg-gray-50">
                            </div>

                            <div>
                                <label class="label">Or Logo URL</label>
                                <input type="url" name="logo_url" id="logo_url_input" value="{{ old('logo_url', $setting->logo_url ?? '') }}" oninput="updateLivePreview()" placeholder="https://example.com/logo.png" class="input">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="label">Logo Animation Style</label>
                                <select name="logo_animation" id="logo_animation" onchange="updateLivePreview()" class="input">
                                    <option value="pulse" {{ ($setting->logo_animation ?? 'pulse') == 'pulse' ? 'selected' : '' }}>Pulse & Heartbeat</option>
                                    <option value="bounce" {{ ($setting->logo_animation ?? '') == 'bounce' ? 'selected' : '' }}>Bouncy Spring Entrance</option>
                                    <option value="scale_fade" {{ ($setting->logo_animation ?? '') == 'scale_fade' ? 'selected' : '' }}>Smooth Scale & Fade In</option>
                                    <option value="rotating_crown" {{ ($setting->logo_animation ?? '') == 'rotating_crown' ? 'selected' : '' }}>Rotating Halo Crown</option>
                                    <option value="shimmer_sheen" {{ ($setting->logo_animation ?? '') == 'shimmer_sheen' ? 'selected' : '' }}>Shimmer Light Sheen</option>
                                </select>
                            </div>

                            <div>
                                <label class="label">Logo Size Option</label>
                                <select name="logo_size" id="logo_size" onchange="toggleCustomLogoPx()" class="input">
                                    <option value="small" {{ ($setting->logo_size ?? '') == 'small' ? 'selected' : '' }}>Small (80px)</option>
                                    <option value="medium" {{ ($setting->logo_size ?? 'medium') == 'medium' ? 'selected' : '' }}>Medium (100px)</option>
                                    <option value="extra_medium" {{ ($setting->logo_size ?? '') == 'extra_medium' ? 'selected' : '' }}>Extra Medium (120px)</option>
                                    <option value="large" {{ ($setting->logo_size ?? '') == 'large' ? 'selected' : '' }}>Large (140px)</option>
                                    <option value="extra_large" {{ ($setting->logo_size ?? '') == 'extra_large' ? 'selected' : '' }}>Extra Large (170px)</option>
                                    <option value="custom" {{ ($setting->logo_size ?? '') == 'custom' ? 'selected' : '' }}>Custom Size (px)</option>
                                </select>
                            </div>

                            <div id="custom_logo_px_container" class="{{ ($setting->logo_size ?? '') == 'custom' ? '' : 'hidden' }}">
                                <label class="label">Custom Logo Size (px)</label>
                                <input type="number" name="logo_size_px" id="logo_size_px" value="{{ old('logo_size_px', $setting->logo_size_px ?? 150) }}" min="30" max="500" oninput="updateLivePreview()" placeholder="e.g. 150" class="input">
                                <p class="form-hint">Specify exact px size</p>
                            </div>
                        </div>
                    </div>

                    <!-- Background & Colors -->
                    <div class="card p-6 space-y-4">
                        <h2 class="text-base font-bold text-gray-900 mb-1 flex items-center gap-2">
                            <span class="text-amber-500">4.</span> Background & Colors
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="label">Background Style Preset</label>
                                <select name="background_style" id="background_style" onchange="updateLivePreview()" class="input">
                                    <option value="gradient_vibrant" {{ ($setting->background_style ?? 'gradient_vibrant') == 'gradient_vibrant' ? 'selected' : '' }}>Vibrant Dual Gradient</option>
                                    <option value="dark_glassmorphic" {{ ($setting->background_style ?? '') == 'dark_glassmorphic' ? 'selected' : '' }}>Dark Glassmorphism Neon</option>
                                    <option value="solid_brand" {{ ($setting->background_style ?? '') == 'solid_brand' ? 'selected' : '' }}>Solid Brand Color</option>
                                    <option value="geometric_particles" {{ ($setting->background_style ?? '') == 'geometric_particles' ? 'selected' : '' }}>Geometric Particles Pattern</option>
                                    <option value="floating_rings" {{ ($setting->background_style ?? '') == 'floating_rings' ? 'selected' : '' }}>Floating Aura Rings</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="label text-[11px]">Primary Color</label>
                                    <div class="flex items-center gap-2">
                                        <input type="color" name="primary_color" id="primary_color" value="{{ old('primary_color', $setting->primary_color ?? '#F97316') }}" onchange="updateLivePreview()" class="h-8 w-10 border border-gray-300 rounded cursor-pointer p-0">
                                        <input type="text" id="primary_color_text" value="{{ old('primary_color', $setting->primary_color ?? '#F97316') }}" oninput="syncColorInput('primary_color', this.value)" class="input py-1 text-xs">
                                    </div>
                                </div>
                                <div>
                                    <label class="label text-[11px]">Secondary Color</label>
                                    <div class="flex items-center gap-2">
                                        <input type="color" name="secondary_color" id="secondary_color" value="{{ old('secondary_color', $setting->secondary_color ?? '#EA580C') }}" onchange="updateLivePreview()" class="h-8 w-10 border border-gray-300 rounded cursor-pointer p-0">
                                        <input type="text" id="secondary_color_text" value="{{ old('secondary_color', $setting->secondary_color ?? '#EA580C') }}" oninput="syncColorInput('secondary_color', this.value)" class="input py-1 text-xs">
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2 md:col-span-2">
                                <div>
                                    <label class="label text-[11px]">Background Color</label>
                                    <div class="flex items-center gap-2">
                                        <input type="color" name="background_color" id="background_color" value="{{ old('background_color', $setting->background_color ?? '#0F172A') }}" onchange="updateLivePreview()" class="h-8 w-10 border border-gray-300 rounded cursor-pointer p-0">
                                        <input type="text" id="background_color_text" value="{{ old('background_color', $setting->background_color ?? '#0F172A') }}" oninput="syncColorInput('background_color', this.value)" class="input py-1 text-xs">
                                    </div>
                                </div>
                                <div>
                                    <label class="label text-[11px]">Text & Icon Color</label>
                                    <div class="flex items-center gap-2">
                                        <input type="color" name="text_color" id="text_color" value="{{ old('text_color', $setting->text_color ?? '#FFFFFF') }}" onchange="updateLivePreview()" class="h-8 w-10 border border-gray-300 rounded cursor-pointer p-0">
                                        <input type="text" id="text_color_text" value="{{ old('text_color', $setting->text_color ?? '#FFFFFF') }}" oninput="syncColorInput('text_color', this.value)" class="input py-1 text-xs">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content & Typography -->
                    <div class="card p-6 space-y-4">
                        <h2 class="text-base font-bold text-gray-900 mb-1 flex items-center gap-2">
                            <span class="text-amber-500">5.</span> Content & Typography
                        </h2>

                        <div>
                            <label class="label">App Title Name <span class="text-gray-400 font-normal">(Optional - leave empty to hide)</span></label>
                            <input type="text" name="title_text" id="title_text" value="{{ old('title_text', $setting->title_text ?? '') }}" oninput="updateLivePreview()" placeholder="e.g. InAllCart (Optional)" class="input">
                        </div>

                        <div>
                            <label class="label">Subtitle / Motto <span class="text-gray-400 font-normal">(Optional - leave empty to hide)</span></label>
                            <input type="text" name="subtitle_text" id="subtitle_text" value="{{ old('subtitle_text', $setting->subtitle_text ?? '') }}" oninput="updateLivePreview()" placeholder="e.g. Everything Delivered to Your Doorstep (Optional)" class="input">
                        </div>
                    </div>
                </div>

                <!-- STYLE 2 CONTROLS: FULL SCREEN IMAGE / GIF -->
                <div id="style_2_controls" class="card p-6 space-y-4 hidden">
                    <h2 class="text-base font-bold text-gray-900 mb-1 flex items-center gap-2">
                        <span class="text-amber-500">3.</span> Full Screen Image / GIF Setup
                    </h2>
                    <p class="text-xs text-gray-500">Upload a high-resolution poster image or animated GIF (JPG, PNG, WEBP, GIF up to 10MB) to display full screen as the splash.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="label">Upload Full Screen Image / GIF</label>
                            <input type="file" name="fullscreen_media_file" accept="image/jpeg,image/png,image/webp,image/gif" onchange="previewFullscreenMedia(this)" class="input p-1 bg-gray-50">
                            <p class="form-hint">Supports static images and animated GIFs</p>
                        </div>

                        <div>
                            <label class="label">Or Full Screen Image / GIF URL</label>
                            <input type="url" name="fullscreen_media_url" id="fullscreen_media_url_input" value="{{ old('fullscreen_media_url', $setting->fullscreen_media_url ?? '') }}" oninput="updateLivePreview()" placeholder="https://example.com/splash-banner.gif" class="input">
                        </div>
                    </div>

                    @if(!empty($setting->fullscreen_media_url))
                    <div class="mt-3 flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-200">
                        <div class="w-16 h-24 rounded-lg bg-gray-200 overflow-hidden border border-gray-300">
                            <img src="{{ filter_var($setting->fullscreen_media_url, FILTER_VALIDATE_URL) ? $setting->fullscreen_media_url : asset($setting->fullscreen_media_url) }}" class="w-full h-full object-cover">
                        </div>
                        <span class="text-xs text-gray-600 font-medium">Current Full Screen Media</span>
                    </div>
                    @endif
                </div>

                <!-- SHARED FOOTER SETTINGS -->
                <div class="card p-6 space-y-4">
                    <h2 class="text-base font-bold text-gray-900 mb-1 flex items-center gap-2">
                        <span class="text-amber-500">6.</span> Footer & Loading Bar
                    </h2>

                    <div>
                        <label class="label">Tagline Pill Text</label>
                        <input type="text" name="tagline_text" id="tagline_text" value="{{ old('tagline_text', $setting->tagline_text ?? 'Fast · Reliable · Premium') }}" oninput="updateLivePreview()" class="input">
                    </div>

                    <div class="flex items-center gap-6 pt-2">
                        <label class="flex items-center gap-2 text-xs font-semibold text-gray-700 cursor-pointer">
                            <input type="checkbox" name="show_tagline" id="show_tagline" value="1" {{ ($setting->show_tagline ?? true) ? 'checked' : '' }} onchange="updateLivePreview()" class="checkbox text-amber-500">
                            <span>Show Tagline Footer</span>
                        </label>
                        <label class="flex items-center gap-2 text-xs font-semibold text-gray-700 cursor-pointer">
                            <input type="checkbox" name="show_loading_bar" id="show_loading_bar" value="1" {{ ($setting->show_loading_bar ?? true) ? 'checked' : '' }} onchange="updateLivePreview()" class="checkbox text-amber-500">
                            <span>Show Loading Indicator Bar</span>
                        </label>
                    </div>
                </div>

            </div>

            <!-- Right Column: Interactive Mobile Frame Preview (5 Cols) -->
            <div class="lg:col-span-5">
                <div class="sticky top-6">
                    <div class="bg-gray-900 rounded-[40px] p-4 shadow-2xl border-4 border-gray-800 max-w-[340px] mx-auto">
                        <!-- Phone Screen -->
                        <div id="phone_screen" class="relative w-full h-[620px] rounded-[30px] overflow-hidden shadow-inner flex flex-col justify-between p-6 transition-all duration-500" style="background: linear-gradient(135deg, {{ $setting->primary_color ?? '#F97316' }}, {{ $setting->secondary_color ?? '#EA580C' }}); color: {{ $setting->text_color ?? '#FFFFFF' }};">
                            
                            <!-- Fullscreen Media Background Layer (for Style 2) -->
                            <div id="fullscreen_bg_layer" class="absolute inset-0 z-0 hidden bg-cover bg-center"></div>

                            <!-- Overlay tint for legibility -->
                            <div id="fullscreen_overlay_tint" class="absolute inset-0 z-0 bg-black/20 hidden"></div>

                            <!-- Status Bar -->
                            <div class="relative z-10 flex justify-between items-center text-[10px] font-medium opacity-80 pt-1">
                                <span>9:41</span>
                                <div class="flex items-center gap-1">
                                    <span>📶</span>
                                    <span>🔋</span>
                                </div>
                            </div>

                            <!-- Screen Style Specific Layout Container -->
                            <div id="preview_content" class="relative z-10 my-auto flex flex-col items-center text-center space-y-4 w-full">
                                <!-- Rendered dynamically via JavaScript -->
                            </div>

                            <!-- Bottom Loading & Tagline Footer -->
                            <div class="relative z-10 pb-4 flex flex-col items-center w-full">
                                <div id="preview_loading_bar" class="w-28 h-1 bg-white/30 rounded-full overflow-hidden mb-3">
                                    <div class="h-full bg-white animate-pulse w-2/3 rounded-full"></div>
                                </div>
                                <div id="preview_footer_text" class="text-[10px] opacity-75 font-semibold">
                                    {{ $setting->tagline_text ?? 'Fast · Reliable · Premium' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="text-center text-xs text-gray-500 mt-3 font-semibold">⚡ Real-Time Dynamic Mobile Preview</p>
                </div>
            </div>

        </div>
    </form>
</div>

<script>
    let currentLogoSrc = "{{ $setting->logo_url ? (filter_var($setting->logo_url, FILTER_VALIDATE_URL) ? $setting->logo_url : asset($setting->logo_url)) : '' }}";
    let currentFullscreenSrc = "{{ $setting->fullscreen_media_url ? (filter_var($setting->fullscreen_media_url, FILTER_VALIDATE_URL) ? $setting->fullscreen_media_url : asset($setting->fullscreen_media_url)) : '' }}";

    function toggleSplashStyle(style) {
        document.getElementById('style_radio_1').checked = (style === 1);
        document.getElementById('style_radio_2').checked = (style === 2);

        const card1 = document.getElementById('style_card_1');
        const card2 = document.getElementById('style_card_2');
        const controls1 = document.getElementById('style_1_controls');
        const controls2 = document.getElementById('style_2_controls');

        if (style === 1) {
            card1.className = 'relative flex flex-col p-4 rounded-xl border-2 border-indigo-600 bg-indigo-50/20 cursor-pointer transition-all';
            card2.className = 'relative flex flex-col p-4 rounded-xl border-2 border-gray-200 cursor-pointer transition-all';
            controls1.classList.remove('hidden');
            controls2.classList.add('hidden');
        } else {
            card1.className = 'relative flex flex-col p-4 rounded-xl border-2 border-gray-200 cursor-pointer transition-all';
            card2.className = 'relative flex flex-col p-4 rounded-xl border-2 border-indigo-600 bg-indigo-50/20 cursor-pointer transition-all';
            controls1.classList.add('hidden');
            controls2.classList.remove('hidden');
        }

        updateLivePreview();
    }

    function toggleCustomLogoPx() {
        const val = document.getElementById('logo_size').value;
        const container = document.getElementById('custom_logo_px_container');
        if (val === 'custom') {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
        }
        updateLivePreview();
    }

    function syncColorInput(fieldId, val) {
        document.getElementById(fieldId).value = val;
        updateLivePreview();
    }

    function previewLogo(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                currentLogoSrc = e.target.result;
                updateLivePreview();
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function previewFullscreenMedia(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                currentFullscreenSrc = e.target.result;
                updateLivePreview();
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function updateLivePreview() {
        const isStyle1 = document.getElementById('style_radio_1').checked;
        const primaryColor = document.getElementById('primary_color').value;
        const secondaryColor = document.getElementById('secondary_color').value;
        const backgroundColor = document.getElementById('background_color').value;
        const textColor = document.getElementById('text_color').value;
        const titleText = document.getElementById('title_text').value.trim();
        const subtitleText = document.getElementById('subtitle_text').value.trim();
        const taglineText = document.getElementById('tagline_text').value.trim() || 'Fast · Reliable · Premium';
        const showTagline = document.getElementById('show_tagline').checked;
        const showLoadingBar = document.getElementById('show_loading_bar').checked;
        const bgPreset = document.getElementById('background_style').value;
        const logoAnim = document.getElementById('logo_animation').value;
        const logoUrlInput = document.getElementById('logo_url_input').value.trim();
        const fullscreenUrlInput = document.getElementById('fullscreen_media_url_input').value.trim();

        if (logoUrlInput !== '') currentLogoSrc = logoUrlInput;
        if (fullscreenUrlInput !== '') currentFullscreenSrc = fullscreenUrlInput;

        const phoneScreen = document.getElementById('phone_screen');
        const fullscreenBgLayer = document.getElementById('fullscreen_bg_layer');
        const fullscreenOverlayTint = document.getElementById('fullscreen_overlay_tint');
        const previewContent = document.getElementById('preview_content');
        const previewLoadingBar = document.getElementById('preview_loading_bar');
        const previewFooterText = document.getElementById('preview_footer_text');

        previewLoadingBar.style.display = showLoadingBar ? 'block' : 'none';
        previewFooterText.style.display = showTagline ? 'block' : 'none';
        previewFooterText.innerText = taglineText;

        if (!isStyle1) {
            // STYLE 2: FULL SCREEN IMAGE / GIF
            fullscreenBgLayer.classList.remove('hidden');
            fullscreenOverlayTint.classList.remove('hidden');

            if (currentFullscreenSrc) {
                fullscreenBgLayer.style.backgroundImage = `url('${currentFullscreenSrc}')`;
            } else {
                fullscreenBgLayer.style.background = '#1E293B';
            }

            previewContent.innerHTML = `
                <div class="py-12 flex flex-col items-center">
                    ${!currentFullscreenSrc ? '<p class="text-xs opacity-75 font-semibold bg-black/40 px-3 py-1.5 rounded-lg">Upload Full Screen Image / GIF</p>' : ''}
                </div>
            `;
        } else {
            // STYLE 1: WITH LOGO
            fullscreenBgLayer.classList.add('hidden');
            fullscreenOverlayTint.classList.add('hidden');

            if (bgPreset === 'dark_glassmorphic') {
                phoneScreen.style.background = backgroundColor;
            } else if (bgPreset === 'solid_brand') {
                phoneScreen.style.background = primaryColor;
            } else if (bgPreset === 'geometric_particles') {
                phoneScreen.style.background = `radial-gradient(circle at top left, ${primaryColor}, ${backgroundColor})`;
            } else if (bgPreset === 'floating_rings') {
                phoneScreen.style.background = `radial-gradient(circle at center, ${primaryColor}, ${secondaryColor})`;
            } else {
                phoneScreen.style.background = `linear-gradient(135deg, ${primaryColor}, ${secondaryColor})`;
            }
            phoneScreen.style.color = textColor;

            const logoSizeOption = document.getElementById('logo_size').value;
            let logoPx = 100;
            if (logoSizeOption === 'small') logoPx = 80;
            else if (logoSizeOption === 'medium') logoPx = 100;
            else if (logoSizeOption === 'extra_medium') logoPx = 120;
            else if (logoSizeOption === 'large') logoPx = 140;
            else if (logoSizeOption === 'extra_large') logoPx = 170;
            else if (logoSizeOption === 'custom') {
                logoPx = parseInt(document.getElementById('logo_size_px').value) || 150;
            }

            let animCss = '';
            if (logoAnim === 'pulse') animCss = 'animate-pulse scale-105';
            else if (logoAnim === 'bounce') animCss = 'animate-bounce';
            else if (logoAnim === 'scale_fade') animCss = 'transition-transform transform hover:scale-110';
            else if (logoAnim === 'rotating_crown') animCss = 'animate-spin-slow';

            let logoHtml = currentLogoSrc 
                ? `<img src="${currentLogoSrc}" style="width:${logoPx}px; height:${logoPx}px;" class="object-contain drop-shadow-xl ${animCss}">`
                : `<div style="width:${logoPx}px; height:${logoPx}px;" class="bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center text-3xl font-extrabold shadow-xl ${animCss}">👑</div>`;

            let titleHtml = titleText !== '' ? `<h1 class="text-2xl font-black tracking-tight" style="color:${textColor}">${titleText}</h1>` : '';
            let subtitleHtml = subtitleText !== '' ? `<p class="text-xs opacity-90 max-w-[220px] leading-relaxed font-medium mt-1">${subtitleText}</p>` : '';

            previewContent.innerHTML = `
                <div class="p-3 bg-white/10 backdrop-blur-md rounded-3xl border border-white/20 shadow-2xl mb-2">
                    ${logoHtml}
                </div>
                ${titleHtml}
                ${subtitleHtml}
            `;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const initialStyle = {{ ($setting->active_screen_style ?? 1) }};
        toggleSplashStyle(initialStyle);
        toggleCustomLogoPx();

        document.getElementById('primary_color_text').value = document.getElementById('primary_color').value;
        document.getElementById('secondary_color_text').value = document.getElementById('secondary_color').value;
        document.getElementById('background_color_text').value = document.getElementById('background_color').value;
        document.getElementById('text_color_text').value = document.getElementById('text_color').value;
    });
</script>
@endsection
