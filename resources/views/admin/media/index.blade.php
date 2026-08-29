@extends('admin.layouts.app')

@section('title', 'Media Library')

@section('content')
<div class="page-header flex justify-between items-center">
    <div>
        <h1 class="page-title">Media Library</h1>
        <p class="page-subtitle">Manage local uploaded images, videos, and files</p>
    </div>
    <div class="flex gap-3">
        <select onchange="filterMedia(this.value)" class="input w-auto">
            <option value="all" {{ $type === 'all' ? 'selected' : '' }}>All Files</option>
            <option value="images" {{ $type === 'images' ? 'selected' : '' }}>Images</option>
            <option value="videos" {{ $type === 'videos' ? 'selected' : '' }}>Videos</option>
            <option value="audio" {{ $type === 'audio' ? 'selected' : '' }}>Audio</option>
            <option value="files" {{ $type === 'files' ? 'selected' : '' }}>Documents</option>
        </select>
        <x-permission-btn 
            permission="media.create" 
            type="button"
            onclick="document.getElementById('fileUpload').click()"
            class="btn-primary" 
            label="Upload Files"
            icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>'
        />
        <input type="file" multiple class="hidden" id="fileUpload" onchange="uploadFiles(this.files)">
    </div>
</div>

<!-- Upload Progress -->
<div id="uploadProgress" class="hidden mb-4">
    <div class="card p-4">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-orange-500 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span id="uploadStatus">Uploading...</span>
        </div>
    </div>
</div>

<div class="card">
    @if(count($files) > 0)
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 p-4">
        @foreach($files as $file)
        <div class="group relative bg-gray-50 rounded-lg overflow-hidden border border-gray-200 hover:border-orange-300 transition" data-url="{{ $file['url'] }}">
            <div class="aspect-square flex items-center justify-center p-2">
                @if($file['type'] === 'images')
                    <img src="{{ $file['url'] }}" alt="{{ $file['name'] }}" class="max-w-full max-h-full object-contain rounded">
                @elseif($file['type'] === 'videos')
                    <div class="w-full h-full flex flex-col items-center justify-center bg-black rounded relative overflow-hidden">
                        <video src="{{ $file['url'] }}" class="w-full h-full object-cover" controls preload="metadata"></video>
                        <span class="text-[10px] text-white bg-black/70 px-1.5 py-0.5 rounded absolute top-1 left-1 truncate max-w-[80%]">{{ $file['name'] }}</span>
                    </div>
                @elseif($file['type'] === 'audio')
                    <div class="text-center w-full px-2">
                        <svg class="w-10 h-10 text-orange-500 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>
                        <audio src="{{ $file['url'] }}" controls class="w-full text-xs"></audio>
                    </div>
                @else
                    <div class="text-center">
                        <svg class="w-12 h-12 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span class="text-xs text-gray-500 mt-1 block truncate px-2">{{ $file['name'] }}</span>
                    </div>
                @endif
            </div>
            
            <!-- Hover Actions -->
            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-2 pointer-events-none group-hover:pointer-events-auto">
                <button onclick="copyUrl('{{ $file['url'] }}')" class="p-2 bg-white rounded-lg hover:bg-gray-100 transition pointer-events-auto" title="Copy URL">
                    <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                </button>
                <a href="{{ $file['url'] }}" target="_blank" class="p-2 bg-white rounded-lg hover:bg-gray-100 transition pointer-events-auto" title="View">
                    <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
                <x-permission-btn 
                    permission="media.delete" 
                    type="button"
                    onclick="deleteFile('{{ $file['url'] }}')"
                    class="p-2 bg-red-500 rounded-lg hover:bg-red-600 transition pointer-events-auto" 
                    label=""
                    icon='<svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>'
                    title="Delete"
                />
            </div>
            
            <!-- File Size -->
            <div class="absolute bottom-1 right-1 text-xs text-white bg-black/50 px-1.5 py-0.5 rounded pointer-events-none">
                {{ number_format($file['size'] / 1024, 0) }}KB
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="empty-state">
        <div class="empty-icon">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <h3 class="empty-title">No media files yet</h3>
        <p class="empty-text">Upload images, videos, or documents to get started</p>
        <x-permission-btn 
            permission="media.create" 
            type="button"
            onclick="document.getElementById('fileUploadEmpty').click()"
            class="btn-primary inline-flex" 
            label="Upload Files"
        />
        <input type="file" multiple class="hidden" id="fileUploadEmpty" onchange="uploadFiles(this.files)">
    </div>
    @endif
</div>

<!-- Toast Notification -->
<div id="toast" class="fixed bottom-4 right-4 bg-gray-900 text-white px-4 py-2 rounded-lg shadow-lg transform translate-y-20 opacity-0 transition-all duration-300 z-50">
    <span id="toastMessage"></span>
</div>

<script>
function filterMedia(type) {
    window.location.href = '{{ route("admin.media.index") }}?type=' + type;
}

function copyUrl(url) {
    const fullUrl = window.location.origin + url;
    navigator.clipboard.writeText(fullUrl).then(() => {
        showToast('URL copied to clipboard!');
    });
}

function showToast(message) {
    const toast = document.getElementById('toast');
    document.getElementById('toastMessage').textContent = message;
    toast.classList.remove('translate-y-20', 'opacity-0');
    setTimeout(() => {
        toast.classList.add('translate-y-20', 'opacity-0');
    }, 3000);
}

async function uploadFiles(files) {
    const progress = document.getElementById('uploadProgress');
    const status = document.getElementById('uploadStatus');
    progress.classList.remove('hidden');
    
    for (let i = 0; i < files.length; i++) {
        status.textContent = `Uploading ${i + 1} of ${files.length}...`;
        
        const formData = new FormData();
        formData.append('file', files[i]);
        formData.append('_token', '{{ csrf_token() }}');
        
        try {
            await fetch('{{ route("admin.media.upload") }}', {
                method: 'POST',
                body: formData
            });
        } catch (error) {
            console.error('Upload failed:', error);
        }
    }
    
    progress.classList.add('hidden');
    showToast('Upload complete!');
    window.location.reload();
}

async function deleteFile(url) {
    if (!confirm('Delete this file?')) return;
    
    try {
        const response = await fetch('{{ route("admin.media.destroy") }}', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ path: url })
        });
        
        if (response.ok) {
            showToast('File deleted');
            window.location.reload();
        } else {
            showToast('Failed to delete file');
        }
    } catch (error) {
        showToast('Error deleting file');
    }
}
</script>
@endsection
