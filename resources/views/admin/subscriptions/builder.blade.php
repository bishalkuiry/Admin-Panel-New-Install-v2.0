@extends('admin.layouts.app')

@section('title', 'VIP Membership Page & Theme Builder')

@php
    $themeStyle = $sections['theme_style'] ?? 'zomato_gold';
    $badgeText = $sections['badge_text'] ?? 'VIP MEMBERSHIP';
    $subHeaderMotto = $sections['sub_header_motto'] ?? 'More Perks. More Moments.';
    $heroTitle = $sections['hero_title'] ?? 'Upgrade your everyday experience with a premium membership built for people who expect more. Save more, earn more and get treated like a VIP every time.';
    $primaryColor = $sections['primary_color'] ?? '#F97316';
    $secondaryColor = $sections['secondary_color'] ?? '#EA580C';
    $subHeading = $sections['sub_heading'] ?? 'Premium benefits. Effortless savings. One membership.';
    $vipAdvantageTitle = $sections['vip_advantage_title'] ?? 'Your VIP advantage';
    $vipAdvantageSubtitle = $sections['vip_advantage_subtitle'] ?? 'Everything you love, with more value. Four powerful benefits designed to make every order, purchase and support moment feel better.';
    $perksList = $sections['perks'] ?? [
        ['icon' => '🚚', 'title' => 'Unlimited Free Delivery', 'desc' => 'Skip delivery fees and enjoy your favorites whenever you want, without counting every order.'],
        ['icon' => '🏷️', 'title' => 'Extra Member Discount', 'desc' => 'Unlock exclusive member-only pricing and stack more value into the purchases you already make.'],
        ['icon' => '💰', 'title' => 'Wallet Cashback', 'desc' => 'Get rewarded as you spend. Cashback goes straight to your wallet for your next experience.'],
        ['icon' => '⚡', 'title' => 'Priority Support', 'desc' => "Need help? VIP members move to the front of the line for faster, more attentive support."],
    ];
    $whyVipTitle = $sections['why_vip_title'] ?? 'Why Go VIP?';
    $whyVipSubtitle = $sections['why_vip_subtitle'] ?? "Because ordinary is overrated. VIP turns everyday spending into a smarter, more rewarding experience. Whether you're ordering in, shopping your favorites or looking for support, your membership keeps giving back.";
    $highlights = $sections['highlights'] ?? [
        'Designed for frequent users who want maximum value.',
        'Benefits work together to amplify your savings.',
        'One premium membership. A better everyday experience.',
        '✦ Member-only value',
        '✓ More savings',
    ];
    $upgradeTitle = $sections['upgrade_title'] ?? 'Your upgrade starts here';
    $upgradeSubtitle = $sections['upgrade_subtitle'] ?? 'Ready to live a little more VIP? Unlock premium benefits and make every experience count.';
    $footerTagline = $sections['footer_tagline'] ?? 'VIP Membership · Premium experiences, everyday value.';
    $fullHtmlCss = $sections['full_html_css'] ?? '';
@endphp

@section('content')
<div x-data="vipPageBuilder()" class="space-y-6">
    <!-- Header Controls -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-4 sm:p-6 rounded-2xl shadow-sm border border-gray-100">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 flex items-center gap-2">
                <span>🎨 VIP Membership Page &amp; Theme Builder</span>
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">Customize themes, hero texts, colors, perks, section headings, and custom HTML/CSS for the Customer App in real-time</p>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.subscriptions.user-plans.index') }}" class="btn btn-secondary text-xs">Back to Plans</a>

            <button type="button" @click="savePage()" class="btn btn-primary text-xs flex items-center gap-1.5 shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                <span>Save &amp; Publish VIP Page</span>
            </button>
        </div>
    </div>

    <!-- 1. Select Flutter Design Theme Card -->
    <div class="card p-5 space-y-4">
        <div class="border-b pb-3 flex items-center justify-between">
            <h3 class="font-extrabold text-base text-gray-900 flex items-center gap-2">
                <span>🌟 Select Flutter App Theme Design Style</span>
            </h3>
            <span class="text-xs text-gray-400">Selected theme renders automatically in Customer App</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Theme 1: Zomato Gold / Crown Premium -->
            <div @click="theme_style = 'zomato_gold'" 
                 :class="theme_style === 'zomato_gold' ? 'border-orange-500 ring-2 ring-orange-500/20 bg-orange-50/30' : 'border-gray-200 hover:border-gray-300 bg-white'" 
                 class="p-4 rounded-2xl border-2 cursor-pointer transition-all space-y-3 relative group">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">👑</span>
                        <div>
                            <h4 class="font-extrabold text-sm text-gray-900">Theme 1: Zomato Gold / Crown Premium</h4>
                            <p class="text-xs text-gray-500">Vibrant orange gradient, crown badges, elevated cards</p>
                        </div>
                    </div>
                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center" :class="theme_style === 'zomato_gold' ? 'border-orange-600 bg-orange-600 text-white' : 'border-gray-300'">
                        <template x-if="theme_style === 'zomato_gold'">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </template>
                    </div>
                </div>
                <div class="h-20 rounded-xl bg-gradient-to-r from-orange-500 to-amber-600 p-3 text-white flex flex-col justify-center text-center shadow-sm">
                    <span class="text-[10px] font-bold tracking-widest opacity-80" x-text="badge_text"></span>
                    <span class="text-xs font-black truncate" x-text="sub_header_motto"></span>
                </div>
            </div>

            <!-- Theme 2: Swiggy One / Modern Sleek Dark -->
            <div @click="theme_style = 'swiggy_one'" 
                 :class="theme_style === 'swiggy_one' ? 'border-emerald-500 ring-2 ring-emerald-500/20 bg-slate-900 text-white' : 'border-gray-200 hover:border-gray-300 bg-slate-900 text-white'" 
                 class="p-4 rounded-2xl border-2 cursor-pointer transition-all space-y-3 relative group">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">⚡</span>
                        <div>
                            <h4 class="font-extrabold text-sm text-white">Theme 2: Swiggy One / Modern Sleek Dark</h4>
                            <p class="text-xs text-slate-400">Dark glassmorphism, neon green glow, high-contrast UI</p>
                        </div>
                    </div>
                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center" :class="theme_style === 'swiggy_one' ? 'border-emerald-500 bg-emerald-500 text-slate-950' : 'border-slate-700'">
                        <template x-if="theme_style === 'swiggy_one'">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </template>
                    </div>
                </div>
                <div class="h-20 rounded-xl bg-slate-950 border border-emerald-500/30 p-3 text-emerald-400 flex flex-col justify-center text-center shadow-sm">
                    <span class="text-[10px] font-mono font-bold tracking-widest text-emerald-400" x-text="badge_text"></span>
                    <span class="text-xs font-black text-white truncate" x-text="sub_header_motto"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Buy Now Action Button Information Box -->
    <div class="card p-4 bg-amber-50/80 border border-amber-200 text-amber-950 space-y-1.5 text-xs rounded-2xl">
        <div class="flex items-center gap-2">
            <span class="text-base">💳</span>
            <h4 class="font-extrabold text-amber-950">Interactive Buy Now Button Integration</h4>
        </div>
        <p class="text-amber-900 text-[11px] leading-relaxed">
            In the Customer App, every active plan automatically displays an interactive <strong>"Buy Now - Subscribe VIP"</strong> button that opens the payment sheet (Wallet debit / Online Gateway). In custom HTML code, insert <code class="font-mono bg-white px-1.5 py-0.5 rounded border border-amber-300 text-amber-900 font-bold">&#123;&#123;buy_now_button&#125;&#125;</code> or <code class="font-mono bg-white px-1.5 py-0.5 rounded border border-amber-300 text-amber-900 font-bold">&#123;&#123;buy_now_button:PLAN_ID&#125;&#125;</code> to place an action button anywhere!
        </p>
    </div>

    <!-- Workspace: Text & Color Customizer (Left) & Live Preview (Right) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left: Text & Colors Form -->
        <div class="lg:col-span-6 space-y-4">
            <div class="card p-5 space-y-4">
                <div class="border-b pb-3">
                    <h3 class="font-extrabold text-sm text-gray-900 flex items-center gap-2">
                        <span>✏️ Custom Page Texts &amp; Section Content</span>
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">All text changes update dynamically inside the Customer App</p>
                </div>

                <div class="space-y-3 text-xs">
                    <!-- Hero Section Inputs -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label font-bold mb-1">Badge Text (Top Pill)</label>
                            <input type="text" x-model="badge_text" class="input text-xs font-semibold" placeholder="VIP MEMBERSHIP">
                        </div>
                        <div>
                            <label class="label font-bold mb-1">Sub-Header Motto</label>
                            <input type="text" x-model="sub_header_motto" class="input text-xs font-semibold" placeholder="More Perks. More Moments.">
                        </div>
                    </div>

                    <div>
                        <label class="label font-bold mb-1">Hero Title Heading</label>
                        <textarea x-model="hero_title" rows="2" class="input text-xs font-semibold" placeholder="Upgrade your everyday experience..."></textarea>
                    </div>

                    <div>
                        <label class="label font-bold mb-1">Sub Heading Tagline</label>
                        <input type="text" x-model="sub_heading" class="input text-xs font-semibold" placeholder="Premium benefits. Effortless savings. One membership.">
                    </div>

                    <!-- Colors -->
                    <div class="grid grid-cols-2 gap-3 pt-1">
                        <div>
                            <label class="label font-bold mb-1">Primary Theme Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" x-model="primary_color" class="w-8 h-8 rounded-lg border cursor-pointer">
                                <input type="text" x-model="primary_color" class="input text-xs font-mono">
                            </div>
                        </div>

                        <div>
                            <label class="label font-bold mb-1">Secondary Theme Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" x-model="secondary_color" class="w-8 h-8 rounded-lg border cursor-pointer">
                                <input type="text" x-model="secondary_color" class="input text-xs font-mono">
                            </div>
                        </div>
                    </div>

                    <!-- VIP Advantage Section -->
                    <div class="border-t pt-3 space-y-2">
                        <h4 class="font-extrabold text-xs text-gray-900 uppercase tracking-wider">🌟 VIP Advantage Section</h4>
                        <div>
                            <label class="label font-bold mb-1">Section Title</label>
                            <input type="text" x-model="vip_advantage_title" class="input text-xs font-bold" placeholder="Your VIP advantage">
                        </div>
                        <div>
                            <label class="label font-bold mb-1">Section Subtitle</label>
                            <textarea x-model="vip_advantage_subtitle" rows="2" class="input text-xs" placeholder="Everything you love, with more value..."></textarea>
                        </div>
                    </div>

                    <!-- Why Go VIP Section -->
                    <div class="border-t pt-3 space-y-2">
                        <h4 class="font-extrabold text-xs text-gray-900 uppercase tracking-wider">💡 Why Go VIP Section</h4>
                        <div>
                            <label class="label font-bold mb-1">Why VIP Title</label>
                            <input type="text" x-model="why_vip_title" class="input text-xs font-bold" placeholder="Why Go VIP?">
                        </div>
                        <div>
                            <label class="label font-bold mb-1">Why VIP Subtitle</label>
                            <textarea x-model="why_vip_subtitle" rows="2" class="input text-xs" placeholder="Because ordinary is overrated..."></textarea>
                        </div>
                    </div>

                    <!-- Upgrade & Footer Section -->
                    <div class="border-t pt-3 space-y-2">
                        <h4 class="font-extrabold text-xs text-gray-900 uppercase tracking-wider">🚀 Upgrade &amp; Footer Tagline</h4>
                        <div>
                            <label class="label font-bold mb-1">Upgrade Title</label>
                            <input type="text" x-model="upgrade_title" class="input text-xs font-bold" placeholder="Your upgrade starts here">
                        </div>
                        <div>
                            <label class="label font-bold mb-1">Upgrade Subtitle</label>
                            <textarea x-model="upgrade_subtitle" rows="2" class="input text-xs" placeholder="Ready to live a little more VIP?..."></textarea>
                        </div>
                        <div>
                            <label class="label font-bold mb-1">Footer Tagline</label>
                            <input type="text" x-model="footer_tagline" class="input text-xs font-semibold" placeholder="VIP Membership · Premium experiences, everyday value.">
                        </div>
                    </div>
                </div>

                <!-- Perks Section Customizer -->
                <div class="border-t pt-4 space-y-3">
                    <h4 class="font-extrabold text-xs text-gray-900 uppercase tracking-wider">🎁 Editable 4 Exclusive Perks Grid</h4>
                    
                    <template x-for="(perk, index) in perks" :key="index">
                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 space-y-2 text-xs">
                            <div class="flex items-center gap-2">
                                <input type="text" x-model="perk.icon" class="w-10 text-center input text-sm" placeholder="🚚">
                                <input type="text" x-model="perk.title" class="input text-xs font-bold" placeholder="Perk Title">
                            </div>
                            <input type="text" x-model="perk.desc" class="input text-xs text-gray-600" placeholder="Perk Description">
                        </div>
                    </template>
                </div>

                <!-- Custom HTML Code (Accordion) -->
                <div x-data="{ openHtml: false }" class="border-t pt-3 space-y-2">
                    <button type="button" @click="openHtml = !openHtml" class="w-full flex items-center justify-between text-xs font-bold text-gray-700 hover:text-gray-900 py-1">
                        <span>💻 Optional Raw HTML/CSS Editor (Power Users)</span>
                        <span x-text="openHtml ? '▲' : '▼'"></span>
                    </button>
                    <div x-show="openHtml" class="space-y-2 pt-2">
                        <textarea x-model="full_html_css" rows="10" class="input font-mono text-xs leading-relaxed bg-gray-900 text-green-400 p-3 rounded-xl border border-gray-800" placeholder="<style>...</style> <div>...</div>"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Live Canvas Preview -->
        <div class="lg:col-span-6 flex justify-center">
            <div class="bg-gray-100 rounded-3xl p-4 shadow-xl border border-gray-200 w-full max-w-[375px]">
                
                <div class="rounded-2xl overflow-hidden shadow-inner min-h-[620px] border p-4 space-y-4 transition-all duration-300"
                     :class="theme_style === 'swiggy_one' ? 'bg-slate-950 text-white border-slate-800' : 'bg-gray-50 text-gray-900 border-gray-200'">
                    
                    <div class="border-b pb-2 flex items-center justify-between" :class="theme_style === 'swiggy_one' ? 'border-slate-800' : 'border-gray-200'">
                        <span class="text-[10px] font-bold uppercase tracking-widest" :class="theme_style === 'swiggy_one' ? 'text-emerald-400' : 'text-orange-600'">
                            Live Customer App Preview (<span x-text="theme_style === 'swiggy_one' ? 'Theme 2: Swiggy One' : 'Theme 1: Zomato Gold'"></span>)
                        </span>
                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                    </div>

                    <!-- Theme 1 Live Preview (Zomato Gold) -->
                    <template x-if="theme_style === 'zomato_gold'">
                        <div class="space-y-4 text-center">
                            <div class="rounded-2xl p-6 text-white text-center shadow-lg space-y-2" :style="`background: linear-gradient(135deg, ${primary_color} 0%, ${secondary_color} 100%);`">
                                <span class="inline-block px-3 py-1 bg-white/20 rounded-full text-[10px] font-extrabold uppercase tracking-wider" x-text="badge_text"></span>
                                <div class="text-xs font-bold text-amber-100" x-text="sub_header_motto"></div>
                                <h2 class="text-sm font-black leading-snug" x-text="hero_title"></h2>
                            </div>

                            <div class="p-3 bg-orange-100/60 text-orange-950 rounded-xl text-xs font-extrabold" x-text="sub_heading"></div>

                            <div class="space-y-2 text-left">
                                <h4 class="text-sm font-black text-gray-900" x-text="vip_advantage_title"></h4>
                                <p class="text-[11px] text-gray-500" x-text="vip_advantage_subtitle"></p>
                                <div class="grid grid-cols-2 gap-2 pt-1">
                                    <template x-for="p in perks" :key="p.title">
                                        <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-2xs space-y-1">
                                            <span class="text-xl" x-text="p.icon"></span>
                                            <div class="text-xs font-bold text-gray-900" x-text="p.title"></div>
                                            <div class="text-[10px] text-gray-500 leading-tight" x-text="p.desc"></div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="bg-white p-4 rounded-xl border space-y-1 text-left">
                                <h4 class="text-xs font-extrabold text-gray-900" x-text="why_vip_title"></h4>
                                <p class="text-[11px] text-gray-600" x-text="why_vip_subtitle"></p>
                            </div>

                            <!-- Buy Now Action Button Preview -->
                            <div class="p-3 bg-orange-50 rounded-xl border border-orange-200 text-center space-y-2">
                                <button type="button" class="w-full py-3 bg-orange-600 text-white font-black text-xs rounded-xl shadow-md">
                                    Buy Now - Subscribe VIP (₹299.00)
                                </button>
                                <span class="text-[9px] text-orange-700 font-semibold block">💳 Interactive Wallet &amp; Gateway Payment Sheet</span>
                            </div>

                            <div class="bg-orange-50 p-4 rounded-xl space-y-1 text-center">
                                <h4 class="text-xs font-extrabold text-orange-900" x-text="upgrade_title"></h4>
                                <p class="text-[11px] text-orange-700" x-text="upgrade_subtitle"></p>
                                <p class="text-[9px] text-gray-400 pt-2" x-text="footer_tagline"></p>
                            </div>
                        </div>
                    </template>

                    <!-- Theme 2 Live Preview (Swiggy One Dark) -->
                    <template x-if="theme_style === 'swiggy_one'">
                        <div class="space-y-4 text-center">
                            <div class="rounded-2xl p-6 text-white text-center shadow-xl space-y-2 border border-emerald-500/30" :style="`background: linear-gradient(135deg, #0F172A 0%, ${primary_color}22 100%);`">
                                <span class="inline-block px-3 py-1 bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 rounded-full text-[10px] font-mono font-bold uppercase tracking-wider" x-text="badge_text"></span>
                                <div class="text-xs font-bold text-emerald-400" x-text="sub_header_motto"></div>
                                <h2 class="text-sm font-black text-white leading-snug" x-text="hero_title"></h2>
                            </div>

                            <div class="p-3 bg-slate-900 border border-emerald-500/40 text-emerald-300 rounded-xl text-xs font-extrabold" x-text="sub_heading"></div>

                            <div class="space-y-2 text-left">
                                <h4 class="text-sm font-black text-emerald-400 font-mono" x-text="vip_advantage_title"></h4>
                                <p class="text-[11px] text-slate-400" x-text="vip_advantage_subtitle"></p>
                                <div class="grid grid-cols-2 gap-2 pt-1">
                                    <template x-for="p in perks" :key="p.title">
                                        <div class="bg-slate-900 p-3 rounded-xl border border-slate-800 shadow-lg space-y-1">
                                            <span class="text-xl" x-text="p.icon"></span>
                                            <div class="text-xs font-bold text-white" x-text="p.title"></div>
                                            <div class="text-[10px] text-slate-400 leading-tight" x-text="p.desc"></div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="bg-slate-900 p-4 rounded-xl border border-slate-800 space-y-1 text-left">
                                <h4 class="text-xs font-extrabold text-white" x-text="why_vip_title"></h4>
                                <p class="text-[11px] text-slate-400" x-text="why_vip_subtitle"></p>
                            </div>

                            <!-- Buy Now Action Button Preview -->
                            <div class="p-3 bg-slate-900 rounded-xl border border-emerald-500/30 text-center space-y-2">
                                <button type="button" class="w-full py-3 bg-emerald-500 text-slate-950 font-black text-xs rounded-xl shadow-md">
                                    Buy Now - Subscribe VIP (₹299.00)
                                </button>
                                <span class="text-[9px] text-emerald-400 font-semibold block">💳 Interactive Wallet &amp; Gateway Payment Sheet</span>
                            </div>

                            <div class="bg-slate-900 p-4 rounded-xl border border-emerald-500/30 space-y-1 text-center">
                                <h4 class="text-xs font-extrabold text-emerald-400" x-text="upgrade_title"></h4>
                                <p class="text-[11px] text-slate-300" x-text="upgrade_subtitle"></p>
                                <p class="text-[9px] text-slate-500 pt-2" x-text="footer_tagline"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function vipPageBuilder() {
    return {
        theme_style: @json($themeStyle),
        badge_text: @json($badgeText),
        sub_header_motto: @json($subHeaderMotto),
        hero_title: @json($heroTitle),
        primary_color: @json($primaryColor),
        secondary_color: @json($secondaryColor),
        sub_heading: @json($subHeading),
        vip_advantage_title: @json($vipAdvantageTitle),
        vip_advantage_subtitle: @json($vipAdvantageSubtitle),
        perks: @json($perksList),
        why_vip_title: @json($whyVipTitle),
        why_vip_subtitle: @json($whyVipSubtitle),
        highlights: @json($highlights),
        upgrade_title: @json($upgradeTitle),
        upgrade_subtitle: @json($upgradeSubtitle),
        footer_tagline: @json($footerTagline),
        full_html_css: @json($fullHtmlCss),
        savePage() {
            fetch("{{ route('admin.subscriptions.user-plans.builder.save') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    sections_data: {
                        theme_style: this.theme_style,
                        badge_text: this.badge_text,
                        sub_header_motto: this.sub_header_motto,
                        hero_title: this.hero_title,
                        primary_color: this.primary_color,
                        secondary_color: this.secondary_color,
                        sub_heading: this.sub_heading,
                        vip_advantage_title: this.vip_advantage_title,
                        vip_advantage_subtitle: this.vip_advantage_subtitle,
                        perks: this.perks,
                        why_vip_title: this.why_vip_title,
                        why_vip_subtitle: this.why_vip_subtitle,
                        highlights: this.highlights,
                        upgrade_title: this.upgrade_title,
                        upgrade_subtitle: this.upgrade_subtitle,
                        footer_tagline: this.footer_tagline,
                        full_html_css: this.full_html_css
                    }
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert("VIP Membership Page & Theme published successfully!");
                } else {
                    alert("Error saving page: " + data.message);
                }
            })
            .catch(err => alert("Network error saving page: " + err));
        }
    }
}
</script>
@endpush
@endsection
