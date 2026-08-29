<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Seller Dashboard') - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        seller: { 
                            bg: '#1e1b4b', // deep indigo
                            hover: '#312e81',
                            active: '#3730a3'
                        },
                        accent: '#818cf8',
                        primary: { 
                            50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 
                            300: '#a5b4fc', 400: '#818cf8', 500: '#6366f1', 
                            600: '#4f46e5', 700: '#4338ca', 800: '#3730a3', 900: '#312e81' 
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
        ::-webkit-scrollbar-thumb { background: #4338ca; border-radius: 3px; }
        
        /* Modern Professional Sidebar (Inspired by Hyperlocal) */
        .sidebar {
            background-color: #0f172a; /* Deep Slate/Navy */
            border-right: none;
        }

        .sidebar-item {
            display: flex;
            align-items: center;
            padding: 14px 24px;
            color: #94a3b8; /* Muted slate */
            font-size: 15px;
            font-weight: 500;
            text-transform: none;
            letter-spacing: -0.01em;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            margin: 0;
        }
        
        .sidebar-item:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.05);
        }
        
        .sidebar-item.active {
            color: #fff;
            background: rgba(255, 255, 255, 0.05);
        }

        .sidebar-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: #4f46e5; /* Brand Indigo */
        }
        
        .sidebar-item svg {
            width: 22px;
            height: 22px;
            margin-right: 16px;
            flex-shrink: 0;
            opacity: 0.7;
            transition: opacity 0.2s ease;
        }

        .sidebar-item:hover svg, .sidebar-item.active svg {
            opacity: 1;
        }

        .sidebar-section-title {
            padding: 24px 24px 8px;
            font-size: 11px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .card { 
            background: white; 
            border-radius: 20px; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); 
            border: 1px solid #f1f5f9; 
        }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade-in { animation: fadeIn 0.4s ease-out forwards; }
    </style>
    @stack('styles')
</head>
<body class="h-full bg-[#f8fafc]">
    <div x-data="{ sidebarOpen: false }" class="min-h-full flex">
        
        <!-- Mobile backdrop -->
        <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-indigo-900/60 backdrop-blur-sm z-40 lg:hidden" @click="sidebarOpen = false"></div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-[270px] sidebar transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:sticky lg:top-0 lg:h-screen flex flex-col shadow-2xl">
            
            <!-- Logo Section -->
            <div class="p-8 border-b border-white/5">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-indigo-600 rounded flex items-center justify-center shadow-lg shadow-indigo-500/20">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-white tracking-tight leading-none">{{ config('app.name') }}</h2>
                        <p class="text-[10px] text-slate-400 font-medium uppercase tracking-widest mt-1.5">Seller Dashboard</p>
                    </div>
                </div>
            </div>

            <!-- Store Switcher -->
            @php
                $user = auth()->user();
                $ownedStores = $user->ownedStores;
                $staffStores = \App\Models\Store::whereIn('id', $user->staffStores()->pluck('store_id'))->get();
                $allAccessibleStores = $ownedStores->merge($staffStores)->unique('id');
                $currentStore = $store ?? $user->getCurrentStore();
            @endphp
            
            <div class="px-6 mb-4" x-data="{ open: false }">
                <div class="relative">
                    <button 
                        @if($allAccessibleStores->count() > 1) @click="open = !open" @endif
                        class="w-full flex items-center justify-between gap-3 px-4 py-3 bg-indigo-900/40 border {{ $allAccessibleStores->count() > 1 ? 'border-indigo-500/30' : 'border-indigo-500/10' }} rounded-2xl {{ $allAccessibleStores->count() > 1 ? 'hover:bg-indigo-900/60 transition-all cursor-pointer' : 'cursor-default' }} text-left">
                        <div class="flex items-center gap-3 overflow-hidden">
                            <div class="w-7 h-7 bg-indigo-500 rounded-lg flex-shrink-0 flex items-center justify-center text-[10px] font-bold text-white uppercase">
                                {{ substr($currentStore->name ?? 'S', 0, 1) }}
                            </div>
                            <div class="flex flex-col min-w-0">
                                <span class="text-[10px] text-indigo-300 font-bold uppercase tracking-widest leading-none mb-1">
                                    {{ $allAccessibleStores->count() > 1 ? 'Active Store' : 'My Store' }}
                                </span>
                                <span class="text-xs font-bold text-white truncate">{{ $currentStore->name ?? 'Select Store' }}</span>
                            </div>
                        </div>
                        @if($allAccessibleStores->count() > 1)
                        <svg class="w-4 h-4 text-indigo-400 flex-shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        @endif
                    </button>

                    @if($allAccessibleStores->count() > 1)
                    <div x-show="open" @click.away="open = false" x-transition class="absolute left-0 right-0 mt-2 bg-indigo-950 border border-indigo-500/30 rounded-2xl shadow-2xl z-50 overflow-hidden">
                        <div class="p-1 max-h-60 overflow-y-auto">
                            @foreach($allAccessibleStores as $s)
                                @if(($currentStore->id ?? null) !== $s->id)
                                <form action="{{ route('seller.switch-store', $s) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 hover:bg-white/5 rounded-xl transition-all text-left group">
                                        <div class="w-6 h-6 bg-indigo-800 rounded flex-shrink-0 flex items-center justify-center text-[8px] font-bold text-indigo-300 group-hover:bg-indigo-500 group-hover:text-white transition-all">
                                            {{ substr($s->name, 0, 1) }}
                                        </div>
                                        <span class="text-xs font-medium text-indigo-200 group-hover:text-white truncate">{{ $s->name }}</span>
                                    </button>
                                </form>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Navigation -->
            <div class="flex-1 overflow-y-auto no-scrollbar py-6">
                <nav class="space-y-1">
                    <a href="{{ route('seller.dashboard') }}" class="sidebar-item {{ request()->routeIs('seller.dashboard') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('seller.analytics.index') }}" class="sidebar-item {{ request()->routeIs('seller.analytics.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span>Analytics</span>
                    </a>


                    <a href="{{ route('seller.orders.index') }}" class="sidebar-item {{ request()->routeIs('seller.orders.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <span>Orders</span>
                    </a>

                    <a href="{{ route('seller.products.index') }}" class="sidebar-item {{ request()->routeIs('seller.products.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <span>Manage Products</span>
                    </a>

                    <a href="{{ route('seller.settings.edit') }}" class="sidebar-item {{ request()->routeIs('seller.settings.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m12 4a2 2 0 100-4m0 4a2 2 0 110-4m-6 0a2 2 0 100-4m0 4a2 2 0 110-4"/></svg>
                        <span>Store Settings</span>
                    </a>

                    <a href="{{ route('seller.payouts.index') }}" class="sidebar-item {{ request()->routeIs('seller.payouts.*') || request()->routeIs('seller.settlements.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Settlements</span>
                    </a>

                    <a href="{{ route('seller.subscription.index') }}" class="sidebar-item {{ request()->routeIs('seller.subscription.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        <span>Subscription Plans</span>
                    </a>

                    <a href="{{ route('seller.ad-plans.index') }}" class="sidebar-item {{ request()->routeIs('seller.ad-plans.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                        <span>Paid Advertisements</span>
                    </a>

                    <a href="{{ route('seller.staff.index') }}" class="sidebar-item {{ request()->routeIs('seller.staff.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Employees</span>
                    </a>

                    <a href="{{ route('seller.profile.edit') }}" class="sidebar-item {{ request()->routeIs('seller.profile.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>Profile Settings</span>
                    </a>
                </nav>
            </div>

            <!-- Logout Section -->
            <div class="mt-auto border-t border-white/5">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="sidebar-item w-full hover:text-rose-400">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top header -->
            <header class="bg-white/80 backdrop-blur-md border-b border-gray-100 sticky top-0 z-30">
                <div class="flex items-center justify-between px-4 sm:px-8 py-4">
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 -ml-2 text-indigo-900 rounded-xl hover:bg-indigo-50 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    
                    <div class="flex-1"></div>

                    <div class="flex items-center gap-3 sm:gap-6">
                        <!-- Notifications -->
                        <button class="relative p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <span class="absolute top-2 right-2.5 w-2 h-2 bg-rose-500 rounded-full border-2 border-white"></span>
                        </button>

                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" @click.away="open = false" class="flex items-center gap-2 p-1 pr-3 hover:bg-gray-50 rounded-xl transition-all border border-transparent hover:border-gray-200">
                                <div class="w-8 h-8 rounded-lg bg-indigo-500 flex items-center justify-center text-white text-xs font-bold ring-2 ring-indigo-500/10">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            
                            <div x-show="open" x-transition class="absolute right-0 mt-2 w-48 bg-white border border-gray-100 rounded-2xl shadow-xl z-50 overflow-hidden">
                                <div class="px-4 py-3 border-b border-gray-50">
                                    <p class="text-xs text-gray-400 font-medium">Logged in as</p>
                                    <p class="text-sm font-bold text-gray-800 truncate">{{ auth()->user()->email }}</p>
                                </div>
                                <div class="p-1">
                                    <a href="{{ route('seller.profile.edit') }}" class="flex items-center gap-2 px-3 py-2 text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        Profile
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-rose-600 hover:bg-rose-50 rounded-xl transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-8">
                @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl flex items-center gap-3 text-sm animate-fade-in shadow-sm">
                    <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="font-bold text-[11px] uppercase tracking-widest">{{ session('success') }}</span>
                </div>
                @endif

                @if(session('error'))
                <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl flex items-center gap-3 text-sm animate-fade-in shadow-sm">
                    <div class="w-8 h-8 rounded-full bg-rose-100 flex items-center justify-center text-rose-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <span class="font-bold text-[11px] uppercase tracking-widest">{{ session('error') }}</span>
                </div>
                @endif

                @if(session('status'))
                <div class="mb-6 p-4 bg-indigo-50 border border-indigo-200 text-indigo-700 rounded-2xl flex items-center gap-3 text-sm animate-fade-in shadow-sm">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="font-bold text-[11px] uppercase tracking-widest">{{ session('status') }}</span>
                </div>
                @endif


                @yield('content')
            </main>
        </div>
    </div>

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('scripts')
</body>
</html>
