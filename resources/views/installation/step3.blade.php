@extends('layouts.install')

@section('title', 'Database Configuration')
@section('progress', '50%')

@section('content')
<div class="p-8 sm:p-12">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-primary-100 rounded-2xl mb-4">
            <svg class="w-10 h-10 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Database Configuration</h2>
        <p class="text-gray-500">Enter your MySQL database credentials</p>
    </div>

    <form action="{{ route('install.db') }}" method="POST" class="space-y-6">
        @csrf
        <input type="hidden" name="token" value="{{ bcrypt('step_4') }}">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Database Host</label>
            <input type="text" name="DB_HOST" value="{{ old('DB_HOST', env('DB_HOST', 'localhost')) }}" required
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
            <p class="text-xs text-gray-500 mt-1">Usually "localhost" or "127.0.0.1"</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Database Name</label>
            <input type="text" name="DB_DATABASE" required
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition"
                   placeholder="quixko_db">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Database Username</label>
            <input type="text" name="DB_USERNAME" required
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition"
                   placeholder="root">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Database Password</label>
            <input type="password" name="DB_PASSWORD"
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition"
                   placeholder="••••••••">
        </div>

        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-amber-600 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="text-sm text-amber-800">
                    <p class="font-medium">Important</p>
                    <p class="mt-1">Make sure the database exists and the user has full privileges.</p>
                </div>
            </div>
        </div>

        <div class="flex justify-between pt-4">
            <a href="{{ route('step1', ['token' => bcrypt('step_1')]) }}" class="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition">
                Back
            </a>
            <button type="submit" class="inline-flex items-center px-8 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-all shadow-lg shadow-primary-600/30">
                Test & Save
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </button>
        </div>
    </form>
</div>
@endsection
