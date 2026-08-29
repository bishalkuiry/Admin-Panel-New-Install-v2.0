@extends('admin.layouts.app')
@section('title', 'Add Attribute')
@section('content')
<div class="max-w-4xl">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.attributes.index') }}" class="w-10 h-10 flex items-center justify-center rounded-lg bg-white border border-gray-200 text-gray-500 hover:bg-gray-50 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Add New Attribute</h1>
            <p class="text-sm text-gray-500">Create a new product attribute</p>
        </div>
    </div>

    <form action="{{ route('admin.attributes.store') }}" method="POST" x-data="attributeForm()">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Info -->
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Basic Information</h3></div>
                    <div class="card-body space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="label">Attribute Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" value="{{ old('name') }}" class="input" required placeholder="e.g., Size, Color">
                            </div>
                            <div>
                                <label class="label">Type <span class="text-red-500">*</span></label>
                                <select name="type" class="input" x-model="type">
                                    <option value="select">Select (Dropdown)</option>
                                    <option value="color">Color Picker</option>
                                    <option value="size">Size</option>
                                    <option value="text">Text</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="label">Sort Order</label>
                            <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" class="input w-32" min="0">
                            <p class="form-hint">Lower numbers appear first</p>
                        </div>
                    </div>
                </div>

                <!-- Attribute Values -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Attribute Values</h3>
                        <button type="button" @click="addValue()" class="btn-secondary btn-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Add Value
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="space-y-3">
                            <template x-for="(value, index) in values" :key="index">
                                <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg border border-gray-100">
                                    <div class="w-8 h-8 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-sm font-medium text-gray-500" x-text="index + 1"></div>
                                    <div class="flex-1">
                                        <input type="text" :name="'values[' + index + '][value]'" x-model="value.value" class="input" placeholder="Value name (e.g., Small, Red)" required>
                                    </div>
                                    <div x-show="type === 'color'" class="flex items-center gap-2">
                                        <div class="w-10 h-10 rounded-lg border border-gray-200 overflow-hidden">
                                            <input type="color" :name="'values[' + index + '][color_code]'" x-model="value.color_code" class="w-14 h-14 -m-2 cursor-pointer">
                                        </div>
                                        <span class="text-xs text-gray-500 font-mono" x-text="value.color_code"></span>
                                    </div>
                                    <button type="button" @click="removeValue(index)" class="action-btn action-btn-delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <div x-show="values.length === 0" class="text-center py-10">
                            <div class="empty-icon mx-auto">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
                            </div>
                            <p class="text-sm text-gray-500 mt-3">No values added yet</p>
                            <button type="button" @click="addValue()" class="btn-secondary btn-sm mt-3">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Add First Value
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Settings</h3></div>
                    <div class="card-body space-y-3">
                        <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition">
                            <input type="checkbox" name="is_filterable" value="1" checked class="checkbox">
                            <div>
                                <p class="font-medium text-gray-900 text-sm">Filterable</p>
                                <p class="text-xs text-gray-500">Show in product filters</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition">
                            <input type="checkbox" name="is_visible" value="1" checked class="checkbox">
                            <div>
                                <p class="font-medium text-gray-900 text-sm">Visible</p>
                                <p class="text-xs text-gray-500">Show on product page</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h3 class="card-title">Quick Tips</h3></div>
                    <div class="card-body">
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Use "Color" type for color swatches</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Use "Size" for S, M, L, XL options</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Enable "Filterable" for search filters</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="flex gap-3">
                    <a href="{{ route('admin.attributes.index') }}" class="btn-secondary flex-1 justify-center">Cancel</a>
                    <x-permission-btn 
                        permission="attributes.create" 
                        type="submit"
                        class="btn-primary flex-1 justify-center" 
                        label="Create Attribute"
                    />
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function attributeForm() {
    return {
        type: 'select',
        values: [],
        addValue() {
            this.values.push({ value: '', color_code: '#6366f1' });
        },
        removeValue(index) {
            this.values.splice(index, 1);
        }
    }
}
</script>
@endsection
