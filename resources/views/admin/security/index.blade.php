@extends('admin.layouts.app')

@section('title', 'Security & Audit Center')

@section('content')
<div x-data="{ activeTab: 'policies' }" class="space-y-8 pb-12">

    {{-- ── Hero Security Header ── --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 rounded-3xl p-8 text-white shadow-2xl border border-slate-700/60">
        {{-- Ambient Glows --}}
        <div class="absolute right-0 top-0 -mt-16 -mr-16 w-96 h-96 bg-gradient-to-br from-orange-500/20 to-amber-500/0 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute left-1/3 bottom-0 -mb-20 w-80 h-80 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
            <div class="space-y-3">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-orange-500/15 text-orange-400 rounded-full text-xs font-bold tracking-wide border border-orange-500/30 backdrop-blur-md">
                    <span class="w-2 h-2 rounded-full bg-orange-400 animate-ping"></span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    System Security & Protection Engine
                </div>
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white font-sans">
                    Security & Audit Center
                </h1>
                <p class="text-slate-300 text-sm max-w-2xl leading-relaxed">
                    Manage real-time authentication parameters, brute-force protection, IP firewall access control, and audit security events across Driver, Store Partner, and Admin roles.
                </p>
            </div>

            {{-- Security Gauge Widget --}}
            <div class="flex items-center gap-5 bg-slate-800/90 p-5 rounded-2xl border border-slate-700/80 backdrop-blur-xl shadow-lg">
                <div class="relative w-16 h-16 flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                        <path stroke-linecap="round" stroke-dasharray="100, 100" class="text-slate-700/60" stroke-width="3.5" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path stroke-linecap="round" stroke-dasharray="96, 100" class="text-emerald-400" stroke-width="3.5" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <span class="absolute text-base font-black text-emerald-400">96%</span>
                </div>
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Health Index</div>
                    <div class="text-base font-extrabold text-white flex items-center gap-2 mt-0.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Fully Hardened
                    </div>
                    <div class="text-[11px] text-slate-400 mt-1">SSL & Brute Force Shield Enabled</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Stat Cards Grid ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        {{-- Card 1 --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-all duration-300 group">
            <div class="flex items-center justify-between">
                <div class="w-12 h-12 bg-orange-50 text-orange-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-lg">Active</span>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-black text-slate-900 tracking-tight">{{ $activeSessions }}</div>
                <div class="text-xs font-semibold text-slate-500 mt-1">Active User Sessions</div>
            </div>
        </div>

        {{-- Card 2 --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-all duration-300 group">
            <div class="flex items-center justify-between">
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <span class="px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-bold rounded-lg">{{ $settings['max_failed_logins'] }} Max</span>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-black text-slate-900 tracking-tight">{{ $settings['lockout_duration_minutes'] }}m Cooldown</div>
                <div class="text-xs font-semibold text-slate-500 mt-1">Brute-Force Lockout</div>
            </div>
        </div>

        {{-- Card 3 --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-all duration-300 group">
            <div class="flex items-center justify-between">
                <div class="w-12 h-12 bg-red-50 text-red-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                </div>
                <span class="px-2.5 py-1 bg-red-50 text-red-700 text-xs font-bold rounded-lg">Firewall</span>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-black text-slate-900 tracking-tight">{{ $blockedIpsCount }} IPs</div>
                <div class="text-xs font-semibold text-slate-500 mt-1">Blacklisted IP Addresses</div>
            </div>
        </div>

        {{-- Card 4 --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-all duration-300 group">
            <div class="flex items-center justify-between">
                <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-lg">Admin OTP</span>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-black text-slate-900 tracking-tight">{{ $settings['enforce_2fa_admin'] ? 'Enforced' : 'Optional' }}</div>
                <div class="text-xs font-semibold text-slate-500 mt-1">Two-Factor Authentication</div>
            </div>
        </div>
    </div>

    {{-- ── Navigation Tabs ── --}}
    <div class="bg-slate-100/80 p-1.5 rounded-2xl flex items-center gap-2 max-w-2xl border border-slate-200/60">
        <button 
            @click="activeTab = 'policies'" 
            :class="activeTab === 'policies' ? 'bg-white text-orange-600 shadow-md font-extrabold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
            class="flex-1 py-3 rounded-xl text-xs sm:text-sm transition-all duration-200 flex items-center justify-center gap-2"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            Auth & Password Policies
        </button>

        <button 
            @click="activeTab = 'ip'" 
            :class="activeTab === 'ip' ? 'bg-white text-orange-600 shadow-md font-extrabold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
            class="flex-1 py-3 rounded-xl text-xs sm:text-sm transition-all duration-200 flex items-center justify-center gap-2"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
            IP Firewall Control
        </button>

        <button 
            @click="activeTab = 'audit'" 
            :class="activeTab === 'audit' ? 'bg-white text-orange-600 shadow-md font-extrabold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
            class="flex-1 py-3 rounded-xl text-xs sm:text-sm transition-all duration-200 flex items-center justify-center gap-2"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Audit Logs
        </button>
    </div>

    {{-- ── TAB 1: Security Policies ── --}}
    <div x-show="activeTab === 'policies'" class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100 space-y-8">
        <div class="flex items-center justify-between pb-6 border-b border-slate-100">
            <div>
                <h2 class="text-xl font-extrabold text-slate-900">Authentication & Lockout Parameters</h2>
                <p class="text-xs text-slate-500 mt-1">Configure global password complexity and brute-force protection thresholds.</p>
            </div>
            <div class="w-10 h-10 bg-orange-50 text-orange-600 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
        </div>

        <form action="{{ route('admin.security.update-policies') }}" method="POST" class="space-y-8">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Minimum Password Length</label>
                    <input type="number" name="min_password_length" value="{{ $settings['min_password_length'] }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-slate-800 text-sm font-semibold focus:outline-none focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 transition-all" min="6" max="32" required>
                    <p class="text-xs text-slate-400">Enforced during user, driver, and vendor account registration.</p>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Max Failed Login Attempts</label>
                    <input type="number" name="max_failed_logins" value="{{ $settings['max_failed_logins'] }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-slate-800 text-sm font-semibold focus:outline-none focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 transition-all" min="3" max="20" required>
                    <p class="text-xs text-slate-400">Number of invalid passwords before temporary account lockout.</p>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Lockout Duration (Minutes)</label>
                    <input type="number" name="lockout_duration_minutes" value="{{ $settings['lockout_duration_minutes'] }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-slate-800 text-sm font-semibold focus:outline-none focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 transition-all" min="5" max="1440" required>
                    <p class="text-xs text-slate-400">Cooldown period required before trying again.</p>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Session Idle Timeout (Minutes)</label>
                    <input type="number" name="session_timeout_minutes" value="{{ $settings['session_timeout_minutes'] }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-slate-800 text-sm font-semibold focus:outline-none focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 transition-all" min="15" max="1440" required>
                    <p class="text-xs text-slate-400">Automatic logout after continuous inactivity.</p>
                </div>
            </div>

            <div class="space-y-4 pt-4 border-t border-slate-100">
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-400">System Hardening Controls</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="flex items-center justify-between p-4 rounded-2xl border border-slate-200/80 bg-slate-50/50 hover:bg-slate-50 cursor-pointer transition-colors">
                        <div>
                            <div class="text-sm font-bold text-slate-800">Require Special Characters</div>
                            <div class="text-xs text-slate-500 mt-0.5">Enforce symbols (!@#$%^&*) in passwords</div>
                        </div>
                        <input type="checkbox" name="require_special_char" value="1" {{ $settings['require_special_char'] ? 'checked' : '' }} class="w-5 h-5 text-orange-600 rounded border-slate-300 focus:ring-orange-500">
                    </label>

                    <label class="flex items-center justify-between p-4 rounded-2xl border border-slate-200/80 bg-slate-50/50 hover:bg-slate-50 cursor-pointer transition-colors">
                        <div>
                            <div class="text-sm font-bold text-slate-800">Require Digits in Passwords</div>
                            <div class="text-xs text-slate-500">Enforce numbers (0-9) in passwords</div>
                        </div>
                        <input type="checkbox" name="require_numbers" value="1" {{ $settings['require_numbers'] ? 'checked' : '' }} class="w-5 h-5 text-orange-600 rounded border-slate-300 focus:ring-orange-500">
                    </label>

                    <label class="flex items-center justify-between p-4 rounded-2xl border border-slate-200/80 bg-slate-50/50 hover:bg-slate-50 cursor-pointer transition-colors">
                        <div>
                            <div class="text-sm font-bold text-slate-800">Enforce Admin 2FA OTP</div>
                            <div class="text-xs text-slate-500">Require 2-factor authentication for Admin logins</div>
                        </div>
                        <input type="checkbox" name="enforce_2fa_admin" value="1" {{ $settings['enforce_2fa_admin'] ? 'checked' : '' }} class="w-5 h-5 text-orange-600 rounded border-slate-300 focus:ring-orange-500">
                    </label>

                    <label class="flex items-center justify-between p-4 rounded-2xl border border-slate-200/80 bg-slate-50/50 hover:bg-slate-50 cursor-pointer transition-colors">
                        <div>
                            <div class="text-sm font-bold text-slate-800">Force HTTPS / SSL Mode</div>
                            <div class="text-xs text-slate-500">Redirect all HTTP connections to encrypted HTTPS</div>
                        </div>
                        <input type="checkbox" name="force_https" value="1" {{ $settings['force_https'] ? 'checked' : '' }} class="w-5 h-5 text-orange-600 rounded border-slate-300 focus:ring-orange-500">
                    </label>
                </div>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="px-6 py-3.5 bg-gradient-to-r from-orange-500 to-amber-600 hover:from-orange-600 hover:to-amber-700 text-white font-extrabold text-sm rounded-2xl shadow-lg shadow-orange-500/25 hover:shadow-orange-500/40 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Save Security Policies
                </button>
            </div>
        </form>
    </div>

    {{-- ── TAB 2: IP Access Control ── --}}
    <div x-show="activeTab === 'ip'" class="space-y-6" style="display: none;">
        <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100">
            <h2 class="text-xl font-extrabold text-slate-900 mb-6">Add Firewall IP Rule</h2>
            <form action="{{ route('admin.security.add-ip') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                @csrf
                <div class="sm:col-span-2 space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">IP Address</label>
                    <input type="text" name="ip_address" placeholder="e.g. 103.24.12.89" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm font-semibold focus:outline-none focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10" required>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Rule Action</label>
                    <select name="rule_type" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm font-semibold focus:outline-none focus:border-orange-500" required>
                        <option value="blacklist">Blacklist (Block Access)</option>
                        <option value="whitelist">Whitelist (Allow Only)</option>
                    </select>
                </div>

                <div class="sm:col-span-3 pt-2">
                    <button type="submit" class="px-6 py-3 bg-slate-900 hover:bg-slate-800 text-white font-extrabold text-sm rounded-xl transition-all inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Firewall Rule
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-6 border-b border-slate-100">
                <h3 class="text-lg font-extrabold text-slate-900">Blacklisted IP Addresses</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">IP Address</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Action Type</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Status</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500 text-right">Remove</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($settings['ip_blacklist'] as $ip)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-6 py-4 font-mono font-bold text-slate-900 text-sm">{{ $ip }}</td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 bg-red-100 text-red-700 text-xs font-extrabold rounded-lg inline-block">BLACK LISTED</span>
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold text-slate-500">Blocked at gateway</td>
                            <td class="px-6 py-4 text-right">
                                <form action="{{ route('admin.security.remove-ip') }}" method="POST" class="inline-block">
                                    @csrf
                                    <input type="hidden" name="ip_address" value="{{ $ip }}">
                                    <input type="hidden" name="rule_type" value="blacklist">
                                    <button type="submit" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 font-bold text-xs rounded-xl transition-colors">
                                        Unblock
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-400 text-sm">
                                No IP addresses currently blacklisted.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ── TAB 3: Security Audit Logs ── --}}
    <div x-show="activeTab === 'audit'" class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden" style="display: none;">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-extrabold text-slate-900">Security Audit Trail</h2>
                <p class="text-xs text-slate-500 mt-1">Real-time log of security events across Driver App, Store App, and Admin Panel.</p>
            </div>
            <span class="px-3 py-1 bg-slate-100 text-slate-700 text-xs font-bold rounded-lg">Realtime Monitor</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Identity / Role</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Security Event</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">IP Address</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Severity</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($auditLogs as $log)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-extrabold text-slate-900 text-sm">{{ $log['user'] }}</div>
                            <div class="text-xs text-slate-400 font-mono">{{ $log['email'] }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm font-bold text-slate-800">{{ $log['event'] }}</td>
                        <td class="px-6 py-4 font-mono text-xs text-slate-600">{{ $log['ip'] }}</td>
                        <td class="px-6 py-4">
                            @if($log['status'] === 'success')
                                <span class="px-3 py-1 bg-emerald-100 text-emerald-800 text-xs font-bold rounded-lg inline-block">Success</span>
                            @elseif($log['status'] === 'warning')
                                <span class="px-3 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-lg inline-block">Warning</span>
                            @else
                                <span class="px-3 py-1 bg-red-100 text-red-800 text-xs font-bold rounded-lg inline-block">Blocked</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs font-semibold text-slate-500">{{ $log['created_at'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
