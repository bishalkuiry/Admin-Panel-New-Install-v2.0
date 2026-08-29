@extends('admin.layouts.app')
@section('title', 'Import Products')
@section('content')
<div class="space-y-5">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ request('type') === 'seller' ? route('admin.products.seller') : route('admin.products.index') }}" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Import {{ request('type') === 'seller' ? 'Seller' : 'In-house' }} Products</h1>
                <p class="text-sm text-gray-500 mt-1">Bulk upload {{ request('type') === 'seller' ? 'seller-managed' : 'in-house' }} products using Excel</p>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
        {{ session('error') }}
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Import Form -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Upload File</h3>
                <p class="text-sm text-gray-500 mt-1">Select your Excel file to import</p>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.products.import.post') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-indigo-500 transition-colors bg-gray-50">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <label class="block">
                            <span class="sr-only">Choose file</span>
                            <input type="file" name="file" class="block w-full text-sm text-gray-500
                              file:mr-4 file:py-2 file:px-4
                              file:rounded-full file:border-0
                              file:text-sm file:font-semibold
                              file:bg-indigo-50 file:text-indigo-700
                              hover:file:bg-indigo-100
                            " accept=".csv, .xlsx, .xls" required />
                        </label>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="btn-primary w-full justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            Start Import
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Instructions -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Instructions</h3>
                <p class="text-sm text-gray-500 mt-1">Please follow these steps</p>
            </div>
            <div class="card-body space-y-4">
                <ul class="list-disc list-inside space-y-2 text-sm text-gray-600">
                    <li>Download the <strong>sample data file</strong> to understand formatting.</li>
                    <li>The sample includes two sheets: <strong>Normal Products</strong> and <strong>Variant Products</strong>.</li>
                    <li>For <strong>Images</strong>: Upload images to the Media Manager first, then copy the URLs.</li>
                    <li>Do not change the header row names.</li>
                    <li>Categories and Brands will be created if they don't exist.</li>
                    @if(request('type') === 'seller')
                    <li class="text-orange-600 font-medium">For Seller Products: Ensure the <strong>store_id</strong> column is filled with the correct Store ID.</li>
                    @else
                    <li class="text-blue-600 font-medium">For In-house Products: Leave the <strong>store_id</strong> column empty.</li>
                    @endif
                </ul>

                <div class="pt-4">
                    <a href="{{ route('admin.products.sample') }}" class="btn-secondary w-full justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Download Sample Data (ZIP)
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
