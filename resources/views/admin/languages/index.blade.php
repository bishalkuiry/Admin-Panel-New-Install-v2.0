@extends('admin.layouts.app')

@section('title', 'Multi-Language & RTL Management')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Multi-Language & Direction Management</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">Configure unlimited languages, LTR/RTL text directions, and default system language</p>
        </div>
        <button type="button" @click="$dispatch('open-add-lang-modal')" class="btn btn-primary text-xs flex items-center justify-center gap-1.5 w-full sm:w-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Add New Language</span>
        </button>
    </div>

    @if(session('success'))
        <div class="p-3 bg-green-50 text-green-700 text-xs rounded-xl font-bold border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="card overflow-hidden p-4 sm:p-5">
        <div class="overflow-x-auto">
            <table class="table w-full text-xs min-w-[600px]">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Language Name</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">ISO Code</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Text Direction</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Default System</th>
                        <th class="text-right py-3 px-4 font-bold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($languages as $lang)
                        <tr class="hover:bg-gray-50/50">
                            <td class="py-3 px-4 font-bold text-gray-900">{{ $lang->name }}</td>
                            <td class="py-3 px-4 font-mono uppercase font-bold text-orange-600">{{ $lang->code }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $lang->is_rtl ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ $lang->is_rtl ? 'RTL (Right to Left)' : 'LTR (Left to Right)' }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                @if($lang->is_default)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700 uppercase">Default</span>
                                @else
                                    <form action="{{ route('admin.languages.default', $lang->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary text-[10px] py-0.5 px-2">Set Default</button>
                                    </form>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <form action="{{ route('admin.languages.toggle-rtl', $lang->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary text-[10px] py-0.5 px-2">
                                            Toggle {{ $lang->is_rtl ? 'LTR' : 'RTL' }}
                                        </button>
                                    </form>
                                    @if(!$lang->is_default)
                                        <form action="{{ route('admin.languages.destroy', $lang->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this language?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 p-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Language Modal -->
<div x-data="{ open: false }" @open-add-lang-modal.window="open = true" x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50" @click="open = false"></div>
        <div class="relative bg-white rounded-2xl max-w-md w-full p-5 sm:p-6 shadow-xl space-y-4 text-xs">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="font-bold text-sm text-gray-900">Add New Language</h3>
                <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('admin.languages.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="label font-bold">Language Name</label>
                    <input type="text" name="name" class="input text-xs" required placeholder="e.g. French (Français) / German (Deutsch)">
                </div>
                <div>
                    <label class="label font-bold">ISO Language Code</label>
                    <input type="text" name="code" class="input text-xs font-mono" required placeholder="e.g. fr / de / ru / zh">
                </div>
                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="is_rtl" value="1" id="is_rtl_check" class="rounded text-orange-500">
                    <label for="is_rtl_check" class="font-bold text-gray-800">RTL (Right-to-Left Text Direction)</label>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t">
                    <button type="button" @click="open = false" class="btn btn-secondary text-xs">Cancel</button>
                    <button type="submit" class="btn btn-primary text-xs">Add Language</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
