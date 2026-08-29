<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Media</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .selected { border: 3px solid #f97316 !important; background: #fff7ed; }
    </style>
</head>
<body class="bg-gray-50">
    <div x-data="mediaSelector()" class="h-screen flex flex-col">
        <!-- Header -->
        <div class="bg-white border-b border-gray-200 px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Select Media</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Click files to select • <span x-text="selectedImages.length"></span> selected</p>
                </div>
                <div class="flex items-center gap-3">
                    <select x-model="filterFolder" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="all">All Folders</option>
                        @foreach($files as $file)
                            @if(!in_array($file['folder'], $folders ?? []))
                                @php $folders[] = $file['folder']; @endphp
                            @endif
                        @endforeach
                        @foreach($folders ?? [] as $folder)
                            <option value="{{ $folder }}">{{ ucfirst($folder) }}</option>
                        @endforeach
                    </select>
                    <label class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium cursor-pointer hover:bg-gray-200 transition">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Upload
                        <input type="file" multiple class="hidden" @change="uploadFiles($event.target.files)">
                    </label>
                </div>
            </div>
        </div>

        <!-- Media Grid -->
        <div class="flex-1 overflow-y-auto p-6">
            @if(count($files) > 0)
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-4">
                @foreach($files as $file)
                <div x-show="filterFolder === 'all' || filterFolder === '{{ $file['folder'] }}'"
                     @click="toggleSelect('{{ $file['url'] }}')"
                     :class="selectedImages.includes('{{ $file['url'] }}') ? 'selected' : ''"
                     class="relative group bg-white rounded-lg overflow-hidden border-2 border-gray-200 hover:border-orange-300 cursor-pointer transition aspect-square flex items-center justify-center p-1">
                    @if(($file['type'] ?? 'images') === 'videos')
                        <div class="w-full h-full bg-black flex items-center justify-center rounded overflow-hidden relative">
                            <video src="{{ $file['url'] }}" class="w-full h-full object-cover" preload="metadata"></video>
                            <span class="text-[10px] text-white bg-black/70 px-1 py-0.5 rounded absolute bottom-1 left-1 truncate max-w-[90%]">{{ $file['name'] }}</span>
                        </div>
                    @elseif(($file['type'] ?? 'images') === 'audio')
                        <div class="text-center p-2">
                            <svg class="w-10 h-10 text-orange-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>
                            <span class="text-xs text-gray-600 truncate block mt-1">{{ $file['name'] }}</span>
                        </div>
                    @else
                        <img src="{{ $file['url'] }}" alt="{{ $file['name'] }}" class="w-full h-full object-cover rounded">
                    @endif
                    
                    <!-- Selection Indicator -->
                    <div x-show="selectedImages.includes('{{ $file['url'] }}')" 
                         class="absolute top-2 right-2 w-6 h-6 bg-orange-500 rounded-full flex items-center justify-center z-10">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    
                    <!-- Folder Badge -->
                    <div class="absolute bottom-2 left-2 px-2 py-0.5 bg-black/60 text-white text-xs rounded z-10">
                        {{ $file['folder'] }}
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-20">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No media files found</h3>
                <p class="text-sm text-gray-500 mb-4">Upload files to get started</p>
                <label class="inline-flex items-center px-4 py-2 bg-orange-500 text-white rounded-lg cursor-pointer hover:bg-orange-600 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Upload Media
                    <input type="file" multiple class="hidden" @change="uploadFiles($event.target.files)">
                </label>
            </div>
            @endif
        </div>

        <!-- Footer Actions -->
        <div class="bg-white border-t border-gray-200 px-6 py-4">
            <div class="flex items-center justify-between">
                <button @click="clearSelection()" 
                        x-show="selectedImages.length > 0"
                        class="text-sm text-gray-600 hover:text-gray-900">
                    Clear Selection
                </button>
                <div class="flex items-center gap-3">
                    <button @click="window.parent.postMessage({action: 'cancel'}, '*')" 
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button @click="confirmSelection()" 
                            :disabled="selectedImages.length === 0"
                            :class="selectedImages.length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-orange-600'"
                            class="px-4 py-2 bg-orange-500 text-white rounded-lg text-sm font-medium transition">
                        Add <span x-text="selectedImages.length"></span> Item<span x-show="selectedImages.length !== 1">s</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Upload Progress -->
        <div x-show="uploading" 
             x-transition
             class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-sm w-full mx-4">
                <div class="flex items-center gap-3 mb-3">
                    <svg class="w-6 h-6 text-orange-500 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="font-medium text-gray-900" x-text="uploadStatus"></span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-orange-500 h-2 rounded-full transition-all" :style="`width: ${uploadProgress}%`"></div>
                </div>
            </div>
        </div>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        function mediaSelector() {
            return {
                selectedImages: [],
                filterFolder: 'all',
                uploading: false,
                uploadStatus: '',
                uploadProgress: 0,
                
                toggleSelect(url) {
                    const index = this.selectedImages.indexOf(url);
                    if (index > -1) {
                        this.selectedImages.splice(index, 1);
                    } else {
                        this.selectedImages.push(url);
                    }
                },
                
                clearSelection() {
                    this.selectedImages = [];
                },
                
                confirmSelection() {
                    if (this.selectedImages.length === 0) return;
                    window.parent.postMessage({
                        action: 'select',
                        urls: this.selectedImages
                    }, '*');
                },
                
                async uploadFiles(files) {
                    this.uploading = true;
                    for (let i = 0; i < files.length; i++) {
                        this.uploadStatus = `Uploading ${i + 1} of ${files.length}...`;
                        this.uploadProgress = ((i + 1) / files.length) * 100;
                        
                        const formData = new FormData();
                        formData.append('file', files[i]);
                        formData.append('_token', '{{ csrf_token() }}');
                        
                        try {
                            const res = await fetch('{{ route("admin.media.upload") }}', {
                                method: 'POST',
                                body: formData
                            });
                            const data = await res.json();
                            if (data.location) {
                                this.selectedImages.push(data.location);
                            }
                        } catch (e) {
                            console.error(e);
                        }
                    }
                    this.uploading = false;
                    window.location.reload();
                }
            }
        }
    </script>
</body>
</html>
