@extends('seller.layouts.app')

@section('title', 'Global Settings: ' . $store->name)

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Compact Page Header -->
    <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('seller.dashboard') }}" class="group w-8 h-8 rounded border border-gray-200 flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:border-indigo-200 transition-all">
                <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-tight">Store Configuration</h1>
                <p class="text-[10px] text-gray-500 font-medium uppercase tracking-wider">Modify store profile and operational parameters</p>
            </div>
        </div>
        <div>
            <button type="submit" form="store-settings-form" class="inline-flex items-center px-8 py-2 bg-indigo-600 border border-transparent rounded text-[10px] font-black text-white hover:bg-indigo-700 uppercase tracking-widest transition-all shadow-sm">
                Update Store
            </button>
        </div>
    </div>

    <!-- Quick Actions / Status Row -->
    <div class="bg-white border border-gray-200 rounded-sm p-4 flex items-center justify-between mb-6 shadow-sm">
        <div class="flex items-center gap-6">
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Network Status</span>
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest {{ $store->is_online ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-100' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $store->is_online ? 'bg-emerald-600' : 'bg-rose-600' }} animate-pulse"></span>
                    {{ $store->is_online ? 'Live' : 'Standby' }}
                </span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Commission Tier</span>
                <span class="text-[10px] font-black text-gray-900 uppercase">Standard {{ $store->commission_percent }}% Per Transaction</span>
            </div>
        </div>
        <form action="{{ route('seller.settings.update') }}" method="POST" id="quick-toggle-form">
            @csrf
            @method('PUT')
            <input type="hidden" name="name" value="{{ $store->name }}">
            <input type="hidden" name="email" value="{{ $store->email }}">
            <input type="hidden" name="phone" value="{{ $store->phone }}">
            <input type="hidden" name="address" value="{{ $store->address }}">
            <input type="hidden" name="city" value="{{ $store->city }}">
            <input type="hidden" name="state" value="{{ $store->state }}">
            <input type="hidden" name="postal_code" value="{{ $store->postal_code }}">
            
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="is_online" value="1" {{ $store->is_online ? 'checked' : '' }} onchange="this.form.submit()" class="sr-only peer">
                <div class="w-8 h-4 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-emerald-600 shadow-inner"></div>
                <span class="ml-2 text-[10px] font-black text-gray-500 uppercase tracking-widest">Store Open / Close</span>
            </label>
        </form>
    </div>

    <form id="store-settings-form" action="{{ route('seller.settings.update') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        @csrf
        @method('PUT')
        
        <!-- Left Column: Primary Data -->
        <div class="lg:col-span-8 space-y-6">
            <!-- Profile Data Table -->
            <div class="bg-white border border-gray-200 rounded-sm shadow-sm overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Store Profile Data</h3>
                </div>
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Registered Trading Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $store->name) }}" required class="block w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-indigo-500 focus:border-indigo-500 font-medium">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Primary Contact Email <span class="text-rose-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $store->email) }}" required class="block w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-indigo-500 focus:border-indigo-500 font-medium">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Business Phone <span class="text-rose-500">*</span></label>
                            <input type="text" name="phone" value="{{ old('phone', $store->phone) }}" required class="block w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-indigo-500 focus:border-indigo-500 font-medium">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Operating City <span class="text-rose-500">*</span></label>
                            <input type="text" name="city" value="{{ old('city', $store->city) }}" required class="block w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-indigo-500 focus:border-indigo-500 font-medium">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Physical Head Office Address <span class="text-rose-500">*</span></label>
                        <textarea name="address" rows="3" required class="block w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('address', $store->address) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">State / Province <span class="text-rose-500">*</span></label>
                            <input type="text" name="state" value="{{ old('state', $store->state) }}" required class="block w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-indigo-500 focus:border-indigo-500 font-medium">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Postal Index Number (PIN) <span class="text-rose-500">*</span></label>
                            <input type="text" name="postal_code" value="{{ old('postal_code', $store->postal_code) }}" required class="block w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-indigo-500 focus:border-indigo-500 font-medium font-mono tracking-tighter">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Commercial Summary (Public)</label>
                        <textarea name="description" rows="4" placeholder="Brief elevator pitch for your brand..." class="block w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('description', $store->description) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Asset Management -->
            <div class="bg-white border border-gray-200 rounded-sm shadow-sm overflow-hidden" 
                 x-data="{ 
                    logoPreview: '{{ $store->logo ? storage_url($store->logo) : '' }}', 
                    bannerPreview: '{{ $store->banner ? storage_url($store->banner) : '' }}',
                    updatePreview(event, type) {
                        const file = event.target.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                if(type === 'logo') this.logoPreview = e.target.result;
                                if(type === 'banner') this.bannerPreview = e.target.result;
                            };
                            reader.readAsDataURL(file);
                        }
                    }
                 }">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Brand Assets</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Corporate Mark (Logo)</label>
                            <div class="relative group aspect-square rounded border border-gray-200 bg-gray-50 overflow-hidden shadow-inner flex items-center justify-center">
                                <template x-if="logoPreview">
                                    <img :src="logoPreview" class="w-full h-full object-cover grayscale brightness-90 group-hover:grayscale-0 transition-all">
                                </template>
                                <template x-if="!logoPreview">
                                    <div class="text-gray-200">
                                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                </template>
                                <div class="absolute inset-x-0 bottom-0 bg-gray-900/60 p-2 text-[8px] text-white text-center font-black uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-opacity">Select New File</div>
                                <input type="file" name="logo" @change="updatePreview($event, 'logo')" class="absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                            </div>
                            <p class="text-[9px] text-gray-400 italic">Recommended: Square PNG/WEBP, Max 2MB.</p>
                        </div>

                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Marketplace Hero (Banner)</label>
                            <div class="relative group aspect-video rounded border border-gray-200 bg-gray-50 overflow-hidden shadow-inner flex items-center justify-center">
                                <template x-if="bannerPreview">
                                    <img :src="bannerPreview" class="w-full h-full object-cover grayscale brightness-90 group-hover:grayscale-0 transition-all">
                                </template>
                                <template x-if="!bannerPreview">
                                    <div class="text-gray-200">
                                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                </template>
                                <div class="absolute inset-x-0 bottom-0 bg-gray-900/60 p-2 text-[8px] text-white text-center font-black uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-opacity">Select New File</div>
                                <input type="file" name="banner" @change="updatePreview($event, 'banner')" class="absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                            </div>
                            <p class="text-[9px] text-gray-400 italic">Recommended: 16:9 Aspect Ratio, Max 2MB.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Operational Protocols -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Settlement Infrastructure -->
            <div class="bg-white border border-gray-200 rounded-sm shadow-sm overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Financial Remittance</h3>
                </div>
                <div class="p-5 space-y-5">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Beneficiary Bank</label>
                        <input type="text" name="bank_name" value="{{ old('bank_name', $store->bank_name) }}" placeholder="e.g. ICICI Corporate" class="block w-full px-3 py-2 border border-gray-300 rounded text-xs font-bold text-gray-900 focus:ring-indigo-500">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Settlement Account #</label>
                        <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $store->bank_account_number) }}" class="block w-full px-3 py-2 border border-gray-300 rounded text-xs font-mono font-bold text-gray-900 tracking-widest">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Routing Code (IFSC)</label>
                        <input type="text" name="bank_ifsc" value="{{ old('bank_ifsc', $store->bank_ifsc) }}" class="block w-full px-3 py-2 border border-gray-300 rounded text-xs font-mono font-bold text-gray-900 uppercase tracking-[0.2em]">
                    </div>
                </div>
            </div>

            <!-- Operational Runtime -->
            <div class="bg-white border border-gray-200 rounded-sm shadow-sm overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Service Window</h3>
                </div>
                <div class="p-5 space-y-4">
                    <p class="text-[10px] text-gray-400 font-bold uppercase leading-relaxed">Advanced daywise operating windows and automated holiday shutdown protocols are enabled.</p>
                    <div class="p-3 bg-indigo-50 border border-indigo-100 rounded text-[9px] font-black text-indigo-700 uppercase tracking-widest leading-normal">
                        Note: Holiday automation is active based on Regional Commercial Calendar.
                    </div>
                </div>
            </div>

            <!-- Commitment Control -->
            <div class="bg-white border border-gray-200 rounded-sm shadow-sm p-4">
                <button type="submit" form="store-settings-form" class="w-full bg-gray-900 text-white rounded py-3 text-[10px] font-black uppercase tracking-[0.2em] hover:bg-black transition-all shadow-lg active:scale-95">
                    Update Store
                </button>
                <p class="text-center text-[8px] text-gray-400 mt-3 font-bold uppercase tracking-widest">Syncing with Central Registry</p>
            </div>
        </div>
    </form>
</div>
@endsection
