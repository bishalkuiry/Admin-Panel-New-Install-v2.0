@extends('admin.layouts.app')
@section('title', 'Add Category')
@section('content')
<div class="max-w-4xl">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.categories.index') }}" class="w-10 h-10 flex items-center justify-center rounded-lg bg-white border border-gray-200 text-gray-500 hover:bg-gray-50 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Add New Category</h1>
            <p class="text-sm text-gray-500">Create a new product category</p>
        </div>
    </div>

    <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Info -->
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Basic Information</h3></div>
                    <div class="card-body space-y-4">
                        <div>
                            <label class="label">Category Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" class="input" required placeholder="e.g., Fruits & Vegetables">
                        </div>
                        <div>
                            <label class="label">Description</label>
                            <textarea name="description" rows="4" class="input" placeholder="Brief description of this category...">{{ old('description') }}</textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="label">Parent Category</label>
                                <select name="parent_id" class="input">
                                    <option value="">None (Top Level)</option>
                                    @foreach($parentCategories as $parent)
                                    <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                                    @endforeach
                                </select>
                                <p class="form-hint">Leave empty for top-level category</p>
                            </div>
                            <div>
                                <label class="label">Sort Order</label>
                                <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" class="input" min="0">
                                <p class="form-hint">Lower numbers appear first</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Category Image -->
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Category Image</h3></div>
                    <div class="card-body" x-data="{ preview: null }">
                        <label class="upload-zone block cursor-pointer">
                            <template x-if="!preview">
                                <div>
                                    <svg class="w-10 h-10 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <p class="text-sm font-medium text-gray-700 mt-2">Click to upload image</p>
                                    <p class="text-xs text-gray-500 mt-1">PNG, JPG up to 2MB</p>
                                </div>
                            </template>
                            <template x-if="preview">
                                <img :src="preview" class="max-h-40 mx-auto rounded-lg">
                            </template>
                            <input type="file" name="image" accept="image/*" class="hidden" @change="preview = URL.createObjectURL($event.target.files[0])">
                        </label>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Status</h3></div>
                    <div class="card-body space-y-3">
                        <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition">
                            <input type="checkbox" name="is_active" value="1" checked class="checkbox">
                            <div>
                                <p class="font-medium text-gray-900 text-sm">Active</p>
                                <p class="text-xs text-gray-500">Category visible to customers</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition">
                            <input type="checkbox" name="is_featured" value="1" class="checkbox">
                            <div>
                                <p class="font-medium text-gray-900 text-sm">Featured</p>
                                <p class="text-xs text-gray-500">Show on homepage</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="flex gap-3">
                    <a href="{{ route('admin.categories.index') }}" class="btn-secondary flex-1 justify-center">Cancel</a>
                    <button type="submit" class="btn-primary flex-1 justify-center">Create Category</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
