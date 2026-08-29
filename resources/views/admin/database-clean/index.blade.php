@extends('admin.layouts.app')
@section('title', 'Database Clean')

@section('content')
<div class="page-header flex items-center justify-between">
    <div>
        <h1 class="page-title">Database Clean</h1>
        <p class="page-subtitle">Select tables to clean. This will permanently delete all records from the selected tables.</p>
    </div>
    <button type="button" onclick="submitClean()" id="cleanBtn" disabled
        class="btn-primary opacity-50 cursor-not-allowed transition-all"
        style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        Clean Selected (<span id="selectedCount">0</span>)
    </button>
</div>

<form id="cleanForm" method="POST" action="{{ route('admin.database-clean.clean') }}">
    @csrf

    <!-- Warning Banner -->
    <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
        <div>
            <p class="text-sm font-semibold text-amber-800">Warning: Destructive Operation</p>
            <p class="text-xs text-amber-600 mt-0.5">Cleaning a table will permanently remove all its records. This action cannot be undone. Make sure you have a backup before proceeding.</p>
        </div>
    </div>

    @foreach($tableData as $groupName => $tables)
    <div class="card mb-6 animate-fade-in">
        <div class="card-header">
            <div class="flex items-center gap-3">
                <h3 class="card-title">{{ $groupName }}</h3>
                <span class="badge badge-gray text-xs">{{ count($tables) }} tables</span>
            </div>
            <label class="flex items-center gap-2 cursor-pointer text-xs text-gray-500 hover:text-orange-600 transition-colors select-none">
                <input type="checkbox" class="group-toggle checkbox" data-group="{{ Str::slug($groupName) }}" onchange="toggleGroup(this)">
                Select All
            </label>
        </div>
        <div class="p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($tables as $tableName => $info)
                <label class="relative flex items-start gap-3 p-3 rounded-lg border border-gray-100 hover:border-orange-200 hover:bg-orange-50/30 transition-all cursor-pointer group {{ !$info['exists'] ? 'opacity-40 pointer-events-none' : '' }}"
                       data-group="{{ Str::slug($groupName) }}">
                    <input type="checkbox" name="tables[]" value="{{ $tableName }}"
                           class="table-checkbox checkbox mt-0.5" data-group="{{ Str::slug($groupName) }}"
                           onchange="updateCount()"
                           {{ !$info['exists'] ? 'disabled' : '' }}>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-base">{{ $info['icon'] }}</span>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900">{{ $info['label'] }}</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $info['description'] }}</p>
                        <div class="flex items-center gap-2 mt-1.5">
                            <span class="text-[11px] font-mono px-1.5 py-0.5 rounded bg-gray-100 text-gray-500">{{ $tableName }}</span>
                            @if($info['exists'])
                                @if($info['count'] > 0)
                                    <span class="badge badge-blue text-[11px] px-1.5 py-0.5">{{ number_format($info['count']) }} records</span>
                                @else
                                    <span class="badge badge-green text-[11px] px-1.5 py-0.5">Empty</span>
                                @endif
                            @else
                                <span class="badge badge-red text-[11px] px-1.5 py-0.5">Not found</span>
                            @endif
                        </div>
                    </div>
                </label>
                @endforeach
            </div>
        </div>
    </div>
    @endforeach
</form>

@push('scripts')
<script>
function updateCount() {
    const checked = document.querySelectorAll('.table-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = checked;
    const btn = document.getElementById('cleanBtn');
    if (checked > 0) {
        btn.disabled = false;
        btn.classList.remove('opacity-50', 'cursor-not-allowed');
    } else {
        btn.disabled = true;
        btn.classList.add('opacity-50', 'cursor-not-allowed');
    }

    // Update group toggle states
    document.querySelectorAll('.group-toggle').forEach(toggle => {
        const group = toggle.dataset.group;
        const all = document.querySelectorAll(`.table-checkbox[data-group="${group}"]:not(:disabled)`);
        const checkedInGroup = document.querySelectorAll(`.table-checkbox[data-group="${group}"]:checked`);
        toggle.checked = all.length > 0 && all.length === checkedInGroup.length;
        toggle.indeterminate = checkedInGroup.length > 0 && checkedInGroup.length < all.length;
    });
}

function toggleGroup(el) {
    const group = el.dataset.group;
    const checkboxes = document.querySelectorAll(`.table-checkbox[data-group="${group}"]:not(:disabled)`);
    checkboxes.forEach(cb => cb.checked = el.checked);
    updateCount();
}

function submitClean() {
    const checked = document.querySelectorAll('.table-checkbox:checked');
    if (checked.length === 0) return;

    const names = Array.from(checked).map(cb => cb.value).join(', ');
    const msg = `⚠️ WARNING: You are about to permanently delete ALL records from the following ${checked.length} table(s):\n\n${names}\n\nThis action CANNOT be undone!\n\nAre you sure you want to proceed?`;

    if (confirm(msg)) {
        document.getElementById('cleanForm').submit();
    }
}
</script>
@endpush
@endsection
