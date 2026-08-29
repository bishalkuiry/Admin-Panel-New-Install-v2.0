@extends('admin.layouts.app')
@section('title', 'Customer Tax Rules')
@section('content')
<div class="space-y-5">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Customer Tax Rules</h1>
            <p class="text-sm text-gray-500 mt-1">Taxes added to customer orders at checkout</p>
        </div>
        <a href="{{ route('admin.taxes.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Tax Rule
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="table-header">Name</th>
                        <th class="table-header">Type</th>
                        <th class="table-header">Value</th>
                        <th class="table-header">Order Range</th>
                        <th class="table-header">Inclusive</th>
                        <th class="table-header">Status</th>
                        <th class="table-header text-center w-28">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($taxes as $tax)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="table-cell font-medium text-gray-900">{{ $tax->name }}</td>
                        <td class="table-cell">
                            <span class="badge badge-gray">{{ ucfirst($tax->calculation_type) }}</span>
                        </td>
                        <td class="table-cell font-semibold text-gray-900">{{ $tax->value_label }}</td>
                        <td class="table-cell text-sm text-gray-500">
                            @if($tax->min_order_value || $tax->max_order_value)
                                @if($tax->min_order_value) Min: {{ \App\Helpers\CurrencyHelper::format($tax->min_order_value) }} @endif
                                @if($tax->max_order_value) Max: {{ \App\Helpers\CurrencyHelper::format($tax->max_order_value) }} @endif
                            @else
                                <span class="text-gray-400">All orders</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            @if($tax->is_inclusive)
                                <span class="badge badge-green">Yes</span>
                            @else
                                <span class="badge badge-gray">No</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            <form action="{{ route('admin.taxes.toggle', $tax) }}" method="POST">
                                @csrf @method('PATCH')
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_active" onchange="this.form.submit()" {{ $tax->is_active ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gray-900"></div>
                                </label>
                            </form>
                        </td>
                        <td class="table-cell">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('admin.taxes.edit', $tax) }}" class="action-btn action-btn-edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form action="{{ route('admin.taxes.destroy', $tax) }}" method="POST" onsubmit="return confirm('Delete this tax rule?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn action-btn-delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-16 text-center">
                            <div class="empty-icon mx-auto"><svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg></div>
                            <p class="font-medium text-gray-900 mt-4">No customer tax rules yet</p>
                            <p class="text-sm text-gray-500 mt-1">Create your first tax rule to apply taxes to customer orders.</p>
                            <a href="{{ route('admin.taxes.create') }}" class="btn-primary mt-4 inline-flex">Add Tax Rule</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
