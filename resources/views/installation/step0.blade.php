@extends('layouts.install')

@section('title', 'Welcome')
@section('progress', '0%')

@section('content')
<div class="p-8 sm:p-12">
    <div class="text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-primary-100 rounded-2xl mb-6">
            <svg class="w-10 h-10 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
            </svg>
        </div>
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Welcome to InAllCart</h2>
        <p class="text-gray-500 mb-8 max-w-md mx-auto">
            Thank you for choosing InAllCart! This wizard will guide you through the installation process in just a few simple steps.
        </p>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8 text-left">
            <div class="p-4 bg-green-50 rounded-xl border border-green-100">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mb-3">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h3 class="font-semibold text-gray-900 mb-1">Dependencies</h3>
                <p class="text-sm text-gray-500">Composer packages installed</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-xl">
                <div class="w-8 h-8 bg-primary-100 rounded-lg flex items-center justify-center mb-3">
                    <span class="text-primary-600 font-bold">1</span>
                </div>
                <h3 class="font-semibold text-gray-900 mb-1">Requirements</h3>
                <p class="text-sm text-gray-500">Check server compatibility</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-xl">
                <div class="w-8 h-8 bg-primary-100 rounded-lg flex items-center justify-center mb-3">
                    <span class="text-primary-600 font-bold">2</span>
                </div>
                <h3 class="font-semibold text-gray-900 mb-1">Database</h3>
                <p class="text-sm text-gray-500">Configure your database</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-xl">
                <div class="w-8 h-8 bg-primary-100 rounded-lg flex items-center justify-center mb-3">
                    <span class="text-primary-600 font-bold">3</span>
                </div>
                <h3 class="font-semibold text-gray-900 mb-1">Setup</h3>
                <p class="text-sm text-gray-500">Create admin account</p>
            </div>
        </div>

        <a href="{{ route('step1', ['token' => bcrypt('step_1')]) }}" 
           class="inline-flex items-center px-8 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-all shadow-lg shadow-primary-600/30">
            Start Installation
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
            </svg>
        </a>
    </div>
</div>
@endsection
