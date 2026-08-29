@extends('admin.layouts.app')
@section('title', 'Invoices')
@section('content')
<div class="space-y-5">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Invoices</h1>
            <p class="text-sm text-gray-500 mt-1">Manage and track all order invoices</p>
        </div>
        <button onclick="document.getElementById('settingsPanel').classList.remove('translate-x-full')" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Manage Style
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-4">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Invoices</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Issued</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ number_format($stats['issued']) }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Paid</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ number_format($stats['paid']) }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Revenue</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ \App\Helpers\CurrencyHelper::format($stats['total_revenue']) }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card p-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by invoice #, customer, or order #..." class="input flex-1">
            <select name="status" class="input w-auto">
                <option value="">All Status</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="issued" {{ request('status') === 'issued' ? 'selected' : '' }}>Issued</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="input w-auto" placeholder="From">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="input w-auto" placeholder="To">
            <button type="submit" class="btn-primary">Filter</button>
            @if(request()->hasAny(['search', 'status', 'date_from', 'date_to']))
                <a href="{{ route('admin.invoices.index') }}" class="btn-secondary">Clear</a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="table-header">Invoice #</th>
                        <th class="table-header">Order</th>
                        <th class="table-header">Customer</th>
                        <th class="table-header">Store</th>
                        <th class="table-header">Amount</th>
                        <th class="table-header">Status</th>
                        <th class="table-header">Date</th>
                        <th class="table-header text-center w-32">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($invoices as $invoice)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="table-cell">
                            <a href="{{ route('admin.invoices.show', $invoice) }}" class="font-semibold text-blue-600 hover:text-blue-800">
                                {{ $invoice->invoice_number }}
                            </a>
                        </td>
                        <td class="table-cell">
                            <a href="{{ route('admin.orders.show', $invoice->order_id) }}" class="text-sm text-gray-600 hover:text-blue-600">
                                #{{ $invoice->order?->order_number ?? 'N/A' }}
                            </a>
                        </td>
                        <td class="table-cell">
                            <p class="font-medium text-gray-900 text-sm">{{ $invoice->customer_name }}</p>
                            <p class="text-xs text-gray-500">{{ $invoice->customer_email }}</p>
                        </td>
                        <td class="table-cell">
                            <span class="text-sm text-gray-700">{{ $invoice->store?->name ?? $invoice->business_name }}</span>
                        </td>
                        <td class="table-cell">
                            <span class="font-semibold text-gray-900">{{ \App\Helpers\CurrencyHelper::format($invoice->total) }}</span>
                        </td>
                        <td class="table-cell">
                            @if($invoice->status === 'paid')
                                <span class="badge badge-green">Paid</span>
                            @elseif($invoice->status === 'issued')
                                <span class="badge badge-blue">Issued</span>
                            @elseif($invoice->status === 'draft')
                                <span class="badge badge-orange">Draft</span>
                            @else
                                <span class="badge badge-red">Cancelled</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            <span class="text-sm text-gray-500">{{ $invoice->issued_at ? $invoice->issued_at->format('d M Y') : $invoice->created_at->format('d M Y') }}</span>
                        </td>
                        <td class="table-cell">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('admin.invoices.show', $invoice) }}" class="action-btn action-btn-view p-0 bg-transparent border-0 shadow-none" title="Preview">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('admin.invoices.edit', $invoice) }}" class="action-btn action-btn-edit p-0 bg-transparent border-0 shadow-none" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <a href="{{ route('admin.invoices.print', $invoice) }}" target="_blank" class="action-btn p-0 bg-transparent border-0 shadow-none text-gray-400 hover:text-gray-700" title="Print">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                </a>
                                <form action="{{ route('admin.invoices.destroy', $invoice) }}" method="POST" onsubmit="return confirm('Delete this invoice?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn action-btn-delete p-0 bg-transparent border-0 shadow-none" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-16 text-center">
                            <div class="empty-icon mx-auto"><svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div>
                            <p class="font-medium text-gray-900 mt-4">No invoices found</p>
                            <p class="text-sm text-gray-500 mt-1">Invoices are generated from individual order pages.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())<div class="px-5 py-4 border-t border-gray-100">{{ $invoices->appends(request()->query())->links() }}</div>@endif
    </div>
</div>

<!-- ==================== SETTINGS SLIDE-OUT PANEL ==================== -->
<div id="settingsPanel" class="fixed inset-0 z-[9999] translate-x-full transition-transform duration-300 ease-in-out">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="document.getElementById('settingsPanel').classList.add('translate-x-full')"></div>

    <!-- Panel -->
    <div class="absolute right-0 top-0 bottom-0 w-full max-w-xl bg-white shadow-2xl flex flex-col">
        <!-- Panel Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Invoice Template Settings</h2>
                <p class="text-xs text-gray-500 mt-0.5">Configure how your invoices look when printed</p>
            </div>
            <button onclick="document.getElementById('settingsPanel').classList.add('translate-x-full')" class="p-2 hover:bg-gray-200 rounded-lg transition-colors">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Panel Body (Scrollable) -->
        <div class="flex-1 overflow-y-auto">
            <form action="{{ route('admin.invoices.update-settings') }}" method="POST" enctype="multipart/form-data" class="divide-y divide-gray-100">
                @csrf

                <!-- Company Logo -->
                <div class="p-6 space-y-4">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Company Logo
                    </h3>
                    <div class="flex items-center gap-4">
                        <div class="w-20 h-20 rounded-xl border-2 border-dashed border-gray-300 flex items-center justify-center overflow-hidden bg-gray-50 flex-shrink-0" id="logoPreviewBox">
                            @if($invoiceSettings['invoice_logo'])
                                <img src="{{ storage_url($invoiceSettings['invoice_logo']) }}" class="w-full h-full object-contain" id="logoPreviewImg">
                            @else
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="logoPlaceholder"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            @endif
                        </div>
                        <div class="flex-1">
                            <label class="block">
                                <span class="btn-secondary inline-flex items-center gap-2 cursor-pointer text-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    Upload Logo
                                    <input type="file" name="invoice_logo" accept="image/png,image/jpg,image/jpeg,image/svg+xml,image/webp" class="hidden" onchange="previewLogo(this)">
                                </span>
                            </label>
                            <p class="text-xs text-gray-400 mt-2">PNG, JPG, SVG, WebP • Max 2MB</p>
                            @if($invoiceSettings['invoice_logo'])
                                <label class="flex items-center gap-2 mt-2 text-xs text-red-500 cursor-pointer hover:text-red-700">
                                    <input type="checkbox" name="remove_logo" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                    Remove current logo
                                </label>
                            @endif
                        </div>
                    </div>
                    <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors">
                        <input type="checkbox" name="invoice_show_logo" value="1" {{ $invoiceSettings['invoice_show_logo'] === '1' ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="text-sm font-medium text-gray-900">Show logo on invoices</span>
                            <p class="text-xs text-gray-500">Display your company logo in the invoice header</p>
                        </div>
                    </label>
                </div>

                <!-- Company Information -->
                <div class="p-6 space-y-4">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Company Information
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Company / Business Name</label>
                            <input type="text" name="invoice_company_name" id="inp_company_name" value="{{ $invoiceSettings['invoice_company_name'] }}" class="input w-full" placeholder="Your Company Name">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <textarea name="invoice_company_address" rows="2" class="input w-full" placeholder="123 Business Street, City, State, ZIP">{{ $invoiceSettings['invoice_company_address'] }}</textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                <input type="text" name="invoice_company_phone" id="inp_company_phone" value="{{ $invoiceSettings['invoice_company_phone'] }}" class="input w-full" placeholder="+91 9876543210">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" name="invoice_company_email" value="{{ $invoiceSettings['invoice_company_email'] }}" class="input w-full" placeholder="billing@company.com">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">GSTIN / Tax ID</label>
                            <input type="text" name="invoice_gstin" value="{{ $invoiceSettings['invoice_gstin'] }}" class="input w-full" placeholder="e.g. 22AAAAA0000A1Z5">
                        </div>
                    </div>
                    <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors">
                        <input type="checkbox" name="invoice_show_store_info" value="1" {{ $invoiceSettings['invoice_show_store_info'] === '1' ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="text-sm font-medium text-gray-900">Use store-specific info</span>
                            <p class="text-xs text-gray-500">Override with the store's own name/address when applicable</p>
                        </div>
                    </label>
                </div>

                <!-- Design & Style -->
                <div class="p-6 space-y-4">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                        Design & Style
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Invoice Number Prefix</label>
                            <input type="text" name="invoice_prefix" id="inp_prefix" value="{{ $invoiceSettings['invoice_prefix'] }}" class="input w-full" placeholder="INV">
                            <p class="text-xs text-gray-400 mt-1">Format: PREFIX-YYYYMM-0001 (e.g. INV-202602-0001)</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Accent Color</label>
                            <div class="flex items-center gap-3">
                                <input type="color" name="invoice_accent_color" value="{{ $invoiceSettings['invoice_accent_color'] }}" class="w-10 h-10 rounded-lg border border-gray-300 cursor-pointer p-0.5" id="accentColorPicker">
                                <input type="text" value="{{ $invoiceSettings['invoice_accent_color'] }}" class="input w-28 font-mono text-sm" id="accentColorText">
                                <div class="flex gap-1.5">
                                    @foreach(['#111827','#1e40af','#0d9488','#7c3aed','#dc2626','#ea580c','#16a34a'] as $color)
                                    <button type="button" onclick="setAccentColor('{{ $color }}')" 
                                        class="w-7 h-7 rounded-full border-2 border-white shadow-sm hover:scale-110 transition-transform" style="background: {{ $color }}"></button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer & Terms -->
                <div class="p-6 space-y-4">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Footer & Terms
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Footer Text</label>
                            <input type="text" name="invoice_footer_text" id="inp_footer_text" value="{{ $invoiceSettings['invoice_footer_text'] }}" class="input w-full" placeholder="Thank you for your business!">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Terms & Conditions</label>
                            <textarea name="invoice_terms" rows="3" class="input w-full" placeholder="Payment is due within 30 days. Late payments may incur additional charges...">{{ $invoiceSettings['invoice_terms'] }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Live Preview Card -->
                <div class="p-6 space-y-4">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        Live Preview
                        <span class="ml-auto text-[10px] font-normal text-green-500 flex items-center gap-1"><span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span> Updates in realtime</span>
                    </h3>
                    <div class="border border-gray-200 rounded-xl p-5 bg-white shadow-inner" id="previewCard">
                        <!-- Preview Header -->
                        <div class="flex justify-between items-start mb-4 pb-3" id="previewHeaderBorder" style="border-bottom: 2px solid {{ $invoiceSettings['invoice_accent_color'] }}">
                            <div class="flex items-center gap-3">
                                <img src="{{ $invoiceSettings['invoice_logo'] ? storage_url($invoiceSettings['invoice_logo']) : '' }}" class="w-10 h-10 object-contain rounded {{ $invoiceSettings['invoice_logo'] ? '' : 'hidden' }}" id="previewLogo">
                                <div>
                                    <p class="font-bold text-sm" id="previewCompanyName" style="color: {{ $invoiceSettings['invoice_accent_color'] }}">{{ $invoiceSettings['invoice_company_name'] ?: 'Your Company' }}</p>
                                    <p class="text-[10px] text-gray-400" id="previewPhone">{{ $invoiceSettings['invoice_company_phone'] ?: '+91 XXXXXXXXXX' }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-black uppercase tracking-widest" id="previewInvoiceLabel" style="color: {{ $invoiceSettings['invoice_accent_color'] }}20">Invoice</p>
                                <p class="text-[10px] font-semibold" id="previewInvoiceNum" style="color: {{ $invoiceSettings['invoice_accent_color'] }}">{{ $invoiceSettings['invoice_prefix'] }}-202602-0001</p>
                            </div>
                        </div>
                        <!-- Preview Items -->
                        <div class="space-y-1.5 mb-3">
                            <div class="flex justify-between text-[10px]"><span class="text-gray-400">Item Name</span><span class="text-gray-600">₹299.00 × 2</span></div>
                            <div class="flex justify-between text-[10px]"><span class="text-gray-400">Another Item</span><span class="text-gray-600">₹149.00 × 1</span></div>
                        </div>
                        <!-- Preview Total -->
                        <div class="pt-2 flex justify-between" id="previewTotalBorder" style="border-top: 2px solid {{ $invoiceSettings['invoice_accent_color'] }}">
                            <span class="text-[10px] font-bold" id="previewTotalLabel" style="color: {{ $invoiceSettings['invoice_accent_color'] }}">Total</span>
                            <span class="text-[10px] font-bold" id="previewTotalValue" style="color: {{ $invoiceSettings['invoice_accent_color'] }}">₹747.00</span>
                        </div>
                        <!-- Preview Footer -->
                        <p class="text-[8px] text-gray-400 mt-3 text-center italic" id="previewFooter">{{ $invoiceSettings['invoice_footer_text'] ?: '' }}</p>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="p-6 bg-gray-50">
                    <button type="submit" class="w-full py-3 bg-gray-900 text-white rounded-xl text-sm font-bold uppercase tracking-wider hover:bg-black transition-all shadow-lg active:scale-[0.98]">
                        Save Invoice Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // === Element References ===
    const inp = {
        companyName: document.getElementById('inp_company_name'),
        phone: document.getElementById('inp_company_phone'),
        prefix: document.getElementById('inp_prefix'),
        footerText: document.getElementById('inp_footer_text'),
        colorPicker: document.getElementById('accentColorPicker'),
        colorText: document.getElementById('accentColorText'),
    };

    const preview = {
        companyName: document.getElementById('previewCompanyName'),
        phone: document.getElementById('previewPhone'),
        invoiceNum: document.getElementById('previewInvoiceNum'),
        invoiceLabel: document.getElementById('previewInvoiceLabel'),
        footer: document.getElementById('previewFooter'),
        logo: document.getElementById('previewLogo'),
        headerBorder: document.getElementById('previewHeaderBorder'),
        totalBorder: document.getElementById('previewTotalBorder'),
        totalLabel: document.getElementById('previewTotalLabel'),
        totalValue: document.getElementById('previewTotalValue'),
    };

    // === Company Name ===
    inp.companyName?.addEventListener('input', function() {
        preview.companyName.textContent = this.value || 'Your Company';
    });

    // === Phone ===
    inp.phone?.addEventListener('input', function() {
        preview.phone.textContent = this.value || '+91 XXXXXXXXXX';
    });

    // === Prefix ===
    inp.prefix?.addEventListener('input', function() {
        preview.invoiceNum.textContent = (this.value || 'INV') + '-202602-0001';
    });

    // === Footer Text ===
    inp.footerText?.addEventListener('input', function() {
        preview.footer.textContent = this.value;
        preview.footer.style.display = this.value ? 'block' : 'none';
    });
    // Init footer visibility
    if (preview.footer && !preview.footer.textContent.trim()) {
        preview.footer.style.display = 'none';
    }

    // === Accent Color (from picker, text input, and palette buttons) ===
    function applyAccentColor(color) {
        inp.colorPicker.value = color;
        inp.colorText.value = color;

        // Update all preview accent elements
        preview.companyName.style.color = color;
        preview.invoiceNum.style.color = color;
        preview.invoiceLabel.style.color = color + '20';
        preview.headerBorder.style.borderBottomColor = color;
        preview.totalBorder.style.borderTopColor = color;
        preview.totalLabel.style.color = color;
        preview.totalValue.style.color = color;
    }

    inp.colorPicker?.addEventListener('input', function() {
        applyAccentColor(this.value);
    });

    inp.colorText?.addEventListener('input', function() {
        // Only apply if it looks like a valid color
        if (/^#[0-9a-fA-F]{3,8}$/.test(this.value)) {
            applyAccentColor(this.value);
        }
    });

    // Expose for palette buttons
    window.setAccentColor = applyAccentColor;

    // === Logo Preview ===
    window.previewLogo = function(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Update the upload box
                const box = document.getElementById('logoPreviewBox');
                box.innerHTML = '<img src="' + e.target.result + '" class="w-full h-full object-contain" id="logoPreviewImg">';

                // Update the live preview logo
                preview.logo.src = e.target.result;
                preview.logo.classList.remove('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        }
    };
});
</script>
@endsection
