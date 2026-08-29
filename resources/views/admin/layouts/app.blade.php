    <!DOCTYPE html>
    <html lang="{{ session('locale', 'en') }}" dir="{{ session('is_rtl', false) ? 'rtl' : 'ltr' }}" class="h-full">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        {{-- Branding values ($adminAppName, $adminFavicon, $adminSidebarLogo) are
            injected by App\View\Composers\AdminLayoutComposer. --}}
        <title>@yield('title', 'Dashboard') - {{ $adminAppName }} Admin</title>
        @if($adminFavicon)
        <link rel="icon" type="image/png" href="{{ storage_url($adminFavicon) }}">
        @else
        <link rel="icon" type="image/png" href="{{ asset('assets/admin/img/favicon.png') }}">
        @endif
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <script src="https://unpkg.com/feather-icons"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: { sans: ['Inter', 'sans-serif'] },
                        colors: {
                            sidebar: { 
                                bg: '#1a2332',
                                hover: '#243044',
                                active: '#2d3a4f'
                            },
                            accent: '#f97316',
                            primary: { 
                                50: '#fff7ed', 100: '#ffedd5', 200: '#fed7aa', 
                                300: '#fdba74', 400: '#fb923c', 500: '#f97316', 
                                600: '#ea580c', 700: '#c2410c', 800: '#9a3412', 900: '#7c2d12' 
                            }
                        }
                    }
                }
            }
        </script>
        <style>
            [x-cloak] { display: none !important; }
            body { font-family: 'Inter', sans-serif; }
            
            /* Scrollbar */
            ::-webkit-scrollbar { width: 5px; height: 5px; }
            ::-webkit-scrollbar-track { background: transparent; }
            ::-webkit-scrollbar-thumb { background: #374151; border-radius: 3px; }
            
            /* Sidebar Styles */
            .sidebar-section-title {
                font-size: 11px;
                font-weight: 600;
                color: #64748b;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                padding: 16px 20px 8px 20px;
                margin-top: 8px;
            }
            
            .sidebar-item {
                display: flex;
                align-items: center;
                padding: 10px 20px;
                color: #94a3b8;
                font-size: 13px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.15s ease;
                position: relative;
                margin: 2px 8px;
                border-radius: 6px;
            }
            
            .sidebar-item:hover {
                background: #243044;
                color: #e2e8f0;
            }
            
            .sidebar-item.active {
                background: linear-gradient(90deg, rgba(249, 115, 22, 0.15) 0%, transparent 100%);
                color: #f97316;
            }
            
            .sidebar-item.active::before {
                content: '';
                position: absolute;
                left: -8px;
                top: 50%;
                transform: translateY(-50%);
                width: 3px;
                height: 24px;
                background: #f97316;
                border-radius: 0 3px 3px 0;
            }
            
            .sidebar-item svg {
                width: 18px;
                height: 18px;
                margin-right: 12px;
                flex-shrink: 0;
            }
            
            .sidebar-item .chevron {
                margin-left: auto;
                width: 16px;
                height: 16px;
                margin-right: 0;
                transition: transform 0.2s ease;
            }
            
            .sidebar-item .chevron.open {
                transform: rotate(180deg);
            }
            
            .sidebar-submenu {
                padding-left: 50px;
            }
            
            .sidebar-submenu a {
                display: block;
                padding: 8px 20px;
                color: #64748b;
                font-size: 13px;
                transition: color 0.15s ease;
            }
            
            .sidebar-submenu a:hover,
            .sidebar-submenu a.active {
                color: #f97316;
            }
            
            /* Card & Table */
            .card { 
                background: rgba(255, 255, 255, 0.9); 
                backdrop-filter: blur(12px);
                border-radius: 20px; 
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.04), 0 4px 6px -2px rgba(0, 0, 0, 0.02); 
                border: 1px solid rgba(255, 255, 255, 0.5); 
                transition: all 0.3s ease;
            }
            .card-header { padding: 2px 28px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; }
            .card-title { font-size: 17px; font-weight: 800; color: #0f172a; letter-spacing: -0.02em; }
            .card-body { padding: 28px; }
            
            .btn-primary { 
                display: inline-flex; align-items: center; gap: 8px;
                padding: 10px 24px; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: white;
                font-size: 14px; font-weight: 700; border-radius: 14px;
                transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 4px 12px rgba(249, 115, 22, 0.25);
                border: none;
            }
            .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(249, 115, 22, 0.35); filter: brightness(1.05); }
            .btn-secondary { 
                display: inline-flex; align-items: center; gap: 8px;
                padding: 10px 24px; background: white; color: #334155;
                font-size: 14px; font-weight: 600; border-radius: 14px;
                border: 1px solid #e2e8f0; transition: all 0.2s ease;
                box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            }
            .btn-secondary:hover { background: #f8fafc; border-color: #cbd5e1; transform: translateY(-1px); box-shadow: 0 4px 8px rgba(0,0,0,0.05); }
            .btn-outline {
                display: inline-flex; align-items: center; gap: 6px;
                padding: 9px 18px; background: white; color: #f97316;
                font-size: 13px; font-weight: 500; border-radius: 8px;
                border: 1px solid #f97316; transition: all 0.15s ease;
            }
            .btn-outline:hover { background: #fff7ed; }
            .btn-sm { padding: 6px 12px; font-size: 12px; }
            
            .input {
                width: 100%; padding: 10px 14px; font-size: 13px;
                border: 1px solid #e2e8f0; border-radius: 8px; background: #fff;
                transition: all 0.15s ease;
            }
            .input:focus {
                outline: none; border-color: #f97316;
                box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
            }
            .input::placeholder { color: #94a3b8; }
            select.input { cursor: pointer; }
            textarea.input { resize: vertical; min-height: 80px; }
            
            .label { display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px; }
            .form-group { margin-bottom: 16px; }
            .form-hint { font-size: 12px; color: #64748b; margin-top: 4px; }
            
            .table-header { 
                padding: 14px 20px; text-align: left;
                font-size: 11px; font-weight: 600; color: #64748b;
                text-transform: uppercase; letter-spacing: 0.05em;
                background: #f8fafc;
            }
            .table-cell { padding: 14px 20px; font-size: 13px; color: #334155; vertical-align: middle; }
            .table-row { border-bottom: 1px solid #f1f5f9; transition: background 0.1s ease; }
            .table-row:hover { background: #fafbfc; }
            .table-row:last-child { border-bottom: none; }
            
            .badge { 
                display: inline-flex; align-items: center; gap: 4px;
                padding: 4px 10px; border-radius: 6px;
                font-size: 12px; font-weight: 500;
            }
            .badge-orange { background: #fff7ed; color: #c2410c; }
            .badge-green { background: #ecfdf5; color: #059669; }
            .badge-blue { background: #eff6ff; color: #2563eb; }
            
            /* Toggle Switch */
            .toggle { position: relative; display: inline-flex; align-items: center; cursor: pointer; }
            .toggle input { position: absolute; opacity: 0; width: 0; height: 0; }
            .toggle-slider {
                width: 40px; height: 22px; background: #e2e8f0; border-radius: 11px;
                transition: all 0.2s ease; position: relative;
            }
            .toggle-slider::before {
                content: ''; position: absolute; width: 18px; height: 18px;
                background: white; border-radius: 50%; top: 2px; left: 2px;
                transition: all 0.2s ease; box-shadow: 0 1px 3px rgba(0,0,0,0.2);
            }
            .toggle input:checked + .toggle-slider { background: #22c55e; }
            .toggle input:checked + .toggle-slider::before { transform: translateX(18px); }
            .toggle.loading .toggle-slider { opacity: 0.5; }
            .badge-red { background: #fef2f2; color: #dc2626; }
            .badge-gray { background: #f1f5f9; color: #475569; }
            .badge-purple { background: #faf5ff; color: #7c3aed; }
            
            .action-btn {
                width: 34px; height: 34px; display: inline-flex;
                align-items: center; justify-content: center;
                border-radius: 8px; transition: all 0.15s ease;
            }
            .action-btn-view { color: #64748b; background: #f8fafc; }
            .action-btn-view:hover { background: #e2e8f0; color: #334155; }
            .action-btn-edit { color: #f97316; background: #fff7ed; }
            .action-btn-edit:hover { background: #ffedd5; }
            .action-btn-delete { color: #ef4444; background: #fef2f2; }
            .action-btn-delete:hover { background: #fee2e2; }
            
            .stat-card { 
                padding: 26px; border-radius: 24px; 
                background: rgba(255, 255, 255, 0.95); 
                border: 1px solid rgba(255, 255, 255, 1); 
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .stat-card:hover { transform: translateY(-6px); box-shadow: 0 30px 40px -10px rgba(0, 0, 0, 0.12); }
            .stat-icon { width: 56px; height: 56px; border-radius: 18px; display: flex; align-items: center; justify-content: center; }
            .stat-value { font-size: 34px; font-weight: 900; color: #0f172a; line-height: 1; letter-spacing: -0.04em; }
            .stat-label { font-size: 14px; font-weight: 600; color: #64748b; margin-top: 8px; text-transform: uppercase; letter-spacing: 0.025em; }
            .stat-change { font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 20px; }
            .stat-change.up { color: #059669; bg: #ecfdf5; }
            .stat-change.down { color: #dc2626; bg: #fef2f2; }
            
            .page-header { margin-bottom: 24px; }
            .page-title { font-size: 20px; font-weight: 600; color: #1e293b; }
            .page-subtitle { font-size: 13px; color: #64748b; margin-top: 2px; }
            
            .empty-state { padding: 60px 20px; text-align: center; }
            .empty-icon { width: 64px; height: 64px; background: #f1f5f9; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; }
            .empty-title { font-size: 15px; font-weight: 600; color: #1e293b; margin-bottom: 4px; }
            .empty-text { font-size: 13px; color: #64748b; margin-bottom: 20px; }
            
            .checkbox { width: 18px; height: 18px; border-radius: 4px; border: 2px solid #cbd5e1; cursor: pointer; }
            .checkbox:checked { background: #f97316; border-color: #f97316; }
            
            .avatar { width: 40px; height: 40px; border-radius: 10px; object-fit: cover; background: #f1f5f9; display: flex; align-items: center; justify-content: center; }
            .avatar-sm { width: 32px; height: 32px; border-radius: 8px; }
            .avatar-lg { width: 48px; height: 48px; border-radius: 12px; }
            
            .upload-zone { border: 2px dashed #e2e8f0; border-radius: 12px; padding: 32px; text-align: center; cursor: pointer; transition: all 0.2s ease; }
            .upload-zone:hover { border-color: #f97316; background: #fffbf7; }
            
            @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
            .animate-fade-in { animation: fadeIn 0.3s ease-out forwards; }
            .delay-1 { animation-delay: 0.05s; }
            .delay-2 { animation-delay: 0.1s; }
            .delay-3 { animation-delay: 0.15s; }
            .delay-4 { animation-delay: 0.2s; }

            /* ── Collapsed Sidebar ── */
            @media (min-width: 1024px) {
                .sidebar-collapsed .sidebar-section-title { display: none; }
                .sidebar-collapsed .sidebar-item span:not(.sr-only) { display: none; }
                .sidebar-collapsed .sidebar-item .chevron { display: none; }
                .sidebar-collapsed .sidebar-item {
                    justify-content: center;
                    padding: 10px 6px;
                    margin: 2px 6px;
                    position: relative;
                }
                .sidebar-collapsed .sidebar-item svg { margin-right: 0; }
                .sidebar-collapsed .sidebar-submenu { display: none !important; }
                .sidebar-collapsed .sidebar-logo-area { justify-content: center; padding: 12px 4px; }
                /* Tooltip on hover */
                .sidebar-collapsed .sidebar-item[data-label]:hover::after {
                    content: attr(data-label);
                    position: absolute;
                    left: calc(100% + 10px);
                    top: 50%;
                    transform: translateY(-50%);
                    background: #0f172a;
                    color: #f1f5f9;
                    padding: 5px 10px;
                    border-radius: 7px;
                    font-size: 12px;
                    font-weight: 500;
                    white-space: nowrap;
                    z-index: 9999;
                    box-shadow: 0 4px 16px rgba(0,0,0,0.25);
                    pointer-events: none;
                }
            }
        </style>
        @stack('styles')
    </head>
    <body class="h-full bg-[#f1f5f9]">
        <div x-data="{
            sidebarOpen: false,
            sidebarCollapsed: localStorage.getItem('sb_col') === '1',
            toggleSidebar() {
                this.sidebarCollapsed = !this.sidebarCollapsed;
                localStorage.setItem('sb_col', this.sidebarCollapsed ? '1' : '0');
            }
        }" class="min-h-full flex">
            
            <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/50 z-40 lg:hidden" @click="sidebarOpen = false"></div>

            <aside :class="[sidebarOpen ? 'translate-x-0' : '-translate-x-full', sidebarCollapsed ? 'lg:w-16 sidebar-collapsed' : 'lg:w-[220px]']" class="w-[220px] fixed inset-y-0 left-0 z-50 bg-sidebar-bg transform transition-all duration-300 ease-out lg:translate-x-0 lg:sticky lg:top-0 lg:h-screen flex flex-col">
                
                <div class="sidebar-logo-area flex items-center gap-2 px-4 py-3 border-b border-white/5">
                    @if($adminSidebarLogo)
                        <div x-show="!sidebarCollapsed" class="flex items-center justify-between flex-1 min-w-0 pr-1">
                            <img src="{{ storage_url($adminSidebarLogo) }}" alt="{{ $adminAppName }}" class="h-8 object-contain transition-all duration-300 max-w-[130px]">
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-orange-500/20 text-orange-400 border border-orange-500/30">v{{ env('SOFTWARE_VERSION', '1.0.0') }}</span>
                        </div>
                        <img x-show="sidebarCollapsed" src="{{ storage_url($adminSidebarLogo) }}" alt="{{ $adminAppName }}" class="h-8 object-contain flex-shrink-0 transition-all duration-300 max-w-[28px]">
                    @else
                        <div class="w-8 h-8 bg-gradient-to-br from-orange-400 to-orange-600 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div x-show="!sidebarCollapsed" class="flex flex-col flex-1 min-w-0">
                            <span class="text-sm font-bold text-white truncate leading-tight">{{ $adminAppName }}</span>
                            <span class="text-[10px] font-semibold text-orange-400 leading-none mt-0.5">v{{ env('SOFTWARE_VERSION', '1.0.0') }}</span>
                        </div>
                    @endif
                    <button @click="sidebarOpen = false" x-show="!sidebarCollapsed" class="lg:hidden text-slate-500 hover:text-white flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <button @click="toggleSidebar()" class="hidden lg:flex items-center justify-center w-7 h-7 rounded-md text-slate-500 hover:text-slate-200 hover:bg-white/10 transition-all flex-shrink-0" :class="sidebarCollapsed ? 'ml-0' : 'ml-auto'" title="Toggle sidebar">
                        <svg :class="sidebarCollapsed ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7"/>
                        </svg>
                    </button>
                </div>

                @php
                    $openMenu = match(true) {
                        request()->routeIs('admin.products.*') => 'products',
                        request()->routeIs('admin.categories.*') => 'category',
                        request()->routeIs('admin.attributes.*') => 'attributes',
                        request()->routeIs('admin.pages.*') => 'pages',
                        request()->routeIs('admin.stores.*') => 'stores',
                        request()->routeIs('admin.sellers.*') => 'sellers',
                        request()->routeIs('admin.delivery-partners.*') => 'delivery',
                        request()->routeIs('admin.brands.*') => 'brands',
                        request()->routeIs('admin.coupons.*') => 'coupons',
                        request()->routeIs('admin.users.*') => 'users',
                        request()->routeIs('admin.wallets.*') || request()->routeIs('admin.settings.wallet') => 'wallets',
                        request()->routeIs('admin.support.*') => 'support',
                        request()->routeIs('admin.website-content.*') || request()->routeIs('admin.website-settings.*') => 'website',
                        default => '',
                    };
                @endphp
                <nav id="adminSidebarNav" class="flex-1 py-2 overflow-y-auto" x-data="{ openMenu: '{{ $openMenu }}' }">
                    
                    {{-- ── Dashboard & Billing ── --}}
                    <div class="sidebar-section-title">Dashboard &amp; Billing</div>

                    @if(auth()->user()->hasPermission('dashboard.view'))
                    <a href="{{ route('admin.dashboard') }}" data-label="Dashboard" class="sidebar-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span>Dashboard</span>
                    </a>
                    @endif

                    @if(auth()->user()->hasPermission('orders.view'))
                    <a href="{{ route('admin.dispatch.index') }}" class="sidebar-item {{ request()->routeIs('admin.dispatch.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>Dispatch Center</span>
                    </a>

                    <a href="{{ route('admin.orders.index') }}" class="sidebar-item {{ request()->routeIs('admin.orders.*') && !request()->routeIs('admin.dispatch.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <span>Orders</span>
                    </a>
                    
                    <a href="{{ route('admin.invoices.index') }}" class="sidebar-item {{ request()->routeIs('admin.invoices.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Invoices</span>
                    </a>

                    <a href="{{ route('admin.pos.index') }}" class="sidebar-item {{ request()->routeIs('admin.pos.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <span>POS Terminal</span>
                    </a>

                    <a href="{{ route('admin.prescriptions.index') }}" class="sidebar-item {{ request()->routeIs('admin.prescriptions.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        <span>Prescriptions Workflow</span>
                    </a>

                    <a href="{{ route('admin.returns.index') }}" class="sidebar-item {{ request()->routeIs('admin.returns.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Product Returns</span>
                    </a>

                    <a href="{{ route('admin.complaints.index') }}" class="sidebar-item {{ request()->routeIs('admin.complaints.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        <span>Customer Complaints</span>
                    </a>

                    <a href="{{ route('admin.kyc.index') }}" class="sidebar-item {{ request()->routeIs('admin.kyc.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 012-2h2a2 2 0 012 2v1m-6 0h6m-3 6a2 2 0 100-4 2 2 0 000 4zm-5 6a3 3 0 016 0H7z"/></svg>
                        <span>eKYC Form Builder</span>
                    </a>

                    <a href="{{ route('admin.reports.index') }}" class="sidebar-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 19v-6a2 2 0 012-2h2a2 2 0 012 2v6a2 2 0 01-2 2h-2a2 2 0 01-2-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span>Analytical Reports</span>
                    </a>

                    <a href="{{ route('admin.incentives.index') }}" class="sidebar-item {{ request()->routeIs('admin.incentives.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Rider Incentives & Tips</span>
                    </a>

                    <a href="{{ route('admin.reviews.index') }}" class="sidebar-item {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        <span>Ratings & Reviews</span>
                    </a>

                    <a href="{{ route('admin.languages.index') }}" class="sidebar-item {{ request()->routeIs('admin.languages.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/></svg>
                        <span>Multi-Language & RTL</span>
                    </a>

                    @if(Route::has('admin.subscriptions.store-plans.index'))
                    <a href="{{ route('admin.subscriptions.store-plans.index') }}" class="sidebar-item {{ request()->routeIs('admin.subscriptions.store-plans.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span>Store Subscription Plans</span>
                    </a>
                    @endif

                    @if(Route::has('admin.subscriptions.user-plans.index'))
                    <a href="{{ route('admin.subscriptions.user-plans.index') }}" class="sidebar-item {{ request()->routeIs('admin.subscriptions.user-plans.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                        <span>VIP User Memberships</span>
                    </a>
                    @endif

                    <a href="{{ route('admin.support-chat.index') }}" class="sidebar-item {{ request()->routeIs('admin.support-chat.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <span>Live Support Chat</span>
                    </a>
                    @endif

                    {{-- ── Product Setup ── --}}
                    <div class="sidebar-section-title">Product Setup</div>

                    @if(auth()->user()->hasPermission('products.view'))
                    <div>
                        <div @click="openMenu = openMenu === 'products' ? '' : 'products'" class="sidebar-item {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <span>Products</span>
                            <svg :class="openMenu === 'products' ? 'open' : ''" class="chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        <div x-show="openMenu === 'products'" x-collapse class="sidebar-submenu">
                            <a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.index') && !request()->routeIs('admin.products.seller') ? 'active' : '' }}">In-house Products</a>
                            <a href="{{ route('admin.products.seller') }}" class="{{ request()->routeIs('admin.products.seller') ? 'active' : '' }}">Seller Products</a>
                            <a href="{{ route('admin.food-addons.index') }}" class="{{ request()->routeIs('admin.food-addons.*') ? 'active' : '' }}">Food Add-ons</a>
                        </div>
                    </div>
                    @endif

                    @if(auth()->user()->hasPermission('categories.view'))
                    <div>
                        <div @click="openMenu = openMenu === 'category' ? '' : 'category'" class="sidebar-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            <span>Categories</span>
                            <svg :class="openMenu === 'category' ? 'open' : ''" class="chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        <div x-show="openMenu === 'category'" x-collapse class="sidebar-submenu">
                            <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.index') ? 'active' : '' }}">All Categories</a>
                            @if(auth()->user()->hasPermission('categories.create'))
                            <a href="{{ route('admin.categories.create') }}" class="{{ request()->routeIs('admin.categories.create') ? 'active' : '' }}">Add Category</a>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if(auth()->user()->hasPermission('brands.view'))
                    <div>
                        <div @click="openMenu = openMenu === 'brands' ? '' : 'brands'" class="sidebar-item {{ request()->routeIs('admin.brands.*') ? 'active' : '' }}">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                            <span>Brands</span>
                            <svg :class="openMenu === 'brands' ? 'open' : ''" class="chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        <div x-show="openMenu === 'brands'" x-collapse class="sidebar-submenu">
                            <a href="{{ route('admin.brands.index') }}" class="{{ request()->routeIs('admin.brands.index') ? 'active' : '' }}">All Brands</a>
                            <a href="{{ route('admin.brands.create') }}" class="{{ request()->routeIs('admin.brands.create') ? 'active' : '' }}">Add Brand</a>
                        </div>
                    </div>
                    @endif

                    @if(auth()->user()->hasPermission('attributes.view'))
                    <div>
                        <div @click="openMenu = openMenu === 'attributes' ? '' : 'attributes'" class="sidebar-item {{ request()->routeIs('admin.attributes.*') ? 'active' : '' }}">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                            <span>Attributes</span>
                            <svg :class="openMenu === 'attributes' ? 'open' : ''" class="chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        <div x-show="openMenu === 'attributes'" x-collapse class="sidebar-submenu">
                            <a href="{{ route('admin.attributes.index') }}" class="{{ request()->routeIs('admin.attributes.index') ? 'active' : '' }}">All Attributes</a>
                            <a href="{{ route('admin.attributes.create') }}" class="{{ request()->routeIs('admin.attributes.create') ? 'active' : '' }}">Add Attribute</a>
                        </div>
                    </div>
                    @endif

                    @if(auth()->user()->hasPermission('reviews.view'))
                    <a href="{{ route('admin.reviews.index') }}" class="sidebar-item {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        <span>Reviews</span>
                    </a>
                    @endif


                    {{-- ── App Design & Setup ── --}}
                    <div class="sidebar-section-title">App Design & Setup</div>

                    @if(auth()->user()->hasPermission('mobile_app.view'))
                    <a href="{{ url('admin/splash-screen/builder') }}" class="sidebar-item {{ request()->is('admin/splash-screen/builder*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                        <span>Splash Builder</span>
                    </a>

                    <a href="{{ route('admin.settings.onboarding') }}" class="sidebar-item {{ request()->routeIs('admin.settings.onboarding') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <span>Onboarding Screens</span>
                    </a>
                    
                    <a href="{{ route('admin.home-header.index') }}" class="sidebar-item {{ request()->routeIs('admin.home-header.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                        <span>App Modules</span>
                    </a>
                    
                    <a href="{{ route('admin.app-content.index') }}" class="sidebar-item {{ request()->routeIs('admin.app-content.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>App Design</span>
                    </a>
                    
                    @if(\App\Models\Plugin::where(function($q) { $q->where('name', 'website')->orWhere('name', 'quixko-website')->orWhere('name', 'website-content'); })->where('is_active', true)->exists())
                    <div>
                        <div @click="openMenu = openMenu === 'website' ? '' : 'website'" class="sidebar-item {{ (request()->routeIs('admin.website-content.*') || request()->routeIs('admin.website-settings.*')) ? 'active' : '' }}">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                            <span>Website Builder</span>
                            <span class="ml-1.5 px-1.5 py-0.5 text-[10px] font-extrabold text-white bg-red-600 rounded shadow-xs uppercase tracking-wider leading-none">Plugin</span>
                            <svg :class="openMenu === 'website' ? 'open' : ''" class="chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        <div x-show="openMenu === 'website'" x-collapse class="sidebar-submenu">
                            @if(\Illuminate\Support\Facades\Route::has('admin.website-content.index'))
                            <a href="{{ route('admin.website-content.index') }}" class="{{ request()->routeIs('admin.website-content.*') ? 'active' : '' }}">Website Builder</a>
                            @endif
                            @if(\Illuminate\Support\Facades\Route::has('admin.website-settings.index'))
                            <a href="{{ route('admin.website-settings.index') }}" class="{{ request()->routeIs('admin.website-settings.*') ? 'active' : '' }}">Website Setting</a>
                            @endif
                        </div>
                    </div>
                    @endif
                    
                    <a href="{{ route('admin.category-screen-content.index') }}" class="sidebar-item {{ request()->routeIs('admin.category-screen-content.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span>Category Screen</span>
                    </a>
                    
                    <a href="{{ route('admin.settings.ai') }}" class="sidebar-item {{ request()->routeIs('admin.settings.ai') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>AI Settings</span>
                    </a>
                    
                    <a href="{{ route('admin.settings.mobile-app') }}" class="sidebar-item {{ request()->routeIs('admin.settings.mobile-app') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <span>App Settings</span>
                    </a>
                    @endif


                    {{-- ── Offers & Taxes ── --}}
                    <div class="sidebar-section-title">Offers & Taxes</div>

                    @if(auth()->user()->hasPermission('coupons.view'))
                    <div>
                        <div @click="openMenu = openMenu === 'coupons' ? '' : 'coupons'" class="sidebar-item {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                            <span>Coupons</span>
                            <svg :class="openMenu === 'coupons' ? 'open' : ''" class="chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        <div x-show="openMenu === 'coupons'" x-collapse class="sidebar-submenu">
                            <a href="{{ route('admin.coupons.index') }}" class="{{ request()->routeIs('admin.coupons.index') ? 'active' : '' }}">All Coupons</a>
                            <a href="{{ route('admin.coupons.create') }}" class="{{ request()->routeIs('admin.coupons.create') ? 'active' : '' }}">Add Coupon</a>
                        </div>
                    </div>
                    @endif

                    @if(auth()->user()->hasPermission('settings.view'))
                    <a href="{{ route('admin.taxes.index') }}" class="sidebar-item {{ request()->routeIs('admin.taxes.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>Tax Rules</span>
                    </a>
                    
                    <a href="{{ route('admin.commissions.index') }}" class="sidebar-item {{ request()->routeIs('admin.commissions.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Admin Commission</span>
                    </a>
                    
                    <a href="{{ route('admin.subscriptions.index') }}" class="sidebar-item {{ request()->routeIs('admin.subscriptions.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        <span>Store Subscriptions</span>
                    </a>

                    <a href="{{ route('admin.ad-plans.index') }}" class="sidebar-item {{ request()->routeIs('admin.ad-plans.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                        <span>Paid Seller Ads</span>
                    </a>
                    @endif


                    {{-- ── Zone Management ── --}}
                    <div class="sidebar-section-title">Zone Management</div>
                    
                    @if(auth()->user()->hasPermission('stores.view'))
                    <a href="{{ route('admin.zones.index') }}" class="sidebar-item {{ request()->routeIs('admin.zones.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Zones</span>
                    </a>
                    @endif


                    {{-- ── Customers & Sellers ── --}}
                    <div class="sidebar-section-title">Customers & Sellers</div>

                    @if(auth()->user()->hasPermission('users.view'))
                    <div>
                        <div @click="openMenu = openMenu === 'users' ? '' : 'users'" class="sidebar-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <span>Customers</span>
                            <svg :class="openMenu === 'users' ? 'open' : ''" class="chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        <div x-show="openMenu === 'users'" x-collapse class="sidebar-submenu">
                            <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.index') ? 'active' : '' }}">All Customers</a>
                            @if(auth()->user()->hasPermission('users.create'))
                            <a href="{{ route('admin.users.create') }}" class="{{ request()->routeIs('admin.users.create') ? 'active' : '' }}">Add Customer</a>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if(auth()->user()->hasPermission('sellers.view'))
                    <div>
                        <div @click="openMenu = openMenu === 'sellers' ? '' : 'sellers'" class="sidebar-item {{ request()->routeIs('admin.sellers.*') ? 'active' : '' }}">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <span>Sellers</span>
                            <svg :class="openMenu === 'sellers' ? 'open' : ''" class="chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        <div x-show="openMenu === 'sellers'" x-collapse class="sidebar-submenu">
                            <a href="{{ route('admin.sellers.index') }}" class="{{ request()->routeIs('admin.sellers.index') ? 'active' : '' }}">All Sellers</a>
                        </div>
                    </div>
                    @endif

                    @if(auth()->user()->hasPermission('stores.view'))
                    <div>
                        <div @click="openMenu = openMenu === 'stores' ? '' : 'stores'" class="sidebar-item {{ request()->routeIs('admin.stores.*') ? 'active' : '' }}">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span>Stores</span>
                            <svg :class="openMenu === 'stores' ? 'open' : ''" class="chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        <div x-show="openMenu === 'stores'" x-collapse class="sidebar-submenu">
                            <a href="{{ route('admin.stores.index') }}" class="{{ request()->routeIs('admin.stores.index') ? 'active' : '' }}">All Stores</a>
                            <a href="{{ route('admin.stores.pending') }}" class="{{ request()->routeIs('admin.stores.pending') ? 'active' : '' }}">Pending Approvals</a>
                        </div>
                    </div>
                    @endif

                    @if(auth()->user()->hasPermission('delivery_partners.view'))
                    <div>
                        <div @click="openMenu = openMenu === 'delivery' ? '' : 'delivery'" class="sidebar-item {{ request()->routeIs('admin.delivery-partners.*') ? 'active' : '' }}">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                            <span>Delivery Partners</span>
                            <svg :class="openMenu === 'delivery' ? 'open' : ''" class="chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        <div x-show="openMenu === 'delivery'" x-collapse class="sidebar-submenu">
                            <a href="{{ route('admin.delivery-partners.index') }}" class="{{ request()->routeIs('admin.delivery-partners.index') && !request('status') ? 'active' : '' }}">All Partners</a>
                            <a href="{{ route('admin.delivery-partners.index', ['status' => 'pending']) }}" class="{{ request()->routeIs('admin.delivery-partners.index') && request('status') === 'pending' ? 'active' : '' }}">Pending Approvals</a>
                            @if(auth()->user()->hasPermission('delivery_partners.create'))
                            <a href="{{ route('admin.delivery-partners.create') }}" class="{{ request()->routeIs('admin.delivery-partners.create') ? 'active' : '' }}">Add Partner</a>
                            @endif
                        </div>
                    </div>
                    @endif


                    {{-- ── Notifications ── --}}
                    <div class="sidebar-section-title">Notifications</div>

                    @if(auth()->user()->hasPermission('mobile_app.view'))
                    <a href="{{ route('admin.push-notifications.index') }}" class="sidebar-item {{ request()->routeIs('admin.push-notifications.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span>Send Push Notification</span>
                    </a>
                    
                    <a href="{{ route('admin.settings.notification-templates') }}" class="sidebar-item {{ request()->routeIs('admin.settings.notification-templates') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
                        <span>Notification Templates</span>
                    </a>
                    @endif
                    
                    @if(auth()->user()->hasPermission('email_templates.view'))
                    <a href="{{ route('admin.email-templates.index') }}" class="sidebar-item {{ request()->routeIs('admin.email-templates.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>Email Templates</span>
                    </a>
                    @endif


                    {{-- ── Help & Support ── --}}
                    <div class="sidebar-section-title">Help &amp; Support</div>

                    @if(auth()->user()->hasPermission('dashboard.view'))
                    <a href="{{ route('admin.support.index') }}" data-label="Support Tickets" class="sidebar-item {{ request()->routeIs('admin.support.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <span>Support Tickets</span>
                    </a>
                    @endif


                    {{-- ── Wallet Management ── --}}
                    <div class="sidebar-section-title">Wallet Management</div>
                    <div>
                        <div @click="openMenu = openMenu === 'wallets' ? '' : 'wallets'" class="sidebar-item {{ request()->routeIs('admin.wallets.*') || request()->routeIs('admin.settings.wallet') || request()->routeIs('admin.loyalty.*') ? 'active' : '' }}">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            <span>Wallets</span>
                            <svg :class="openMenu === 'wallets' ? 'open' : ''" class="chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        <div x-show="openMenu === 'wallets'" x-collapse class="sidebar-submenu">
                            <a href="{{ route('admin.settings.wallet') }}" class="{{ request()->routeIs('admin.settings.wallet') ? 'active' : '' }}">Setting</a>
                            <a href="{{ route('admin.wallets.index') }}" class="{{ request()->routeIs('admin.wallets.index') || request()->routeIs('admin.wallets.show') ? 'active' : '' }}">All Wallets</a>
                            @if(auth()->user()->hasPermission('loyalty.view'))
                            <a href="{{ route('admin.loyalty.index') }}" class="{{ request()->routeIs('admin.loyalty.index') ? 'active' : '' }}">Cashback & Refer</a>
                            @endif
                            <a href="{{ route('admin.wallets.withdrawals') }}" class="{{ request()->routeIs('admin.wallets.withdrawals') ? 'active' : '' }}">Withdrawal Request</a>
                        </div>
                    </div>


                    {{-- ── Website Content ── --}}
                    <div class="sidebar-section-title">Website Content</div>

                    @if(auth()->user()->hasPermission('pages.view'))
                    <div>
                        <div @click="openMenu = openMenu === 'pages' ? '' : 'pages'" class="sidebar-item {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                            <span>Pages</span>
                            <svg :class="openMenu === 'pages' ? 'open' : ''" class="chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        <div x-show="openMenu === 'pages'" x-collapse class="sidebar-submenu">
                            <a href="{{ route('admin.pages.index') }}" class="{{ request()->routeIs('admin.pages.index') ? 'active' : '' }}">All Pages</a>
                            <a href="{{ route('admin.pages.create') }}" class="{{ request()->routeIs('admin.pages.create') ? 'active' : '' }}">Add Page</a>
                        </div>
                    </div>
                    @endif

                    <a href="{{ route('admin.popups.index') }}" class="sidebar-item {{ request()->routeIs('admin.popups.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                        <span>Popup Manager</span>
                    </a>
                    
                    @if(auth()->user()->hasPermission('media.view'))
                    <a href="{{ route('admin.media.index') }}" class="sidebar-item {{ request()->routeIs('admin.media.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Media</span>
                    </a>
                    @endif

                    @if(auth()->user()->hasPermission('landing_page.view'))
                    <a href="{{ route('admin.landing-page.index') }}" class="sidebar-item {{ request()->routeIs('admin.landing-page.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                        <span>Landing Page</span>
                    </a>
                    @endif

                


                    {{-- ── Admin Control ── --}}
                    <div class="sidebar-section-title">Admin Control</div>
                    
                    @if(auth()->user()->hasPermission('staff.view'))
                    <a href="{{ route('admin.staff.index') }}" class="sidebar-item {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span>Staff Management</span>
                    </a>
                    @endif

                    @if(auth()->user()->hasPermission('roles.view'))
                    <a href="{{ route('admin.roles.index') }}" class="sidebar-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>Roles & Permissions</span>
                    </a>
                    @endif

                    <a href="{{ route('admin.security.index') }}" class="sidebar-item {{ request()->routeIs('admin.security.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span>Security & Audit</span>
                    </a>


                    {{-- ── System Settings ── --}}
                    <div class="sidebar-section-title">System Settings</div>
                    
                    @if(auth()->user()->hasPermission('settings.auth'))
                    <a href="{{ route('admin.settings.auth') }}" class="sidebar-item {{ request()->routeIs('admin.settings.auth') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span>Login Setup</span>
                    </a>
                    @endif

                    @if(auth()->user()->hasPermission('settings.view'))
                    <a href="{{ route('admin.settings.app-settings') }}" class="sidebar-item {{ request()->routeIs('admin.settings.app-settings') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>Business Settings</span>
                    </a>
                    <a href="{{ route('admin.settings.index') }}" class="sidebar-item {{ request()->routeIs('admin.settings.index') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Settings</span>
                    </a>
                    @endif
                    
                    @if(auth()->user()->hasPermission('database_clean.view'))
                    <a href="{{ route('admin.database-clean.index') }}" class="sidebar-item {{ request()->routeIs('admin.database-clean.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                        <span>Database Clean</span>
                    </a>
                    @endif
                    
                    @if(auth()->user()->hasPermission('settings.view'))
                    <a href="{{ route('admin.scheduler-jobs.index') }}" class="sidebar-item {{ request()->routeIs('admin.scheduler-jobs.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Cron Jobs</span>
                    </a>
                    @endif
                    
                    @if(auth()->user()->hasPermission('settings.view'))
                    <a href="{{ route('admin.plugins.index') }}" class="sidebar-item {{ request()->routeIs('admin.plugins.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <span>Plugins</span>
                    </a>
                    @endif

                    {{-- Plugin Menu Hooks --}}
                    @php
                        $pluginMenus = app('plugin.hooks')->fire('admin.menu', []);
                    @endphp

                    @foreach($pluginMenus as $menu)
                        @if(isset($menu['section']))
                            <div class="sidebar-section-title">{{ $menu['section'] }}</div>
                        @endif

                        @php
                            // Determine if it's a nested menu (has children)
                            $hasChildren = isset($menu['children']) && count($menu['children']) > 0;
                            $menuId = 'plugin_menu_' . Str::slug($menu['title'] ?? 'item');
                            $isPluginOpen = false;
                            if (isset($menu['route']) && request()->routeIs(Str::beforeLast($menu['route'], '.') . '.*')) {
                                $isPluginOpen = true;
                            }
                            if ($hasChildren) {
                                foreach ($menu['children'] as $child) {
                                    if (isset($child['route']) && request()->routeIs($child['route'] . '*')) {
                                        $isPluginOpen = true;
                                        break;
                                    }
                                }
                            }
                        @endphp

                        @php
                            $rawMenuTitle = $menu['title'] ?? ($menu['label'] ?? 'Plugin Item');
                            $hasPluginTag = str_contains($rawMenuTitle, '(Plugin)') || str_contains($rawMenuTitle, 'Plugin');
                            $cleanMenuTitle = trim(str_replace(['(Plugin)', 'Plugin'], '', $rawMenuTitle));
                        @endphp

                        @if(!isset($menu['permission']) || auth()->user()->hasPermission($menu['permission']))
                            @if($hasChildren)
                            <div x-data="{ open: {{ $isPluginOpen ? 'true' : 'false' }} }">
                                <div @click="open = !open" 
                                    class="sidebar-item {{ (isset($menu['route']) && request()->routeIs($menu['route'] . '*')) ? 'active' : '' }}">
                                    @if(isset($menu['icon']))
                                        @if(str_contains($menu['icon'], '<svg'))
                                            {!! $menu['icon'] !!}
                                        @else
                                            <i class="feather-{{ $menu['icon'] }}"></i>
                                        @endif
                                    @else
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
                                    @endif
                                    <span>{{ $cleanMenuTitle ?: $rawMenuTitle }}</span>
                                    @if($hasPluginTag)
                                        <span class="ml-1.5 px-1.5 py-0.5 text-[10px] font-extrabold text-white bg-red-600 rounded shadow-xs uppercase tracking-wider leading-none">Plugin</span>
                                    @endif
                                    <svg :class="open ? 'open' : ''" class="chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                                <div x-show="open" x-collapse class="sidebar-submenu">
                                    @foreach($menu['children'] as $child)
                                        @if(!isset($child['permission']) || auth()->user()->hasPermission($child['permission']))
                                        <a href="{{ Route::has($child['route']) ? route($child['route']) : '#' }}" 
                                        class="{{ (isset($child['route']) && request()->routeIs($child['route'] . '*')) ? 'active' : '' }}">
                                            {{ $child['title'] ?? ($child['label'] ?? 'Sub Item') }}
                                        </a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            @else
                            <a href="{{ (isset($menu['route']) && Route::has($menu['route'])) ? route($menu['route']) : '#' }}" 
                            class="sidebar-item {{ (isset($menu['route']) && request()->routeIs($menu['route'] . '*')) ? 'active' : '' }}">
                                @if(isset($menu['icon']))
                                    @if(str_contains($menu['icon'], '<svg'))
                                        {!! $menu['icon'] !!}
                                    @else
                                        <i class="feather-{{ $menu['icon'] }}"></i>
                                    @endif
                                @else
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/></svg>
                                @endif
                                <span>{{ $cleanMenuTitle ?: $rawMenuTitle }}</span>
                                @if($hasPluginTag)
                                    <span class="ml-1.5 px-1.5 py-0.5 text-[10px] font-extrabold text-white bg-red-600 rounded shadow-xs uppercase tracking-wider leading-none">Plugin</span>
                                @endif
                            </a>
                            @endif
                        @endif
                    @endforeach
                </nav>
            </aside>

            <div class="flex-1 flex flex-col min-w-0">
                <header id="admin-topbar" class="bg-white border-b border-gray-200 sticky top-0 z-30">
                    <div class="h-0.5 bg-gradient-to-r from-orange-400 via-orange-500 to-amber-400"></div>
                    <div class="flex items-center gap-3 px-4 py-2.5">
                        {{-- Mobile menu toggle --}}
                        <button @click="sidebarOpen = true" class="lg:hidden p-2 -ml-1 text-gray-500 hover:text-gray-700 rounded-lg flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>

                        {{-- Live Today Stats --}}
                        <div x-data="topbarStats()" x-init="load(); setInterval(load, 60000)" class="flex-1 hidden sm:flex items-center gap-2">

                            {{-- Orders Today --}}
                            <a href="{{ route('admin.orders.index') }}"
                            class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 transition-colors group cursor-pointer">
                                <div class="w-7 h-7 rounded-lg bg-blue-500 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                </div>
                                <div class="leading-none">
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-base font-black text-blue-700 tabular-nums" x-text="loaded ? orders : '—'"></span>
                                        <template x-if="loaded && ordersDelta > 0">
                                            <span class="text-[10px] font-bold text-green-500">+<span x-text="ordersDelta"></span></span>
                                        </template>
                                    </div>
                                    <p class="text-[10px] font-medium text-blue-400 mt-0.5">Today's Orders</p>
                                </div>
                            </a>

                            {{-- Pending Orders --}}
                            <a href="{{ route('admin.orders.index') }}"
                            class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 transition-colors group cursor-pointer">
                                <div class="w-7 h-7 rounded-lg bg-amber-500 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div class="leading-none">
                                    <div class="text-base font-black text-amber-700 tabular-nums" x-text="loaded ? pending : '—'"></div>
                                    <p class="text-[10px] font-medium text-amber-400 mt-0.5">Pending</p>
                                </div>
                            </a>

                            {{-- New Customers Today --}}
                            <a href="{{ route('admin.users.index') }}"
                            class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-green-50 hover:bg-green-100 transition-colors group cursor-pointer hidden lg:flex">
                                <div class="w-7 h-7 rounded-lg bg-green-500 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                </div>
                                <div class="leading-none">
                                    <div class="text-base font-black text-green-700 tabular-nums" x-text="loaded ? newUsers : '—'"></div>
                                    <p class="text-[10px] font-medium text-green-400 mt-0.5">New Customers</p>
                                </div>
                            </a>

                            {{-- Loading skeleton --}}
                            <div x-show="!loaded" class="flex items-center gap-1.5 ml-1">
                                <div class="w-2 h-2 rounded-full bg-gray-300 animate-bounce" style="animation-delay:0s"></div>
                                <div class="w-2 h-2 rounded-full bg-gray-300 animate-bounce" style="animation-delay:.15s"></div>
                                <div class="w-2 h-2 rounded-full bg-gray-300 animate-bounce" style="animation-delay:.3s"></div>
                            </div>

                        
                        </div>

                        {{-- Spacer on mobile --}}
                        <div class="flex-1 sm:hidden"></div>

                    

                        {{-- Global Quick Search Button & Modal --}}
                        <div x-data="{ searchModalOpen: false }" class="relative flex-shrink-0">
                            <button @click="searchModalOpen = true" 
                                    title="Quick Search"
                                    class="flex items-center gap-1.5 px-2.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl transition-all text-xs font-semibold border border-gray-200/60">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <span class="hidden md:inline text-[11px]">Search</span>
                            </button>

                            <div x-show="searchModalOpen" x-cloak 
                                @keydown.escape.window="searchModalOpen = false"
                                class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-start justify-center pt-20 px-4">
                                <div @click.away="searchModalOpen = false" 
                                    class="bg-white w-full max-w-xl rounded-2xl shadow-2xl overflow-hidden border border-gray-100 animate-fade-in">
                                    <form action="{{ route('admin.dispatch.index') }}" method="GET" class="flex items-center px-4 py-3.5 border-b border-gray-100">
                                        <svg class="w-5 h-5 text-gray-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                        <input type="text" name="search" placeholder="Search orders, rides, bookings, customers..." 
                                            class="w-full text-sm outline-none text-gray-800 placeholder-gray-400" autofocus>
                                        <button type="submit" class="px-3.5 py-1.5 bg-orange-500 text-white rounded-xl text-xs font-bold hover:bg-orange-600 transition-colors">
                                            Search
                                        </button>
                                    </form>
                                    <div class="p-3 bg-gray-50 text-[11px] text-gray-400 flex justify-between">
                                        <span>Press Enter to search all active orders and rides</span>
                                        <span class="font-mono bg-gray-200 text-gray-600 px-1.5 py-0.5 rounded">ESC to exit</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Active Module Switcher Dropdown --}}
                        @php
                            $adminModules = \App\Models\HomeHeaderTab::orderBy('sort_order')->get();
                            $activeModuleId = session('admin_active_module');
                        @endphp
                        <div class="relative flex-shrink-0 hidden md:block">
                            <form id="admin-module-switch-form" action="{{ route('admin.switch-module') }}" method="POST" class="flex items-center">
                                @csrf
                                <div class="flex items-center gap-1.5 px-2.5 py-1.5 bg-gradient-to-r from-slate-900 to-indigo-950 text-white rounded-xl shadow-sm border border-slate-700/50">
                                    <svg class="w-3.5 h-3.5 text-orange-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                    <select name="module_id" onchange="document.getElementById('admin-module-switch-form').submit()"
                                            class="bg-transparent text-[11px] font-extrabold text-white outline-none cursor-pointer pr-1 border-none focus:ring-0">
                                        <option value="all" {{ !$activeModuleId ? 'selected' : '' }} class="bg-slate-900 text-white">
                                            All Modules (Global)
                                        </option>
                                        @foreach($adminModules as $mod)
                                            <option value="{{ $mod->id }}" {{ (string)$activeModuleId === (string)$mod->id ? 'selected' : '' }} class="bg-slate-900 text-white">
                                                {{ $mod->name ?? 'Module #' . $mod->id }} ({{ strtoupper($mod->module_type ?? 'grocery') }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </form>
                        </div>

                        {{-- Quick Actions --}}
                        <div x-data="{ qaOpen: false }" class="relative flex-shrink-0">
                            <button @click="qaOpen = !qaOpen" @click.away="qaOpen = false"
                                    class="flex items-center gap-1.5 px-3 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white text-sm font-semibold rounded-xl hover:from-orange-600 hover:to-orange-700 transition-all shadow-md shadow-orange-200 hover:shadow-orange-300 hover:-translate-y-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                <span class="hidden sm:inline">Add</span>
                            </button>
                            <div x-show="qaOpen"
                                x-cloak
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute right-0 mt-2 w-52 bg-white border border-gray-100 rounded-2xl shadow-2xl shadow-gray-200/60 overflow-hidden z-50">
                                <div class="p-1.5">
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-3 py-1.5">Create New</p>
                                    @if(auth()->user() && auth()->user()->hasPermission('products.create'))
                                    <a href="{{ route('admin.products.create') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-orange-50 group transition-colors">
                                        <span class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center group-hover:bg-blue-100 transition-colors">
                                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        </span>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-700 group-hover:text-orange-600">New Product</p>
                                            <p class="text-[10px] text-gray-400">Add to catalog</p>
                                        </div>
                                    </a>
                                    @endif
                                    @if(auth()->user() && auth()->user()->hasPermission('coupons.create'))
                                    <a href="{{ route('admin.coupons.create') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-orange-50 group transition-colors">
                                        <span class="w-8 h-8 bg-green-50 rounded-lg flex items-center justify-center group-hover:bg-green-100 transition-colors">
                                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                        </span>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-700 group-hover:text-orange-600">New Coupon</p>
                                            <p class="text-[10px] text-gray-400">Discount &amp; offers</p>
                                        </div>
                                    </a>
                                    @endif
                                    @if(auth()->user() && auth()->user()->hasPermission('users.create'))
                                    <a href="{{ route('admin.users.create') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-orange-50 group transition-colors">
                                        <span class="w-8 h-8 bg-purple-50 rounded-lg flex items-center justify-center group-hover:bg-purple-100 transition-colors">
                                            <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                        </span>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-700 group-hover:text-orange-600">New Customer</p>
                                            <p class="text-[10px] text-gray-400">Add customer account</p>
                                        </div>
                                    </a>
                                    @endif
                                    <a href="{{ route('admin.push-notifications.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-orange-50 group transition-colors">
                                        <span class="w-8 h-8 bg-orange-50 rounded-lg flex items-center justify-center group-hover:bg-orange-100 transition-colors">
                                            <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                        </span>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-700 group-hover:text-orange-600">Push Notification</p>
                                            <p class="text-[10px] text-gray-400">Broadcast to users</p>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- View Store --}}
                        <a href="{{ url('/') }}" target="_blank"
                        class="hidden md:flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-500 hover:text-orange-600 hover:bg-orange-50 border border-gray-200 rounded-xl transition-all flex-shrink-0 group">
                            <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            <span class="hidden lg:inline">Web</span>
                        </a>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.settings.clear-cache') }}" 
                            title="Clear Cache"
                            onclick="return confirm('Clear all server caches?')"
                            class="p-2 text-gray-500 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            </a>

                            @include('admin.partials.realtime-notifications')
                            
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" @click.away="open = false" class="flex items-center gap-2 hover:bg-gray-50 p-1.5 rounded-lg transition-colors border border-transparent hover:border-gray-200">
                                    @if(auth()->user() && auth()->user()->avatar)
                                        <img src="{{ storage_url(auth()->user()->avatar) }}" class="w-8 h-8 rounded-full object-cover">
                                    @elseif(auth()->user())
                                        <div class="w-8 h-8 bg-gradient-to-br from-orange-400 to-orange-600 rounded-full flex items-center justify-center text-white text-sm font-semibold uppercase">
                                            {{ substr(auth()->user()->name, 0, 1) }}
                                        </div>
                                    @else
                                        <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center text-gray-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        </div>
                                    @endif
                                    <div class="hidden sm:flex flex-col items-start leading-none gap-0.5">
                                        <span class="text-sm font-semibold text-gray-700">{{ auth()->user()->name ?? 'Guest' }}</span>
                                        <span class="text-[10px] text-gray-400 font-medium uppercase">{{ auth()->user() ? auth()->user()->role->label() : 'Unauthorized' }}</span>
                                    </div>
                                    <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                <div x-show="open" 
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95"
                                    class="absolute right-0 mt-2 w-48 bg-white border border-gray-100 rounded-xl shadow-xl shadow-gray-200/50 overflow-hidden z-50">
                                    
                                    <div class="px-4 py-3 bg-gray-50/50 border-b border-gray-100">
                                        <p class="text-xs text-gray-400 mb-0.5 font-medium">Signed in as</p>
                                        <p class="text-sm font-semibold text-gray-700 truncate text-xs">{{ auth()->user()->email ?? 'None' }}</p>
                                    </div>

                                    <div class="p-1.5">
                                        <a href="#" class="flex items-center gap-2 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-orange-600 rounded-lg transition-colors group">
                                            <svg class="w-4 h-4 text-gray-400 group-hover:text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            My Profile
                                        </a>
                                        <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-2 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-orange-600 rounded-lg transition-colors group">
                                            <svg class="w-4 h-4 text-gray-400 group-hover:text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            Settings
                                        </a>
                                    </div>

                                    <div class="p-1.5 border-t border-gray-100">
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors group">
                                                <svg class="w-4 h-4 text-red-400 group-hover:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                                Logout
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <main class="flex-1 p-3 sm:p-6">
                    @if(filter_var(env('DEMO_MODE', config('app.demo_mode', true)), FILTER_VALIDATE_BOOLEAN))
                    @php
                        $demoLastResetAt = \App\Models\Setting::get('demo_last_reset_at');
                        $demoLastResetTimestamp = $demoLastResetAt ? strtotime($demoLastResetAt) : 0;
                    @endphp
                    <!-- Demo Data Restored Notification Popup Modal -->
                    <div x-data="{ 
                            showResetPopup: false,
                            serverResetTime: {{ $demoLastResetTimestamp }},
                            init() {
                                // 1. Check on page load / refresh
                                if (this.serverResetTime > 0) {
                                    const lastSeenTime = parseInt(localStorage.getItem('demo_last_seen_reset_time') || '0');
                                    if (this.serverResetTime > lastSeenTime) {
                                        this.showResetPopup = true;
                                    }
                                }
                                // 2. Real-time background check every 15 seconds (shows popup immediately without refresh!)
                                setInterval(() => {
                                    fetch('{{ route('admin.demo.check-status') }}', {
                                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                                    })
                                    .then(res => res.json())
                                    .then(data => {
                                        if (data.success && data.last_reset_timestamp) {
                                            const latestTime = parseInt(data.last_reset_timestamp);
                                            const lastSeenTime = parseInt(localStorage.getItem('demo_last_seen_reset_time') || '0');
                                            if (latestTime > lastSeenTime && latestTime > this.serverResetTime) {
                                                this.serverResetTime = latestTime;
                                                this.showResetPopup = true;
                                            }
                                        }
                                    })
                                    .catch(e => {});
                                }, 15000);
                            },
                            dismissResetPopup() {
                                localStorage.setItem('demo_last_seen_reset_time', this.serverResetTime.toString());
                                this.showResetPopup = false;
                            }
                        }" 
                        x-show="showResetPopup" 
                        x-cloak
                        class="fixed inset-0 z-[999] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95">
                        
                        <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 max-w-md w-full p-6 text-center transform transition-all relative overflow-hidden"
                            @click.away="dismissResetPopup()">
                            
                            <div class="absolute -top-12 -right-12 w-32 h-32 bg-amber-100 rounded-full blur-2xl pointer-events-none opacity-60"></div>
                            <div class="absolute -bottom-12 -left-12 w-32 h-32 bg-orange-100 rounded-full blur-2xl pointer-events-none opacity-60"></div>

                            <div class="w-14 h-14 bg-gradient-to-tr from-amber-500 to-orange-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-orange-500/30 text-white animate-bounce">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            </div>

                            <h3 class="text-xl font-bold text-slate-900 mb-2">Demo Data Restored</h3>
                            <p class="text-xs sm:text-sm text-slate-600 mb-5 leading-relaxed">
                                The system database tables and uploaded media files were just <strong class="text-slate-900">automatically restored back to default demo state</strong>.
                            </p>

                            <div class="bg-amber-50 border border-amber-200/70 rounded-xl p-3 mb-6 text-left text-xs text-amber-900 flex items-center gap-3">
                                <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>All products, categories, and settings are back to pristine baseline.</span>
                            </div>

                            <button @click="dismissResetPopup()" 
                                    class="w-full py-3 px-5 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-semibold rounded-xl text-sm shadow-lg shadow-orange-500/25 transition-all transform active:scale-95">
                                Got it, Continue Testing!
                            </button>
                        </div>
                    </div>
                    @endif

                    @if(session('success'))
                    <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center gap-2 text-sm animate-fade-in">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ session('success') }}
                    </div>
                    @endif

                    @if($errors->any())
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm animate-fade-in">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    @yield('content')
                </main>
            </div>
        </div>

        {{-- 📦 Module Sidebar - Quick Access from everywhere --}}
        @include('admin.partials.module-sidebar')

        <div id="toast" class="fixed bottom-4 right-4 bg-gray-900 text-white px-4 py-2 rounded-lg shadow-lg transform translate-y-20 opacity-0 transition-all duration-300 z-50">
            <span id="toastMessage"></span>
        </div>

        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <script>
            document.addEventListener('alpine:init', () => {
                // Custom collapse directive that works with x-show
                Alpine.directive('collapse', (el) => {
                    // Get the x-show state from the element
                    el.style.overflow = 'hidden';
                    el.style.transition = 'max-height 0.2s ease, opacity 0.2s ease';
                    
                    // Use MutationObserver to watch for display changes from x-show
                    const observer = new MutationObserver(() => {
                        if (el.style.display !== 'none') {
                            el.style.maxHeight = el.scrollHeight + 'px';
                            el.style.opacity = '1';
                        } else {
                            el.style.maxHeight = '0px';
                            el.style.opacity = '0';
                        }
                    });
                    
                    observer.observe(el, { attributes: true, attributeFilter: ['style'] });
                    
                    // Initial state
                    if (el.style.display === 'none' || !el.offsetParent) {
                        el.style.maxHeight = '0px';
                        el.style.opacity = '0';
                    } else {
                        el.style.maxHeight = el.scrollHeight + 'px';
                        el.style.opacity = '1';
                    }
                });
            });
        </script>
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
        
        <script>
            function showToast(message) {
                const toast = document.getElementById('toast');
                const toastMessage = document.querySelector('#toast span');
                if (!toast || !toastMessage) return;
                
                toastMessage.textContent = message;
                toast.classList.remove('translate-y-20', 'opacity-0');
                setTimeout(() => {
                    toast.classList.add('translate-y-20', 'opacity-0');
                }, 3000);
            }

            document.addEventListener('DOMContentLoaded', function() {
                if (typeof feather !== 'undefined') {
                    feather.replace();
                }
            });
        </script>

        <script>
            // ── Topbar Live Stats ──
            function topbarStats() {
                return {
                    loaded: false,
                    orders: 0,
                    ordersDelta: 0,
                    pending: 0,
                    newUsers: 0,
                    async load() {
                        try {
                            const res = await fetch('{{ route("admin.topbar-stats") }}', {
                                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                            });
                            if (!res.ok) return;
                            const d = await res.json();
                            this.ordersDelta = this.loaded ? Math.max(0, d.orders_today - this.orders) : 0;
                            this.orders   = d.orders_today  ?? 0;
                            this.pending  = d.pending       ?? 0;
                            this.newUsers = d.new_customers  ?? 0;
                            this.loaded   = true;
                        } catch(e) { /* fail silently — stats are non-critical */ }
                    }
                }
            }

            // ── Live Clock ──
            (function updateClock() {
                const timeEl = document.getElementById('topbar-time');
                const dateEl = document.getElementById('topbar-date');
                if (!timeEl || !dateEl) return;
                const now = new Date();
                timeEl.textContent = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                const days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
                const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                dateEl.textContent = `${days[now.getDay()]}, ${months[now.getMonth()]} ${now.getDate()}`;
                setTimeout(updateClock, 1000);
            })();

            // ── Sidebar Scroll Preservation & Active Item Auto-Scroll ──
            document.addEventListener('DOMContentLoaded', function() {
                const sidebarNav = document.getElementById('adminSidebarNav');
                if (!sidebarNav) return;

                // 1. Restore scroll position from sessionStorage
                const savedScroll = sessionStorage.getItem('admin_sidebar_scroll');
                if (savedScroll !== null) {
                    sidebarNav.scrollTop = parseInt(savedScroll, 10);
                }

                // 2. Ensure active menu item is scrolled into view
                setTimeout(function() {
                    const activeItem = sidebarNav.querySelector('.sidebar-item.active, .sidebar-submenu a.active');
                    if (activeItem) {
                        const rect = activeItem.getBoundingClientRect();
                        const navRect = sidebarNav.getBoundingClientRect();
                        if (rect.top < navRect.top || rect.bottom > navRect.bottom || savedScroll === null) {
                            activeItem.scrollIntoView({ block: 'center', behavior: 'smooth' });
                        }
                    }
                }, 100);

                // 3. Save scroll position on scroll
                sidebarNav.addEventListener('scroll', function() {
                    sessionStorage.setItem('admin_sidebar_scroll', sidebarNav.scrollTop);
                }, { passive: true });

                // 4. Save scroll position on link click
                sidebarNav.querySelectorAll('a').forEach(function(link) {
                    link.addEventListener('click', function() {
                        sessionStorage.setItem('admin_sidebar_scroll', sidebarNav.scrollTop);
                    });
                });
            });
        </script>

        {{-- Real-time New Order Sound Alert & Popup Modal Component --}}
        <div x-data="orderNotificationSystem()" x-init="init()" class="fixed top-5 right-5 z-[99999] max-w-md w-full space-y-3 pointer-events-none" x-cloak>
            <template x-for="item in activePopups" :key="item.type + '_' + item.id">
                <div 
                    x-show="true" 
                    x-transition:enter="transition ease-out duration-300 transform"
                    x-transition:enter-start="opacity-0 translate-y-[-20px] scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-200 transform"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 translate-y-[-20px] scale-95"
                    class="pointer-events-auto bg-white/95 backdrop-blur-md rounded-2xl p-4 shadow-2xl border-2 border-orange-500/40 flex items-start gap-4 ring-1 ring-black/5 relative overflow-hidden group"
                >
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-gradient-to-b from-orange-500 to-amber-500"></div>

                    <div class="w-12 h-12 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center text-2xl flex-shrink-0 shadow-inner" x-text="item.icon || '🛍️'"></div>

                    <div class="flex-1 min-w-0 pr-6">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black bg-orange-500 text-white uppercase tracking-wider animate-pulse">NEW ORDER</span>
                            <span class="text-xs text-gray-400 font-medium" x-text="item.time"></span>
                        </div>
                        <h4 class="font-bold text-gray-900 text-sm mt-1" x-text="item.title">New Order Received!</h4>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="font-extrabold text-gray-800 text-sm" x-text="item.number"></span>
                            <span class="text-xs font-black text-orange-600 px-2 py-0.5 bg-orange-50 rounded-lg" x-text="item.amount"></span>
                        </div>
                        
                        <div class="flex items-center gap-2 mt-3">
                            <button @click="handleViewOrder(item)" class="px-3.5 py-1.5 bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold rounded-lg shadow-sm transition-all duration-200 inline-flex items-center gap-1">
                                <span>View Order</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                            <button @click="dismiss(item)" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-lg transition-all duration-200">
                                Dismiss / Ignore
                            </button>
                        </div>
                    </div>

                    <button @click="dismiss(item)" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition-colors" title="Close and Mute">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </template>
        </div>

        <script>
            function orderNotificationSystem() {
                return {
                    activePopups: [],
                    lastOrderId: parseInt(sessionStorage.getItem('last_known_order_id') || 0, 10),
                    lastRideId: parseInt(sessionStorage.getItem('last_known_ride_id') || 0, 10),
                    lastBookingId: parseInt(sessionStorage.getItem('last_known_booking_id') || 0, 10),
                    soundEnabled: true,
                    isInitialized: false,
                    soundLoopInterval: null,
                    soundTimeoutTimer: null,
                    audioContext: null,

                    init() {
                        this.pollNewOrders();
                        setInterval(() => this.pollNewOrders(), 10000);
                    },

                    playOrderChime() {
                        if (!this.soundEnabled) return;
                        this.stopSound();

                        const playTones = () => {
                            try {
                                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                                if (!AudioCtx) return;
                                if (!this.audioContext || this.audioContext.state === 'closed') {
                                    this.audioContext = new AudioCtx();
                                }
                                const ctx = this.audioContext;
                                if (ctx.state === 'suspended') {
                                    ctx.resume();
                                }

                                // Tone 1: 587.33Hz (D5)
                                const osc1 = ctx.createOscillator();
                                const gain1 = ctx.createGain();
                                osc1.type = 'sine';
                                osc1.frequency.setValueAtTime(587.33, ctx.currentTime);
                                gain1.gain.setValueAtTime(0.3, ctx.currentTime);
                                gain1.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);
                                osc1.connect(gain1);
                                gain1.connect(ctx.destination);
                                osc1.start(ctx.currentTime);
                                osc1.stop(ctx.currentTime + 0.35);

                                // Tone 2: 880Hz (A5)
                                const osc2 = ctx.createOscillator();
                                const gain2 = ctx.createGain();
                                osc2.type = 'sine';
                                osc2.frequency.setValueAtTime(880, ctx.currentTime + 0.15);
                                gain2.gain.setValueAtTime(0.4, ctx.currentTime + 0.15);
                                gain2.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.65);
                                osc2.connect(gain2);
                                gain2.connect(ctx.destination);
                                osc2.start(ctx.currentTime + 0.15);
                                osc2.stop(ctx.currentTime + 0.65);
                            } catch(e) {
                                console.warn('Audio playback error or browser policy block', e);
                            }
                        };

                        // Play chime immediately
                        playTones();

                        // Repeat chime every 1.5 seconds for up to 30 seconds
                        this.soundLoopInterval = setInterval(playTones, 1500);

                        // Automatically stop sound after 30 seconds
                        this.soundTimeoutTimer = setTimeout(() => {
                            this.stopSound();
                        }, 30000);
                    },

                    stopSound() {
                        if (this.soundLoopInterval) {
                            clearInterval(this.soundLoopInterval);
                            this.soundLoopInterval = null;
                        }
                        if (this.soundTimeoutTimer) {
                            clearTimeout(this.soundTimeoutTimer);
                            this.soundTimeoutTimer = null;
                        }
                        if (this.audioContext && this.audioContext.state !== 'closed') {
                            try {
                                this.audioContext.close();
                            } catch(e) {}
                            this.audioContext = null;
                        }
                    },

                    async pollNewOrders() {
                        try {
                            const query = `?last_order_id=${this.lastOrderId}&last_ride_id=${this.lastRideId}&last_booking_id=${this.lastBookingId}`;
                            const res = await fetch('{{ route("admin.orders.check-new") }}' + query, {
                                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                            });
                            
                            if (!res.ok) return;
                            const data = await res.json();

                            // On first run, save initial state so old existing orders don't sound alert on page load
                            if (!this.isInitialized) {
                                this.lastOrderId = data.latest_order_id || 0;
                                this.lastRideId = data.latest_ride_id || 0;
                                this.lastBookingId = data.latest_booking_id || 0;
                                sessionStorage.setItem('last_known_order_id', this.lastOrderId);
                                sessionStorage.setItem('last_known_ride_id', this.lastRideId);
                                sessionStorage.setItem('last_known_booking_id', this.lastBookingId);
                                this.isInitialized = true;
                                return;
                            }

                            // If new orders/rides/bookings detected
                            if (data.has_new && data.notifications.length > 0) {
                                // Ring chime audio continuously for up to 30 seconds
                                this.playOrderChime();

                                data.notifications.forEach(item => {
                                    this.activePopups.push(item);
                                    // Auto dismiss popup after 30 seconds
                                    setTimeout(() => {
                                        this.dismiss(item);
                                    }, 30000);
                                });

                                if (data.latest_order_id > this.lastOrderId) {
                                    this.lastOrderId = data.latest_order_id;
                                    sessionStorage.setItem('last_known_order_id', this.lastOrderId);
                                }
                                if (data.latest_ride_id > this.lastRideId) {
                                    this.lastRideId = data.latest_ride_id;
                                    sessionStorage.setItem('last_known_ride_id', this.lastRideId);
                                }
                                if (data.latest_booking_id > this.lastBookingId) {
                                    this.lastBookingId = data.latest_booking_id;
                                    sessionStorage.setItem('last_known_booking_id', this.lastBookingId);
                                }
                            }
                        } catch(e) {
                            // Fail silently
                        }
                    },

                    handleViewOrder(item) {
                        this.stopSound();
                        this.dismiss(item);
                        window.location.href = '{{ route("admin.dispatch.index") }}';
                    },

                    dismiss(item) {
                        this.stopSound();
                        this.activePopups = this.activePopups.filter(p => !(p.type === item.type && p.id === item.id));
                    }
                };
            }
        </script>

        <!-- SOS Emergency Alert System Component -->
        <div x-data="sosAlertSystem()" x-init="initSos()" class="relative z-50">
            <!-- Emergency SOS Modal -->
            <template x-if="hasActiveSos && activeSosModal">
                <div class="fixed inset-0 bg-red-950/80 backdrop-blur-md flex items-center justify-center p-4 z-[9999] animate-fade-in">
                    <div class="card max-w-lg w-full p-6 bg-white border-2 border-red-600 shadow-2xl space-y-5 animate-bounce-short">
                        <div class="flex items-center justify-between border-b border-red-100 pb-3">
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-10 bg-red-100 text-red-600 rounded-full flex items-center justify-center font-extrabold text-xl animate-ping">🚨</span>
                                <div>
                                    <h3 class="text-base font-extrabold text-red-600 uppercase tracking-wide">EMERGENCY SOS ALERT</h3>
                                    <p class="text-xs text-gray-500" x-text="activeSosModal.triggered_by + ' triggered an emergency distress signal'"></p>
                                </div>
                            </div>
                            <button @click="stopSiren()" class="text-gray-400 hover:text-gray-600 font-bold text-sm">✕</button>
                        </div>

                        <div class="space-y-3 text-xs bg-red-50 p-4 rounded-xl border border-red-200">
                            <div class="flex justify-between py-1 border-b border-red-100">
                                <span class="font-semibold text-gray-700">Ride Number:</span>
                                <span class="font-mono font-bold text-gray-900" x-text="'#' + activeSosModal.ride_number"></span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-red-100">
                                <span class="font-semibold text-gray-700">Rider / Person:</span>
                                <span class="font-bold text-gray-900" x-text="activeSosModal.user_name"></span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-red-100">
                                <span class="font-semibold text-gray-700">Phone Number:</span>
                                <a :href="'tel:' + activeSosModal.user_phone" class="font-bold text-blue-600 hover:underline" x-text="activeSosModal.user_phone"></a>
                            </div>
                            <div class="flex justify-between py-1 border-b border-red-100">
                                <span class="font-semibold text-gray-700">Assigned Driver:</span>
                                <span class="font-bold text-gray-900" x-text="activeSosModal.driver_name"></span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-red-100">
                                <span class="font-semibold text-gray-700">Pickup Address:</span>
                                <span class="font-medium text-gray-800 truncate max-w-[200px]" x-text="activeSosModal.pickup_address"></span>
                            </div>
                            <div class="flex justify-between py-1">
                                <span class="font-semibold text-gray-700">GPS Coordinates:</span>
                                <span class="font-mono font-bold text-red-600" x-text="activeSosModal.latitude + ', ' + activeSosModal.longitude"></span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="space-y-2 pt-1">
                            <a :href="'https://www.google.com/maps/search/?api=1&query=' + activeSosModal.latitude + ',' + activeSosModal.longitude" target="_blank" class="btn-primary w-full py-2.5 flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700">
                                📍 Open Live Location on Google Maps
                            </a>
                            <div class="grid grid-cols-2 gap-2">
                                <button @click="stopSiren()" class="btn-secondary text-xs py-2 text-gray-700">
                                    Mute Siren Sound
                                </button>
                                <a href="{{ \Illuminate\Support\Facades\Route::has('admin.ride-sharing.sos.index') ? route('admin.ride-sharing.sos.index') : '#' }}" class="btn-secondary text-xs py-2 text-center text-red-700 border-red-200">
                                    Control Center →
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <script>
            function sosAlertSystem() {
                return {
                    hasActiveSos: false,
                    activeSosModal: null,
                    sirenInterval: null,
                    audioCtx: null,

                    initSos() {
                        window.sosAlertSystem = this;
                        this.pollSosAlerts();
                        setInterval(() => this.pollSosAlerts(), 4000);
                    },

                    async pollSosAlerts() {
                        const checkUrl = '{{ \Illuminate\Support\Facades\Route::has("admin.ride-sharing.sos.check") ? route("admin.ride-sharing.sos.check") : "" }}';
                        if (!checkUrl) return;
                        try {
                            const res = await fetch(checkUrl, {
                                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                            });
                            if (!res.ok) return;
                            const data = await res.json();
                            
                            if (data.has_sos && data.alerts.length > 0) {
                                this.hasActiveSos = true;
                                this.activeSosModal = data.alerts[0];
                                this.startEmergencySiren();
                            } else {
                                if (this.hasActiveSos) {
                                    this.stopSiren();
                                }
                                this.hasActiveSos = false;
                                this.activeSosModal = null;
                            }
                        } catch(e) {}
                    },

                    startEmergencySiren() {
                        if (this.sirenInterval) return; // already sounding
                        try {
                            let high = true;
                            this.playSirenTone(880);
                            this.sirenInterval = setInterval(() => {
                                this.playSirenTone(high ? 660 : 880);
                                high = !high;
                            }, 300);
                        } catch(e) {}
                    },

                    playSirenTone(freq) {
                        try {
                            if (!this.audioCtx) {
                                this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                            }
                            if (this.audioCtx.state === 'suspended') {
                                this.audioCtx.resume();
                            }
                            const osc = this.audioCtx.createOscillator();
                            const gain = this.audioCtx.createGain();
                            osc.type = 'sawtooth';
                            osc.frequency.setValueAtTime(freq, this.audioCtx.currentTime);
                            gain.gain.setValueAtTime(0.3, this.audioCtx.currentTime);
                            gain.gain.exponentialRampToValueAtTime(0.01, this.audioCtx.currentTime + 0.28);
                            osc.connect(gain);
                            gain.connect(this.audioCtx.destination);
                            osc.start();
                            osc.stop(this.audioCtx.currentTime + 0.28);
                        } catch(e) {}
                    },

                    stopSiren() {
                        if (this.sirenInterval) {
                            clearInterval(this.sirenInterval);
                            this.sirenInterval = null;
                        }
                        if (this.audioCtx) {
                            try { this.audioCtx.close(); } catch(e) {}
                            this.audioCtx = null;
                        }
                    }
                };
            }

            (function initAdminClock() {
                function updateTopbarClock() {
                    const timeEl = document.getElementById('topbar-time');
                    const dateEl = document.getElementById('topbar-date');
                    if (!timeEl || !dateEl) return;
                    const now = new Date();
                    timeEl.textContent = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
                    dateEl.textContent = now.toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric' });
                }
                updateTopbarClock();
                setInterval(updateTopbarClock, 1000);
            })();
        </script>
        @stack('scripts')
    </body>
    </html>
