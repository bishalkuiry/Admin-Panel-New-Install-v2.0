@extends('admin.layouts.app')

@section('title', $plugin->display_name . ' Settings')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.plugins.show', $plugin) }}" class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-gray-400 hover:text-gray-900 border border-gray-100 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 leading-tight">{{ $plugin->display_name }} Configuration</h1>
                <p class="text-sm text-gray-500 font-medium">Manage operational parameters for this module</p>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-2xl flex gap-3 items-center animate-fade-in-down">
        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span class="text-sm font-medium">{{ session('success') }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-12 xl:col-span-8">
            <div class="card bg-white border-0 shadow-sm overflow-hidden">
                <div class="p-8">
                    @if($plugin->pluginSettings->isEmpty())
                    <div class="text-center py-20 bg-gray-50/50 rounded-3xl border-2 border-dashed border-gray-100">
                        <div class="w-16 h-16 bg-white rounded-2xl shadow-sm border border-gray-50 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">Immutable Module</h3>
                        <p class="text-sm text-gray-500 max-w-xs mx-auto mt-1">This plugin operates with hardcoded defaults and requires no additional configuration.</p>
                    </div>
                    @else
                    <form method="POST" action="{{ route('admin.plugins.settings.update', $plugin) }}" class="space-y-8">
                        @csrf @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-10">
                            @foreach($plugin->pluginSettings as $setting)
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-gray-500 uppercase tracking-widest flex items-center gap-2 pl-1">
                                    {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                    @if($setting->type === 'encrypted')
                                        <svg class="w-3 h-3 text-indigo-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    @endif
                                </label>
                                
                                <div class="relative group">
                                    @if($setting->type === 'boolean')
                                    <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-2xl border border-gray-100 group-hover:bg-white group-hover:border-indigo-100 transition-all">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="settings[{{ $setting->key }}]" value="1" {{ $setting->value ? 'checked' : '' }} class="sr-only peer">
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                        </label>
                                        <span class="text-sm font-bold text-gray-700">Enabled</span>
                                    </div>
                                    
                                    @elseif($setting->type === 'integer')
                                    <input type="number" name="settings[{{ $setting->key }}]" class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 text-gray-900 font-bold focus:ring-4 focus:ring-indigo-500/10 focus:bg-white focus:border-indigo-500 outline-none transition-all" value="{{ $setting->value }}">
                                    
                                    @elseif($setting->type === 'json')
                                    <textarea name="settings[{{ $setting->key }}]" class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 text-gray-900 font-mono text-xs leading-relaxed focus:ring-4 focus:ring-indigo-500/10 focus:bg-white focus:border-indigo-500 outline-none transition-all" rows="6">{{ is_array($setting->value) ? json_encode($setting->value, JSON_PRETTY_PRINT) : $setting->value }}</textarea>
                                    
                                    @elseif($setting->type === 'encrypted' || str_starts_with($setting->key, 'secret_') || str_starts_with($setting->key, 'password_'))
                                    <input type="password" name="settings[{{ $setting->key }}]" class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 text-gray-900 font-bold focus:ring-4 focus:ring-indigo-500/10 focus:bg-white focus:border-indigo-500 outline-none transition-all" value="{{ $setting->value }}" placeholder="••••••••">
                                    
                                    @else
                                    <input type="text" name="settings[{{ $setting->key }}]" class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 text-gray-900 font-bold focus:ring-4 focus:ring-indigo-500/10 focus:bg-white focus:border-indigo-500 outline-none transition-all" value="{{ $setting->value }}">
                                    @endif
                                </div>
                                
                                @if($manifest['settings'][$setting->key]['description'] ?? null)
                                <p class="text-xs text-gray-400 leading-normal pl-1">{{ $manifest['settings'][$setting->key]['description'] }}</p>
                                @endif
                            </div>
                            @endforeach
                        </div>

                        <div class="flex items-center gap-4 pt-10 border-t border-gray-50">
                            <button type="submit" class="h-14 px-8 bg-indigo-600 text-white rounded-2xl font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-200 active:scale-95 transition-all flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Deploy Configuration
                            </button>
                            <a href="{{ route('admin.plugins.show', $plugin) }}" class="h-14 px-8 flex items-center text-sm font-bold text-gray-500 hover:text-gray-900 transition-colors">Discard Changes</a>
                        </div>
                    </form>
                    @endif
                </div>
            </div>

            @if(isset($manifest['documentation']))
            <div class="mt-8">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4 pl-1">Operation Manual</h3>
                <div class="card bg-white border-0 shadow-sm p-8 prose prose-sm prose-indigo max-w-none">
                    {!! \Illuminate\Support\Str::markdown($manifest['documentation']) !!}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
@keyframes fade-in-down {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-down {
    animation: fade-in-down 0.4s ease-out forwards;
}
</style>
@endsection
