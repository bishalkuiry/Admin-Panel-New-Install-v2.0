@extends('layouts.install')

@section('title', 'Installation Complete')
@section('progress', '100%')

@section('content')
<div class="p-8 sm:p-12">
    <div class="text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-2xl mb-6">
            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Installation Complete! 🎉</h2>
        <p class="text-gray-500 mb-8 max-w-md mx-auto">
            Your InAllCart platform has been successfully installed and is ready to use.
        </p>

        <div class="bg-gray-50 rounded-xl p-6 mb-8 text-left">
            <h3 class="font-semibold text-gray-900 mb-4">What's Next?</h3>
            <ul class="space-y-3">
                <li class="flex items-start">
                    <span class="flex items-center justify-center w-6 h-6 bg-primary-100 text-primary-600 rounded-full text-sm font-bold mr-3 flex-shrink-0">1</span>
                    <span class="text-gray-600">Login to your admin panel and explore the dashboard</span>
                </li>
                <li class="flex items-start">
                    <span class="flex items-center justify-center w-6 h-6 bg-primary-100 text-primary-600 rounded-full text-sm font-bold mr-3 flex-shrink-0">2</span>
                    <span class="text-gray-600">Add your product categories and attributes</span>
                </li>
                <li class="flex items-start">
                    <span class="flex items-center justify-center w-6 h-6 bg-primary-100 text-primary-600 rounded-full text-sm font-bold mr-3 flex-shrink-0">3</span>
                    <span class="text-gray-600">Start adding products to your store</span>
                </li>
            </ul>
        </div>

        <a href="/admin" class="inline-flex items-center px-8 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-all shadow-lg shadow-primary-600/30">
            Go to Admin Panel
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
            </svg>
        </a>
    </div>
</div>
@endsection
