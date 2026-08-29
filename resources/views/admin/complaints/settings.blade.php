@extends('admin.layouts.app')

@section('title', 'Complaint System Settings')

@section('content')
<div class="space-y-6 max-w-4xl">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Complaint System Settings</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">Configure automated vendor penalties, suspension thresholds, and complaint categories</p>
        </div>
        <a href="{{ route('admin.complaints.index') }}" class="btn btn-secondary text-xs flex items-center justify-center gap-1.5 w-full sm:w-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to Complaints List</span>
        </a>
    </div>

    @if(session('success'))
        <div class="p-3 bg-green-50 text-green-700 text-xs rounded-xl font-bold border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="card p-5">
        <form action="{{ route('admin.complaints.settings.update') }}" method="POST" class="space-y-5 text-xs">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="label font-bold text-gray-800">Default Vendor Penalty Amount (₹)</label>
                    <input type="number" step="0.01" name="default_vendor_penalty" value="{{ old('default_vendor_penalty', $settings['default_vendor_penalty']) }}" class="input text-xs" required placeholder="500.00">
                    <p class="text-[10px] text-gray-500 mt-1">Default fine deducted from Vendor Wallet upon complaint approval</p>
                </div>

                <div>
                    <label class="label font-bold text-gray-800">Admin Support Email Notification</label>
                    <input type="email" name="admin_notification_email" value="{{ old('admin_notification_email', $settings['admin_notification_email']) }}" class="input text-xs" required placeholder="support@inallcart.com">
                    <p class="text-[10px] text-gray-500 mt-1">Email address to receive immediate complaint notifications</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-3 border-t">
                <div>
                    <label class="label font-bold text-gray-800">Vendor Auto-Suspension Strike Limit</label>
                    <input type="number" name="auto_suspend_vendor_strikes" value="{{ old('auto_suspend_vendor_strikes', $settings['auto_suspend_vendor_strikes']) }}" class="input text-xs" required min="1" placeholder="3">
                    <p class="text-[10px] text-gray-500 mt-1">Number of approved complaints before vendor account auto-suspends</p>
                </div>

                <div>
                    <label class="label font-bold text-gray-800">Rider Auto-Suspension Strike Limit</label>
                    <input type="number" name="auto_suspend_driver_strikes" value="{{ old('auto_suspend_driver_strikes', $settings['auto_suspend_driver_strikes']) }}" class="input text-xs" required min="1" placeholder="3">
                    <p class="text-[10px] text-gray-500 mt-1">Number of delivery complaints before rider account auto-suspends</p>
                </div>
            </div>

            <div class="pt-3 border-t">
                <label class="label font-bold text-gray-800">Allowed Complaint Categories (Comma Separated)</label>
                <textarea name="allowed_categories" rows="3" class="input text-xs font-mono" required placeholder="Food Quality, Wrong Item, Damaged Packaging, Late Delivery">{{ old('allowed_categories', $settings['allowed_categories']) }}</textarea>
                <p class="text-[10px] text-gray-500 mt-1">Options displayed to customers when filing a complaint in app/web</p>
            </div>

            <div class="flex justify-end pt-3 border-t">
                <button type="submit" class="btn btn-primary text-xs px-6 py-2.5 font-bold">
                    Save Complaint Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
