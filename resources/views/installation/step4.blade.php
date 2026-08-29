@extends('layouts.install')

@section('title', 'Database Migration')
@section('progress', '67%')

@section('content')
<div class="p-8 sm:p-12">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-primary-100 rounded-2xl mb-4">
            <svg class="w-10 h-10 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Database Migration</h2>
        <p class="text-gray-500">Set up your database tables and initial data</p>
    </div>

    <div class="bg-gray-50 rounded-xl p-6 mb-8">
        <h3 class="font-semibold text-gray-900 mb-4">This will:</h3>
        <ul class="space-y-3">
            <li class="flex items-start">
                <svg class="w-5 h-5 text-primary-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span class="text-gray-600">Create all required database tables</span>
            </li>
            <li class="flex items-start">
                <svg class="w-5 h-5 text-primary-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span class="text-gray-600">Set up indexes and relationships</span>
            </li>
            <li class="flex items-start">
                <svg class="w-5 h-5 text-primary-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span class="text-gray-600">Insert default configuration data</span>
            </li>
        </ul>
    </div>

    @if(request('err'))
    <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 rounded-xl text-red-600 text-sm">
        <strong>Fresh Install failed:</strong> {{ urldecode(request('err')) }}
    </div>
    @endif

    <div class="flex flex-col sm:flex-row gap-4">
        <a href="{{ route('import_sql') }}" 
           class="flex-1 inline-flex items-center justify-center px-8 py-4 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-all shadow-lg shadow-primary-600/30">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            Run Migration
        </a>
        <a href="{{ route('force-import-sql') }}" 
           class="flex-1 inline-flex items-center justify-center px-8 py-4 border-2 border-red-300 text-red-600 hover:bg-red-50 font-semibold rounded-xl transition-all"
           onclick="return confirm('This will DELETE all existing data. Are you sure?')">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            Fresh Install (Wipe Data)
        </a>
    </div>

    <p class="text-center text-sm text-gray-500 mt-4">
        Use "Fresh Install" only if you want to start completely fresh
    </p>
</div>
@endsection
