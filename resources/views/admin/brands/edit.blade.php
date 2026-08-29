@extends('admin.layouts.app')
@section('title', 'Edit Brand - ' . $brand->name)
@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.brands.index') }}" class="p-2 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-500">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900 tracking-wide">Edit Brand</h1>
            <p class="text-sm text-gray-500 mt-1">Update brand details</p>
        </div>
    </div>

    <form action="{{ route('admin.brands.update', $brand) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Brand Information -->
                <div class="card bg-white border border-gray-200 shadow-sm rounded-xl p-6">
                    <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider mb-6 border-b border-gray-100 pb-4">Brand Information</h3>
                    
                    <div class="space-y-5">
                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Brand Name</label>
                            <input type="text" name="name" value="{{ old('name', $brand->name) }}" required class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder:text-gray-400">
                            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <!-- Store Assignment -->
                <div class="card bg-white border border-gray-200 shadow-sm rounded-xl p-6">
                    <div class="flex items-center justify-between mb-6 border-b border-gray-100 pb-4">
                        <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider">
                            Store Availability <span class="text-gray-400 font-medium normal-case tracking-normal ml-1">(Optional)</span>
                        </h3>
                        <span class="text-xs text-gray-500">Select where this brand is sold</span>
                    </div>
                    
                    @if($stores->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                            @foreach($stores as $store)
                            <label class="relative flex items-start p-4 border border-gray-200 rounded-xl cursor-pointer hover:border-primary-500 hover:bg-primary-50/10 transition-all group">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" name="store_ids[]" value="{{ $store->id }}" {{ in_array($store->id, $assignedStoreIds) ? 'checked' : '' }} class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500 transition-colors">
                                </div>
                                <div class="ml-3 text-sm">
                                    <span class="font-bold text-gray-900 block group-hover:text-primary-700 transition-colors">{{ $store->name }}</span>
                                    <span class="text-xs text-gray-500">{{ $store->city ?? 'Main Location' }}</span>
                                </div>
                            </label>
                            @endforeach
                        </div>
                        @error('store_ids')<p class="text-red-500 text-xs mt-2">{{ $message }}</p>@enderror
                    @else
                        <div class="text-center py-8 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                            <p class="text-sm text-gray-500">No active stores found.</p>
                            <a href="#" class="text-xs font-bold text-primary-600 hover:underline mt-1 block">Create a store first</a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Column: Settings -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Logo & Status -->
                <div class="card bg-white border border-gray-200 shadow-sm rounded-xl p-6">
                    <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider mb-6 border-b border-gray-100 pb-4">Assets & Status</h3>
                    
                    <div class="space-y-6">
                        <!-- Logo Upload -->
                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-3">Brand Logo</label>
                            <div class="flex items-center gap-4">
                                <div class="relative w-16 h-16 rounded-xl bg-gray-100 flex items-center justify-center overflow-hidden border border-gray-200 shrink-0 group">
                                    <img id="logo-preview" src="{{ $brand->logo ? storage_url($brand->logo) : '#' }}" alt="Preview" class="w-full h-full object-contain {{ $brand->logo ? '' : 'hidden' }}">
                                    <button type="button" id="remove-logo-btn" onclick="removeLogo()" class="absolute top-0 right-0 p-0.5 bg-gray-900/50 hover:bg-red-500 text-white rounded-bl-lg transition-colors {{ $brand->logo ? '' : 'hidden' }} backdrop-blur-sm">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                    <svg id="logo-placeholder" class="w-6 h-6 text-gray-400 {{ $brand->logo ? 'hidden' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <div class="flex-1">
                                    <input type="file" name="logo" id="logo-input" accept="image/*" class="hidden" onchange="previewImage(this)">
                                    <input type="hidden" name="remove_logo" id="remove_logo_input" value="0">
                                    <button type="button" onclick="document.getElementById('logo-input').click()" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                        Change Logo
                                    </button>
                                    <p class="text-xs text-gray-500 mt-1">Recommended 1:1 ratio</p>
                                </div>
                            </div>
                            @error('logo')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="border-t border-gray-100 pt-6"></div>

                        <!-- Status Toggles -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="text-sm font-medium text-gray-900 block">Active Status</label>
                                    <p class="text-xs text-gray-500 mt-0.5">Visible to customers</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" {{ $brand->is_active ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="text-sm font-medium text-gray-900 block">Featured Brand</label>
                                    <p class="text-xs text-gray-500 mt-0.5">Show on home page</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="is_featured" value="0">
                                    <input type="checkbox" name="is_featured" value="1" {{ $brand->is_featured ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col gap-3">
                    <button type="submit" class="w-full py-3 bg-gray-900 text-white rounded-lg text-sm font-bold uppercase tracking-widest hover:bg-black transition-all shadow-md active:scale-[0.98]">
                        Save Changes
                    </button>
                    <a href="{{ route('admin.brands.index') }}" class="w-full py-3 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 rounded-lg text-sm font-bold uppercase tracking-widest text-center transition-all">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('logo-preview').src = e.target.result;
                document.getElementById('logo-preview').classList.remove('hidden');
                document.getElementById('logo-placeholder').classList.add('hidden');
                document.getElementById('remove-logo-btn').classList.remove('hidden');
                document.getElementById('remove_logo_input').value = '0'; // Reset remove flag
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function removeLogo() {
        const input = document.getElementById('logo-input');
        input.value = ''; // Clear file input
        
        document.getElementById('logo-preview').src = '#';
        document.getElementById('logo-preview').classList.add('hidden');
        document.getElementById('logo-placeholder').classList.remove('hidden');
        document.getElementById('remove-logo-btn').classList.add('hidden');
        
        // Use hidden input to tell backend to remove content
        document.getElementById('remove_logo_input').value = '1';
    }
</script>
@endsection
