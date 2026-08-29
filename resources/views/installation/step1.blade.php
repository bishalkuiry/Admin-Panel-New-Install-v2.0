@extends('layouts.install')

@section('title', 'Server Requirements')
@section('progress', '17%')

@section('content')
<div class="p-8 sm:p-12">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-primary-100 rounded-2xl mb-4">
            <svg class="w-10 h-10 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Server Requirements</h2>
        <p class="text-gray-500">Checking if your server meets all requirements</p>
    </div>

    <div class="space-y-3 mb-8">
        @php
            $requirements = [
                'PHP Version (8.2+)' => version_compare(PHP_VERSION, '8.2.0', '>='),
                'cURL Extension' => $permission['curl'] ?? false,
                'BCMath Extension' => $permission['bcmath'] ?? false,
                'Ctype Extension' => $permission['ctype'] ?? false,
                'JSON Extension' => $permission['json'] ?? false,
                'Mbstring Extension' => $permission['mbstring'] ?? false,
                'OpenSSL Extension' => $permission['openssl'] ?? false,
                'PDO Extension' => $permission['pdo'] ?? false,
                'PDO MySQL Extension' => $permission['pdo_mysql'] ?? false,
                'Tokenizer Extension' => $permission['tokenizer'] ?? false,
                'XML Extension' => $permission['xml'] ?? false,
                'Fileinfo Extension' => $permission['fileinfo'] ?? false,
                'GD Extension' => $permission['gd'] ?? false,
                '.env File Writable' => $permission['db_file_write_perm'] ?? false,
                'Storage Writable' => $permission['storage_write_perm'] ?? false,
            ];
            $allPassed = !in_array(false, $requirements);
        @endphp

        @foreach($requirements as $name => $passed)
            <div class="flex items-center justify-between p-3 {{ $passed ? 'bg-green-50' : 'bg-red-50' }} rounded-lg">
                <span class="text-gray-700">{{ $name }}</span>
                @if($passed)
                    <span class="flex items-center text-green-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </span>
                @else
                    <span class="flex items-center text-red-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </span>
                @endif
            </div>
        @endforeach
    </div>

    <div class="flex justify-between">
        <a href="{{ route('step0') }}" class="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition">
            Back
        </a>
        @if($allPassed)
            <a href="{{ route('step3', ['token' => bcrypt('step_3')]) }}"
               class="inline-flex items-center px-8 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-all shadow-lg shadow-primary-600/30">
                Continue
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        @else
            <button disabled class="px-8 py-3 bg-gray-300 text-gray-500 font-semibold rounded-xl cursor-not-allowed">
                Fix Requirements First
            </button>
        @endif
    </div>
</div>
@endsection
