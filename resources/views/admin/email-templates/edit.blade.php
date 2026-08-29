@extends('admin.layouts.app')
@section('title', 'Edit Template: ' . $emailTemplate->name)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Edit Template: {{ $emailTemplate->name }}</h1>
        <a href="{{ route('admin.email-templates.index') }}" class="btn-secondary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Templates
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="card p-6">
                <form action="{{ route('admin.email-templates.update', $emailTemplate) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Subject Line</label>
                            <input type="text" name="subject" value="{{ old('subject', $emailTemplate->subject) }}" class="input w-full" required>
                            <p class="text-xs text-gray-500 mt-1">You can use placeholders in the subject line too.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email Body (HTML)</label>
                            <div class="mb-2 p-3 bg-blue-50 text-blue-800 text-sm rounded-lg border border-blue-100">
                                <p><strong>Tip:</strong> Use standard HTML/CSS for emails. Inline styles work best.</p>
                            </div>
                            <textarea id="email-editor" name="body" rows="25" class="input w-full" required>{{ old('body', $emailTemplate->body) }}</textarea>
                        </div>

                        <div class="flex items-center gap-3 pt-4 border-t">
                            <button type="submit" class="btn-primary">Save Changes</button>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" {{ $emailTemplate->is_active ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-medium text-gray-700">Template Active</span>
                            </label>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card p-6 bg-gray-50 border border-gray-200">
                <h3 class="font-bold text-gray-900 mb-4">Available Placeholders</h3>
                <p class="text-sm text-gray-600 mb-4">Click to insert variable into editor.</p>
                
                @if($emailTemplate->placeholders)
                <div class="space-y-2">
                    @foreach($emailTemplate->placeholders as $placeholder)
                    <button type="button" onclick="insertPlaceholder('{{ $placeholder }}')" class="flex items-center justify-between w-full p-2 bg-white border border-gray-200 rounded hover:bg-indigo-50 hover:border-indigo-200 transition-colors group">
                        <code class="text-xs text-indigo-600 font-mono">{{ $placeholder }}</code>
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </button>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-gray-500 italic">No specific placeholders defined for this template.</p>
                @endif
            </div>

            <div class="card p-6">
                <h3 class="font-bold text-gray-900 mb-4">Live Preview</h3>
                <div class="border border-gray-200 rounded-lg overflow-hidden h-64 relative bg-gray-100">
                    <iframe id="preview-frame" class="w-full h-full bg-white" srcdoc="{{ $emailTemplate->body }}"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- TinyMCE Editor --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        tinymce.init({
            selector: '#email-editor',
            height: 600,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | blocks | ' +
                'bold italic backcolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'removeformat | help',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
            setup: function(editor) {
                editor.on('Change KeyUp', function(e) {
                    // Update the preview frame
                    const content = editor.getContent();
                    document.getElementById('preview-frame').srcdoc = content;
                });
            }
        });
    });

    function insertPlaceholder(placeholder) {
        if (tinymce.activeEditor) {
            tinymce.activeEditor.insertContent(placeholder);
        }
    }
</script>
@endsection
