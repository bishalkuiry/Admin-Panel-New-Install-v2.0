@extends('admin.layouts.app')

@section('title', 'Upload Plugin')

@section('content')
<div class="space-y-8">
    <!-- Header with Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-5">
            <a href="{{ route('admin.plugins.index') }}" class="w-12 h-12 rounded-2xl bg-white shadow-sm flex items-center justify-center text-gray-400 hover:text-gray-900 border border-gray-100 transition-all hover:scale-105 active:scale-95">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 leading-tight">Install Module</h1>
                <p class="text-sm text-gray-500 font-medium mt-1">Expand your system capabilities with new plugins</p>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.plugins.index') }}" class="h-12 px-6 flex items-center text-sm font-bold text-gray-500 hover:text-gray-900 transition-colors">
                Cancel
            </a>
            <button type="submit" form="uploadForm" class="h-12 px-8 bg-indigo-600 text-white rounded-2xl font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-100 active:scale-95 transition-all flex items-center gap-2" id="submitBtn">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Install Now
            </button>
        </div>
    </div>

    @if(session('error'))
    <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-2xl flex gap-3 items-center animate-fade-in-down">
        <svg class="w-5 h-5 flex-shrink-0 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span class="text-sm font-medium">{{ session('error') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="p-6 bg-red-50 border border-red-200 text-red-800 rounded-3xl animate-fade-in-down">
        <div class="flex gap-3 items-center mb-4">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-sm font-bold uppercase tracking-wider">Installation Requirements Failed</span>
        </div>
        <ul class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2 list-none ml-9">
            @foreach($errors->all() as $error)
            <li class="text-xs font-bold text-red-600/80 flex items-center gap-2 tracking-tight">
                <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
                {{ $error }}
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="flex justify-center">
        <div class="w-full max-w-4xl">
            <form method="POST" action="{{ route('admin.plugins.store') }}" enctype="multipart/form-data" id="uploadForm" class="space-y-8">
                @csrf
                
                <div class="card bg-white border-0 shadow-sm overflow-hidden p-10">
                    <div class="space-y-2 mb-8">
                        <h2 class="text-lg font-bold text-gray-900 tracking-tight">Source Selection</h2>
                        <p class="text-sm text-gray-500 font-medium">Select a validated ZIP package from your workstation</p>
                    </div>

                    <div class="relative">
                        <div class="upload-zone group border-3 border-dashed border-gray-100 rounded-[32px] p-16 transition-all duration-500 hover:border-indigo-400 hover:bg-indigo-50/20 cursor-pointer text-center relative overflow-hidden" id="dropZone">
                            <input type="file" name="plugin_file" id="pluginFile" accept=".zip" required class="hidden">
                            <div class="absolute inset-0 bg-indigo-50 opacity-0 group-hover:opacity-5 transition-opacity pointer-events-none"></div>
                            
                            <div id="dropZoneContent" class="space-y-6 relative z-10">
                                <div class="w-24 h-24 bg-indigo-50 rounded-3xl flex items-center justify-center mx-auto transition-all group-hover:scale-110 group-hover:bg-indigo-100 duration-500 shadow-sm">
                                    <svg class="w-12 h-12 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                </div>
                                <div class="space-y-2">
                                    <p class="text-xl font-bold text-gray-900">Drop package here</p>
                                    <p class="text-gray-400 font-medium tracking-tight">Support only native .zip module archives</p>
                                </div>
                                <button type="button" class="h-12 px-6 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50 transition-all">
                                    Browse Filesystem
                                </button>
                            </div>

                            <div id="fileInfo" class="hidden space-y-6 relative z-10 animate-fade-in-down">
                                <div class="w-24 h-24 bg-green-50 rounded-3xl flex items-center justify-center mx-auto shadow-sm border border-green-100">
                                    <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-xl font-bold text-gray-900 truncate px-10" id="fileName"></p>
                                    <p class="text-green-600 font-bold text-xs uppercase tracking-widest" id="fileSize"></p>
                                </div>
                                <button type="button" onclick="clearFile(event)" class="text-sm font-bold text-indigo-600 hover:text-indigo-800 transition-colors">
                                    Reset Selection
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card bg-white border-0 shadow-sm p-10">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div class="space-y-1">
                            <h3 class="text-lg font-bold text-gray-900 tracking-tight">License Validation</h3>
                            <p class="text-sm text-gray-500 font-medium">Verify your ownership of this module</p>
                        </div>
                        <div class="flex-1 max-w-md">
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-300 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                    </svg>
                                </div>
                                <input type="text" name="license_key" class="w-full bg-gray-50 border border-gray-100 rounded-2xl pl-14 pr-5 py-4 text-gray-900 font-bold focus:ring-4 focus:ring-indigo-500/10 focus:bg-white focus:border-indigo-500 transition-all outline-none" placeholder="XXXX-XXXX-XXXX-XXXX" value="{{ old('license_key') }}">
                            </div>
                        </div>
                <div class="card bg-white border-0 shadow-sm p-10">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div class="space-y-1">
                            <h3 class="text-lg font-bold text-gray-900 tracking-tight">Existing Plugin Action</h3>
                            <p class="text-sm text-gray-500 font-medium">Choose behavior if this plugin package is already installed</p>
                        </div>
                        <div class="flex items-center gap-6">
                            <label class="flex items-center gap-3 cursor-pointer p-3 rounded-xl border border-gray-200 hover:bg-gray-50 transition-all">
                                <input type="radio" name="overwrite" value="0" checked class="w-4 h-4 text-indigo-600 focus:ring-indigo-500">
                                <div>
                                    <span class="text-sm font-bold text-gray-800">Skip / Prevent Overwrite</span>
                                    <p class="text-xs text-gray-400">Stop installation if plugin exists</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer p-3 rounded-xl border border-indigo-200 bg-indigo-50/30 hover:bg-indigo-50 transition-all">
                                <input type="radio" name="overwrite" value="1" class="w-4 h-4 text-indigo-600 focus:ring-indigo-500">
                                <div>
                                    <span class="text-sm font-bold text-indigo-700">Replace &amp; Overwrite</span>
                                    <p class="text-xs text-indigo-500">Update files &amp; keep existing settings</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </form>
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
.upload-zone {
    background-image: url("data:image/svg+xml,%3csvg width='100%25' height='100%25' xmlns='http://www.w3.org/2000/svg'%3e%3crect width='100%25' height='100%25' fill='none' rx='32' ry='32' stroke='%23F1F5F9' stroke-width='4' stroke-dasharray='16%2c 16' stroke-dashoffset='0' stroke-linecap='square'/%3e%3c/svg%3e");
    border: none;
}
.upload-zone:hover, .upload-zone.dragover {
    background-image: url("data:image/svg+xml,%3csvg width='100%25' height='100%25' xmlns='http://www.w3.org/2000/svg'%3e%3crect width='100%25' height='100%25' fill='none' rx='32' ry='32' stroke='%23818CF8' stroke-width='4' stroke-dasharray='16%2c 16' stroke-dashoffset='0' stroke-linecap='square'/%3e%3c/svg%3e");
}
</style>

<script>
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('pluginFile');
    const dropZoneContent = document.getElementById('dropZoneContent');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const submitBtn = document.getElementById('submitBtn');

    dropZone.onclick = (e) => {
        if(e.target.tagName !== 'BUTTON' && !e.target.closest('button')) {
            fileInput.click();
        }
    };
    
    // Explicit browse button handler
    const browseBtn = dropZone.querySelector('button');
    if(browseBtn) {
        browseBtn.onclick = (e) => {
            e.stopPropagation();
            fileInput.click();
        };
    }

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, e => {
            e.preventDefault();
            e.stopPropagation();
        });
    });

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => dropZone.classList.add('dragover', 'bg-indigo-50/20'));
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => dropZone.classList.remove('dragover', 'bg-indigo-50/20'));
    });

    dropZone.addEventListener('drop', (e) => {
        const files = e.dataTransfer.files;
        if (files.length) {
            fileInput.files = files;
            updateFileInfo(files[0]);
        }
    });

    fileInput.onchange = (e) => {
        if (e.target.files.length) updateFileInfo(e.target.files[0]);
    };

    function updateFileInfo(file) {
        if (!file.name.toLowerCase().endsWith('.zip')) {
            alert('Selection Restricted: Only .ZIP archives are permitted for installation.');
            clearFile();
            return;
        }
        fileName.textContent = file.name;
        fileSize.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
        dropZoneContent.classList.add('hidden');
        fileInfo.classList.remove('hidden');
    }

    function clearFile(e) {
        if(e) e.stopPropagation();
        fileInput.value = '';
        dropZoneContent.classList.remove('hidden');
        fileInfo.classList.add('hidden');
    }

    document.getElementById('uploadForm').onsubmit = () => {
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
        submitBtn.innerHTML = `
            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Processing...
        `;
    };
</script>
@endsection
