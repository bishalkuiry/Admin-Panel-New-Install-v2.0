@extends('admin.layouts.app')

@section('title', isset($staticPage) ? 'Edit Page' : 'Create Page')

@section('content')
<div class="max-w-6xl">
    <div class="page-header flex items-center gap-4">
        <a href="{{ route('admin.pages.index') }}" class="p-2 rounded-lg hover:bg-gray-100 transition">
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="page-title">{{ isset($staticPage) ? 'Edit Page' : 'Create Page' }}</h1>
            <p class="page-subtitle">{{ isset($staticPage) ? 'Update page content and settings' : 'Add a new static page to your website' }}</p>
        </div>
    </div>

    <form action="{{ isset($staticPage) ? route('admin.pages.update', $staticPage) : route('admin.pages.store') }}" method="POST" class="card">
        @csrf
        @if(isset($staticPage)) @method('PUT') @endif

        <div class="card-body space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="form-group">
                    <label for="title" class="label">Page Title</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $staticPage->title ?? '') }}" 
                           class="input @error('title') border-red-500 @enderror" 
                           placeholder="About Us" required>
                    @error('title') <p class="form-hint text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label for="slug" class="label">URL Slug</label>
                    <div class="flex">
                        <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-gray-200 bg-gray-50 text-gray-500 text-sm">/</span>
                        <input type="text" name="slug" id="slug" value="{{ old('slug', $staticPage->slug ?? '') }}" 
                               class="input rounded-l-none @error('slug') border-red-500 @enderror" 
                               placeholder="about-us" required>
                    </div>
                    @error('slug') <p class="form-hint text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label for="icon" class="label">Icon</label>
                        <input type="text" name="icon" id="icon" value="{{ old('icon', $staticPage->icon ?? '📄') }}" 
                               class="input @error('icon') border-red-500 @enderror" 
                               placeholder="📄" required maxlength="50">
                    </div>
                    <div class="form-group">
                        <label for="order" class="label">Order</label>
                        <input type="number" name="order" id="order" value="{{ old('order', $staticPage->order ?? 0) }}" 
                               class="input @error('order') border-red-500 @enderror" min="0" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="content" class="label">Page Content</label>
                <textarea name="content" id="content">{{ old('content', $staticPage->content ?? '') }}</textarea>
                @error('content') <p class="form-hint text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center">
                <label class="toggle">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $staticPage->is_active ?? true) ? 'checked' : '' }}>
                    <span class="toggle-slider"></span>
                </label>
                <span class="ml-3 text-sm text-gray-700">Page is active and visible</span>
            </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3 rounded-b-lg">
            <a href="{{ route('admin.pages.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">
                {{ isset($staticPage) ? 'Update Page' : 'Create Page' }}
            </button>
        </div>
    </form>
</div>

<!-- TinyMCE Editor - Free Self-hosted Version (No API Key Required) -->
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>
<script>
tinymce.init({
    selector: '#content',
    height: 600,
    license_key: 'gpl',
    plugins: [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'help', 'wordcount', 'emoticons'
    ],
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | ' +
             'alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | ' +
             'link image media table | forecolor backcolor removeformat | ' +
             'emoticons charmap | code fullscreen preview | help',
    toolbar_mode: 'sliding',
    menubar: 'file edit view insert format tools table help',
    
    // Image upload
    images_upload_url: '{{ route("admin.media.upload") }}',
    images_upload_credentials: true,
    automatic_uploads: true,
    images_reuse_filename: true,
    file_picker_types: 'image media',
    
    // File picker for media browser
    file_picker_callback: function(callback, value, meta) {
        // Open media browser
        const input = document.createElement('input');
        input.setAttribute('type', 'file');
        
        if (meta.filetype === 'image') {
            input.setAttribute('accept', 'image/*');
        } else if (meta.filetype === 'media') {
            input.setAttribute('accept', 'video/*,audio/*');
        }
        
        input.onchange = function() {
            const file = this.files[0];
            const formData = new FormData();
            formData.append('file', file);
            formData.append('_token', '{{ csrf_token() }}');
            
            fetch('{{ route("admin.media.upload") }}', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.location) {
                    callback(result.location, { title: file.name });
                }
            })
            .catch(error => {
                console.error('Upload failed:', error);
                alert('Upload failed. Please try again.');
            });
        };
        
        input.click();
    },
    
    // Styling
    content_style: `
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            font-size: 16px; 
            line-height: 1.6;
            padding: 20px;
            max-width: 100%;
        }
        img { max-width: 100%; height: auto; }
        table { border-collapse: collapse; width: 100%; }
        table td, table th { border: 1px solid #ddd; padding: 8px; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
        blockquote { border-left: 4px solid #f97316; margin: 20px 0; padding-left: 20px; color: #666; }
    `,
    
    // Allow all HTML
    valid_elements: '*[*]',
    extended_valid_elements: '*[*]',
    valid_children: '+body[style|script|link]',
    
    // Responsive images
    image_class_list: [
        { title: 'Responsive', value: 'img-fluid' },
        { title: 'Rounded', value: 'rounded-lg' },
        { title: 'Shadow', value: 'shadow-lg' },
        { title: 'Full Width', value: 'w-full' }
    ],
    
    // Link options
    link_class_list: [
        { title: 'None', value: '' },
        { title: 'Button Primary', value: 'btn-primary' },
        { title: 'Button Secondary', value: 'btn-secondary' }
    ],
    
    // Setup callback
    setup: function(editor) {
        editor.on('change', function() {
            editor.save();
        });
    }
});

// Auto-generate slug from title
document.getElementById('title').addEventListener('input', function() {
    const slug = this.value.toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim();
    document.getElementById('slug').value = slug;
});
</script>
@endsection
