@extends('admin.layouts.app')

@section('title', 'Create Popup')

@section('content')
<!-- TomSelect CSS & Styling -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<style>
.ts-control {
    border-radius: 0.75rem !important;
    padding: 0.6rem 0.875rem !important;
    border-color: #e5e7eb !important;
    font-size: 0.875rem !important;
    background-color: #ffffff !important;
}
.ts-control .item {
    background-color: #fff7ed !important;
    color: #c2410c !important;
    border: 1px solid #ffedd5 !important;
    border-radius: 0.375rem !important;
    font-weight: 600 !important;
    padding: 2px 8px !important;
}
.ts-dropdown {
    border-radius: 0.75rem !important;
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15) !important;
    border: 1px solid #e5e7eb !important;
    z-index: 99999 !important;
    max-height: 280px !important;
    background-color: #ffffff !important;
}
.card, .card-body, .form-group {
    overflow: visible !important;
}
.ts-dropdown .option.active {
    background-color: #ea580c !important;
    color: #ffffff !important;
}
</style>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.popups.index') }}" class="w-10 h-10 flex items-center justify-center rounded-lg bg-white border border-gray-200 text-gray-500 hover:bg-gray-50 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Create User App Popup</h1>
            <p class="text-sm text-gray-500">Configure dynamic popups, positions, display triggers, and audience targeting</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 space-y-1">
            <p class="font-semibold">Please correct the errors below:</p>
            <ul class="list-disc list-inside text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.popups.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- 1. GENERAL & MEDIA SETTINGS -->
        <div class="card">
            <div class="card-header"><h3 class="card-title">1. General & Media Settings</h3></div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label class="label">Popup Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g., Summer Special Discount Banner" class="input">
                    </div>

                    <div class="form-group">
                        <label class="label">Status <span class="text-red-500">*</span></label>
                        <select name="status" required class="input">
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="scheduled" {{ old('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                            <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="label">Upload Media (Image / GIF) <span class="text-red-500">*</span></label>
                        <input type="file" name="media" required accept="image/jpeg,image/png,image/webp,image/gif" class="input p-1 bg-gray-50">
                        <p class="form-hint mt-1 text-gray-500 text-xs">Supports JPG, PNG, WEBP, and animated GIF (max 10MB). Image auto-adjusts to fill container.</p>
                        <div class="mt-2 text-[11px] bg-blue-50 border border-blue-100 text-blue-800 p-2.5 rounded-lg space-y-0.5">
                            <p class="font-bold uppercase tracking-wider text-[10px]">Recommended Image Sizes by Position:</p>
                            <div class="grid grid-cols-2 gap-1 text-[11px] mt-1">
                                <div>• <strong>Center Dialog:</strong> 800 x 1000 px (4:5 ratio)</div>
                                <div>• <strong>Floating Card:</strong> 500 x 500 px (1:1 ratio)</div>
                                <div>• <strong>Top/Bottom Banner:</strong> 1200 x 350 px (3:1 ratio)</div>
                                <div>• <strong>Full Screen:</strong> 1080 x 1920 px (9:16 ratio)</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="label">Position <span class="text-red-500">*</span></label>
                        <select name="position" required class="input">
                            <option value="center">Center (Dialog with backdrop)</option>
                            <option value="top">Top (Header banner)</option>
                            <option value="bottom">Bottom (Footer sheet banner)</option>
                            <option value="floating">Floating Popup Card</option>
                            <option value="full_screen">Full Screen Modal</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="label">Priority (Higher numbers load first)</label>
                        <input type="number" name="priority" value="{{ old('priority', 0) }}" min="0" class="input">
                        <p class="form-hint">Controls precedence when multiple popups match</p>
                    </div>

                    <div class="form-group flex items-end">
                        <label class="flex items-center gap-3 p-3 w-full rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition">
                            <input type="checkbox" name="show_close_button" value="1" checked class="checkbox">
                            <div>
                                <p class="font-medium text-gray-900 text-sm">Show Close / X Button</p>
                                <p class="text-xs text-gray-500">Allow users to dismiss the popup via on-screen close button</p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. CLICK ACTION & REDIRECTION -->
        <div class="card">
            <div class="card-header"><h3 class="card-title">2. Image Click Action & Redirection</h3></div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label class="label">Image Click Action</label>
                        <select name="click_action" id="click_action_select" onchange="toggleClickTarget()" class="input">
                            <option value="none">None (Clicking does nothing)</option>
                            <option value="url">Redirect URL (External Link)</option>
                            <option value="page">Internal App Page</option>
                            <option value="product">Product Details</option>
                            <option value="category">Category Products</option>
                            <option value="store">Store Details</option>
                            <option value="coupon">Coupon / Offer Destination</option>
                            <option value="membership">VIP Membership Page</option>
                        </select>
                    </div>

                    <div class="form-group" id="target_container">
                        <label class="label">Target Destination</label>

                        <div id="wrapper_target_url" class="hidden">
                            <input type="text" id="target_url_input" placeholder="https://example.com" class="input">
                        </div>

                        <div id="wrapper_target_page" class="hidden">
                            <select id="target_page_select" class="input searchable-select">
                                <option value="">Search App Page...</option>
                                <option value="/cart">Cart Page</option>
                                <option value="/wishlist">Wishlist Page</option>
                                <option value="/orders">Orders List</option>
                                <option value="/profile">Profile / Account</option>
                                <option value="/vip-membership">VIP Membership Page</option>
                                <option value="/wallet">Wallet Page</option>
                                <option value="/referral">Referral Page</option>
                                <option value="/ai-chat">AI Assistant Chat</option>
                            </select>
                        </div>

                        <div id="wrapper_target_product" class="hidden">
                            <select id="target_product_select" class="input searchable-select">
                                <option value="">Search & Select Product...</option>
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}">{{ $prod->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="wrapper_target_category" class="hidden">
                            <select id="target_category_select" class="input searchable-select">
                                <option value="">Search & Select Category...</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="wrapper_target_store" class="hidden">
                            <select id="target_store_select" class="input searchable-select">
                                <option value="">Search & Select Store...</option>
                                @foreach($stores as $st)
                                    <option value="{{ $st->id }}">{{ $st->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="wrapper_target_coupon" class="hidden">
                            <select id="target_coupon_select" class="input searchable-select">
                                <option value="">Search & Select Coupon Code...</option>
                                @foreach($coupons as $cpn)
                                    <option value="{{ $cpn->code }}">{{ $cpn->code }}</option>
                                @endforeach
                            </select>
                        </div>

                        <input type="hidden" name="click_action_target" id="final_action_target" value="">
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. DISPLAY CONDITIONS & TRIGGERS -->
        <div class="card">
            <div class="card-header"><h3 class="card-title">3. Display Condition & Trigger</h3></div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label class="label">When Should Popup Appear? <span class="text-red-500">*</span></label>
                        <select name="display_trigger" id="display_trigger_select" onchange="toggleTriggerValue()" class="input">
                            <option value="every_app_open">Every App Open</option>
                            <option value="first_app_open">First App Open Only</option>
                            <option value="second_app_open">2nd App Open Only</option>
                            <option value="once_per_day">Once Per Day</option>
                            <option value="once_per_session">Once Per Session</option>
                            <option value="once_per_user">Once Per User (Lifetime)</option>
                            <option value="after_x_seconds">After X Seconds Delay</option>
                            <option value="after_x_opens">After X App Opens</option>
                            <option value="on_app_exit">On App Exit (Back Press)</option>
                            <option value="after_order_completion">After Order Completion</option>
                            <option value="after_login">After Login</option>
                            <option value="before_checkout">Before Checkout Page</option>
                            <option value="after_checkout">After Checkout</option>
                            <option value="specific_product_in_cart">When Cart Contains Specific Product</option>
                            <option value="cart_amount_reached">When Cart Amount Reaches Min Threshold</option>
                        </select>
                    </div>

                    <div class="form-group hidden" id="trigger_value_container">
                        <label class="label" id="trigger_value_label">Trigger Value</label>
                        <input type="text" name="trigger_value" id="trigger_value_input" value="{{ old('trigger_value') }}" placeholder="e.g. 10" class="input">
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. AUDIENCE & TARGETING -->
        <div class="card">
            <div class="card-header"><h3 class="card-title">4. Audience & Targeting Rules</h3></div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label class="label">Target Audience</label>
                        <select name="audience_type" class="input">
                            <option value="all">All Users (Guest & Logged In)</option>
                            <option value="new">New Users Only</option>
                            <option value="existing">Existing Users Only</option>
                            <option value="vip">VIP Members Only</option>
                            <option value="non_vip">Non-VIP Users Only</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="label">Target Zones</label>
                        <select name="zone_ids[]" multiple class="searchable-multi-select" placeholder="Search & select target zones...">
                            @foreach($zones as $z)
                                <option value="{{ $z->id }}">{{ $z->name }}</option>
                            @endforeach
                        </select>
                        <p class="form-hint">Leave empty for All Zones</p>
                    </div>

                    <div class="form-group">
                        <label class="label">Target Languages</label>
                        <select name="language_codes[]" multiple class="searchable-multi-select" placeholder="Search & select target languages...">
                            @foreach($languages as $lang)
                                <option value="{{ $lang->code }}">{{ $lang->name }} ({{ $lang->code }})</option>
                            @endforeach
                        </select>
                        <p class="form-hint">Leave empty for All Languages</p>
                    </div>

                    <div class="form-group">
                        <label class="label">Target Stores</label>
                        <select name="store_ids[]" multiple class="searchable-multi-select" placeholder="Search & select target stores...">
                            @foreach($stores as $st)
                                <option value="{{ $st->id }}">{{ $st->name }}</option>
                            @endforeach
                        </select>
                        <p class="form-hint">Leave empty for All Stores</p>
                    </div>

                    <div class="form-group">
                        <label class="label">Target Categories</label>
                        <select name="category_ids[]" multiple class="searchable-multi-select" placeholder="Search & select target categories...">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <p class="form-hint">Leave empty for All Categories</p>
                    </div>

                    <div class="form-group">
                        <label class="label">Target Products</label>
                        <select name="product_ids[]" multiple class="searchable-multi-select" placeholder="Search & select target products...">
                            @foreach($products as $prod)
                                <option value="{{ $prod->id }}">{{ $prod->name }}</option>
                            @endforeach
                        </select>
                        <p class="form-hint">Leave empty for All Products</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. DATETIME SCHEDULING -->
        <div class="card">
            <div class="card-header"><h3 class="card-title">5. Date & Time Scheduling</h3></div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label class="label">Start Date & Time (Optional)</label>
                        <input type="datetime-local" name="start_at" value="{{ old('start_at') }}" class="input">
                    </div>

                    <div class="form-group">
                        <label class="label">End Date & Time (Optional)</label>
                        <input type="datetime-local" name="end_at" value="{{ old('end_at') }}" class="input">
                    </div>
                </div>
            </div>
        </div>

        <!-- ACTION BUTTONS -->
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.popups.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" onclick="syncActionTarget()" class="btn-primary">Create Popup</button>
        </div>
    </form>
</div>

<script>
function toggleClickTarget() {
    const action = document.getElementById('click_action_select').value;
    const urlWrap = document.getElementById('wrapper_target_url');
    const pageWrap = document.getElementById('wrapper_target_page');
    const prodWrap = document.getElementById('wrapper_target_product');
    const catWrap = document.getElementById('wrapper_target_category');
    const stWrap = document.getElementById('wrapper_target_store');
    const cpnWrap = document.getElementById('wrapper_target_coupon');

    [urlWrap, pageWrap, prodWrap, catWrap, stWrap, cpnWrap].forEach(el => {
        if (el) el.classList.add('hidden');
    });

    if (action === 'url' && urlWrap) urlWrap.classList.remove('hidden');
    else if (action === 'page' && pageWrap) pageWrap.classList.remove('hidden');
    else if (action === 'product' && prodWrap) prodWrap.classList.remove('hidden');
    else if (action === 'category' && catWrap) catWrap.classList.remove('hidden');
    else if (action === 'store' && stWrap) stWrap.classList.remove('hidden');
    else if (action === 'coupon' && cpnWrap) cpnWrap.classList.remove('hidden');
}

function toggleTriggerValue() {
    const trigger = document.getElementById('display_trigger_select').value;
    const container = document.getElementById('trigger_value_container');
    const label = document.getElementById('trigger_value_label');
    const input = document.getElementById('trigger_value_input');

    if (trigger === 'after_x_seconds') {
        container.classList.remove('hidden');
        label.innerText = 'Delay Seconds';
        input.placeholder = 'e.g. 10';
    } else if (trigger === 'after_x_opens') {
        container.classList.remove('hidden');
        label.innerText = 'Number of Opens';
        input.placeholder = 'e.g. 5';
    } else if (trigger === 'cart_amount_reached') {
        container.classList.remove('hidden');
        label.innerText = 'Minimum Cart Subtotal Amount';
        input.placeholder = 'e.g. 500';
    } else if (trigger === 'specific_product_in_cart') {
        container.classList.remove('hidden');
        label.innerText = 'Target Product ID';
        input.placeholder = 'e.g. Product ID';
    } else {
        container.classList.add('hidden');
    }
}

function syncActionTarget() {
    const action = document.getElementById('click_action_select').value;
    let val = '';
    if (action === 'url') val = document.getElementById('target_url_input').value;
    else if (action === 'page') val = document.getElementById('target_page_select').value;
    else if (action === 'product') val = document.getElementById('target_product_select').value;
    else if (action === 'category') val = document.getElementById('target_category_select').value;
    else if (action === 'store') val = document.getElementById('target_store_select').value;
    else if (action === 'coupon') val = document.getElementById('target_coupon_select').value;

    document.getElementById('final_action_target').value = val;
}

document.addEventListener('DOMContentLoaded', () => {
    toggleClickTarget();
    toggleTriggerValue();

    // Initialize TomSelect on Single Selects
    document.querySelectorAll('.searchable-select').forEach(el => {
        new TomSelect(el, {
            create: false,
            maxOptions: 1000,
            plugins: ['clear_button'],
            dropdownParent: 'body',
        });
    });

    // Initialize TomSelect on Multi Selects
    document.querySelectorAll('.searchable-multi-select').forEach(el => {
        new TomSelect(el, {
            create: false,
            maxOptions: 1000,
            plugins: ['remove_button', 'clear_button'],
            dropdownParent: 'body',
        });
    });
});
</script>
@endsection
