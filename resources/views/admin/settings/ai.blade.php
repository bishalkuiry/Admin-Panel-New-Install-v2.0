@extends('admin.layouts.app')

@section('title', 'AI Settings')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
        <div class="min-w-0">
            <h1 class="text-lg sm:text-2xl font-bold text-gray-900 truncate">AI Settings</h1>
            <p class="text-xs sm:text-sm text-gray-500 hidden sm:block">Configure AI models for product generation and chatbot assistance</p>
        </div>
        <a href="{{ route('admin.settings.index') }}" class="btn-secondary text-sm flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            <span class="hidden sm:inline">Back to Settings</span>
            <span class="sm:hidden">Back</span>
        </a>
    </div>

    <form action="{{ route('admin.settings.ai.update') }}" method="POST">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Provider Selection -->
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <div class="mb-4">
                        <h3 class="font-semibold text-gray-900 text-lg">Active AI Provider</h3>
                        <p class="text-sm text-gray-500">Choose the AI intelligence powering your platform</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- OpenAI -->
                        <label class="cursor-pointer">
                            <input type="radio" name="ai_provider" value="openai" class="peer sr-only" {{ ($settings['ai_provider'] ?? 'openai') == 'openai' ? 'checked' : '' }}>
                            <div class="p-4 border-2 border-gray-200 rounded-lg peer-checked:border-primary-500 peer-checked:bg-primary-50 hover:border-gray-300 transition-all h-full">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 bg-slate-900 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M22.28 12.17a5.29 5.29 0 0 0-1.1-4.9 5.3 5.3 0 0 0-3.35-2.25 5.25 5.25 0 0 0-5.43 1.37L10.8 7.3a.5.5 0 0 1-.8-.4V.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v6.4a.5.5 0 0 1-.8.4L5.6 6.4a5.25 5.25 0 0 0-5.43-1.37 5.3 5.3 0 0 0-3.35 2.25 5.29 5.29 0 0 0-1.1 4.9 5.25 5.25 0 0 0 3.32 4.13l1.5.5a.5.5 0 0 1 .3.45v6.27a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-6.27a.5.5 0 0 1 .3-.45l1.5-.5a5.25 5.25 0 0 0 3.32-4.13zm-10.28 2.83a2.5 2.5 0 1 1 2.5-2.5 2.5 2.5 0 0 1-2.5 2.5z"/></svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="font-medium text-gray-900">OpenAI</span>
                                            <span class="px-2 py-0.5 bg-slate-100 text-slate-700 text-[10px] font-bold rounded uppercase">GPT-4o</span>
                                        </div>
                                        <p class="text-xs text-gray-500">Best for creative descriptions and reliable reasoning.</p>
                                    </div>
                                </div>
                            </div>
                        </label>

                        <!-- Gemini -->
                        <label class="cursor-pointer">
                            <input type="radio" name="ai_provider" value="gemini" class="peer sr-only" {{ ($settings['ai_provider'] ?? '') == 'gemini' ? 'checked' : '' }}>
                            <div class="p-4 border-2 border-gray-200 rounded-lg peer-checked:border-primary-500 peer-checked:bg-primary-50 hover:border-gray-300 transition-all h-full">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L14.85 9.15L22 12L14.85 14.85L12 22L9.15 14.85L2 12L9.15 9.15L12 2Z"/></svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="font-medium text-gray-900">Google Gemini</span>
                                            <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-[10px] font-bold rounded uppercase">2.5 Flash</span>
                                        </div>
                                        <p class="text-xs text-gray-500">Fastest multimodal support (vision + generation) with high rate limits.</p>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Development / Mock Mode -->
                <div class="bg-white border-l-4 border-amber-400 bg-amber-50 rounded-lg p-6 shadow-sm mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-900 text-lg flex items-center gap-2">
                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                Mock AI Mode
                            </h3>
                            <p class="text-sm text-gray-700 mt-1">Simulate AI responses to save API credits. Perfect for testing UI.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="mock_ai_enabled" value="1" class="sr-only peer" {{ ($settings['mock_ai_enabled'] ?? '') ? 'checked' : '' }}>
                            <div class="w-14 h-7 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-amber-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-amber-500"></div>
                        </label>
                    </div>
                </div>

                <!-- OpenAI Config Card -->
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm" id="openai_panel">
                    <div class="px-6 py-4 bg-slate-50 border-b border-gray-200 flex items-center gap-3">
                        <div class="w-8 h-8 bg-slate-900 rounded-md flex items-center justify-center text-white">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M22.28 12.17a5.29 5.29 0 0 0-1.1-4.9 5.3 5.3 0 0 0-3.35-2.25 5.25 5.25 0 0 0-5.43 1.37L10.8 7.3a.5.5 0 0 1-.8-.4V.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v6.4a.5.5 0 0 1-.8.4L5.6 6.4a5.25 5.25 0 0 0-5.43-1.37 5.3 5.3 0 0 0-3.35 2.25 5.29 5.29 0 0 0-1.1 4.9 5.25 5.25 0 0 0 3.32 4.13l1.5.5a.5.5 0 0 1 .3.45v6.27a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-6.27a.5.5 0 0 1 .3-.45l1.5-.5a5.25 5.25 0 0 0 3.32-4.13zm-10.28 2.83a2.5 2.5 0 1 1 2.5-2.5 2.5 2.5 0 0 1-2.5-2.5z"/></svg>
                        </div>
                        <h3 class="font-semibold text-gray-900">OpenAI Configuration</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="form-group">
                            <label class="label">API Key</label>
                            <input type="password" name="openai_api_key" class="input" value="{{ $settings['openai_api_key'] ?? '' }}" placeholder="sk-...">
                            <p class="form-hint">Find your keys in the <a href="https://platform.openai.com/api-keys" target="_blank" class="text-primary-600 underline">OpenAI Dashboard</a></p>
                        </div>
                        <div class="form-group">
                            <label class="label">Primary Model</label>
                            <select name="openai_model" class="input">
                                <option value="gpt-4o" {{ ($settings['openai_model'] ?? 'gpt-4o') == 'gpt-4o' ? 'selected' : '' }}>GPT-4o (Most Intelligent)</option>
                                <option value="gpt-4o-mini" {{ ($settings['openai_model'] ?? '') == 'gpt-4o-mini' ? 'selected' : '' }}>GPT-4o Mini (Fast & Efficient)</option>
                                <option value="gpt-4-turbo" {{ ($settings['openai_model'] ?? '') == 'gpt-4-turbo' ? 'selected' : '' }}>GPT-4 Turbo</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Gemini Config Card -->
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm" id="gemini_panel">
                    <div class="px-6 py-4 bg-blue-50 border-b border-blue-100 flex items-center gap-3">
                        <div class="w-8 h-8 bg-blue-600 rounded-md flex items-center justify-center text-white">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L14.85 9.15L22 12L14.85 14.85L12 22L9.15 14.85L2 12L9.15 9.15L12 2Z"/></svg>
                        </div>
                        <h3 class="font-semibold text-gray-900">Google Gemini Configuration</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="form-group">
                            <label class="label">API Key</label>
                            <input type="password" name="gemini_api_key" class="input" value="{{ $settings['gemini_api_key'] ?? '' }}" placeholder="AIza...">
                            <p class="form-hint">Get a free key from <a href="https://aistudio.google.com/" target="_blank" class="text-primary-600 underline">Google AI Studio</a></p>
                        </div>
                        <div class="form-group">
                            <label class="label">Primary Model</label>
                            <select name="gemini_model" class="input">
                                <option value="gemini-2.5-flash" {{ ($settings['gemini_model'] ?? 'gemini-2.5-flash') == 'gemini-2.5-flash' ? 'selected' : '' }}>Gemini 2.5 Flash (Fastest + Vision)</option>
                                <option value="gemini-3-pro-preview" {{ ($settings['gemini_model'] ?? '') == 'gemini-3-pro-preview' ? 'selected' : '' }}>Gemini 3 Pro Preview (Powerful & Advanced)</option>
                                <option value="gemini-2.5-pro-exp" {{ ($settings['gemini_model'] ?? '') == 'gemini-2.5-pro-exp' ? 'selected' : '' }}>Gemini 2.5 Pro Experimental (Highly Intelligent)</option>
                            </select>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Status Card -->
                <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm sticky top-6">
                    <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        AI Integration Status
                    </h3>
                    
                    <div class="space-y-4">
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider mb-1">Active Provider</p>
                            <div class="flex items-center gap-2">
                                @if(($settings['ai_provider'] ?? 'openai') == 'openai')
                                <div class="w-2 h-2 bg-slate-900 rounded-full"></div>
                                <span class="text-sm font-semibold text-slate-800">OpenAI Enabled</span>
                                @else
                                <div class="w-2 h-2 bg-blue-600 rounded-full"></div>
                                <span class="text-sm font-semibold text-blue-800">Gemini Enabled</span>
                                @endif
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-500">Multimodal (Vision)</span>
                                <span class="font-medium {{ ($settings['ai_provider'] ?? 'openai') == 'gemini' ? 'text-green-600' : 'text-amber-600' }}">
                                    {{ ($settings['ai_provider'] ?? 'openai') == 'gemini' ? 'Native' : 'Supported' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-500">Latency</span>
                                <span class="font-medium text-gray-900">Low (Flash Optimized)</span>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-100">
                            <x-permission-btn 
                                permission="settings.manage" 
                                type="submit"
                                class="btn-primary w-full justify-center shadow-md" 
                                label="Save Configuration"
                                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                            />
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Smooth transitions for radio selection
    document.querySelectorAll('input[name="ai_provider"]').forEach(input => {
        input.addEventListener('change', function() {
            // Logic to visually highlight selection if needed
            // Currently background changes via peer-checked classes
        });
    });
</script>
@endpush
