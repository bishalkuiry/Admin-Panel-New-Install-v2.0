@extends('admin.layouts.app')

@section('title', 'Edit Popup - ' . $popup->name)

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
            <h1 class="text-xl font-semibold text-gray-900">Edit Popup: {{ $popup->name }}</h1>
            <p class="text-sm text-gray-500">Update popup settings, position, display trigger, and targeting rules</p>
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

    <form action="{{ route('admin.popups.update', $popup->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- 1. GENERAL & MEDIA SETTINGS -->
        <div class="card">
            <div class="card-header"><h3 class="card-title">1. General & Media Settings</h3></div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label class="label">Popup Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $popup->name) }}" required class="input">
                    </div>

                    <div class="form-group">
                        <label class="label">Status <span class="text-red-500">*</span></label>
                        <select name="status" required class="input">
                            <option value="active" {{ old('status', $popup->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="scheduled" {{ old('status', $popup->status) == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                            <option value="draft" {{ old('status', $popup->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="inactive" {{ old('status', $popup->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="expired" {{ old('status', $popup->status) == 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="label">Replace Media (Image / GIF)</label>
                        <input type="file" name="media" accept="image/jpeg,image/png,image/webp,image/gif" class="input p-1 bg-gray-50">
                        <p class="form-hint mt-1 text-gray-500 text-xs">Leave empty to keep existing media. Image auto-adjusts to fill container.</p>
                        
                        <div class="mt-2 text-[11px] bg-blue-50 border border-blue-100 text-blue-800 p-2.5 rounded-lg space-y-0.5">
                            <p class="font-bold uppercase tracking-wider text-[10px]">Recommended Image Sizes by Position:</p>
                            <div class="grid grid-cols-2 gap-1 text-[11px] mt-1">
                                <div>• <strong>Center Dialog:</strong> 800 x 1000 px (4:5 ratio)</div>
                                <div>• <strong>Floating Card:</strong> 500 x 500 px (1:1 ratio)</div>
                                <div>• <strong>Top/Bottom Banner:</strong> 1200 x 350 px (3:1 ratio)</div>
                                <div>• <strong>Full Screen:</strong> 1080 x 1920 px (9:16 ratio)</div>
                            </div>
                        </div>

                        @if(!empty($popup->formatted_media_url))
                            <div class="mt-3 flex items-center gap-3">
                                <div class="w-16 h-16 rounded-lg bg-gray-100 border border-gray-200 overflow-hidden">
                                    <img src="{{ $popup->formatted_media_url }}" alt="Preview" class="w-full h-full object-cover">
                                </div>
                                <span class="text-xs text-gray-500">Current Media ({{ strtoupper($popup->media_type) }})</span>
                            </div>
                        @endif
                    </div>

                    <div class="form-group">
                        <label class="label">Position <span class="text-red-500">*</span></label>
                        <select name="position" required class="input">
                            <option value="center" {{ old('position', $popup->position) == 'center' ? 'selected' : '' }}>Center (Dialog with backdrop)</option>
                            <option value="top" {{ old('position', $popup->position) == 'top' ? 'selected' : '' }}>Top (Header banner)</option>
                            <option value="bottom" {{ old('position', $popup->position) == 'bottom' ? 'selected' : '' }}>Bottom (Footer sheet banner)</option>
                            <option value="floating" {{ old('position', $popup->position) == 'floating' ? 'selected' : '' }}>Floating Popup Card</option>
                            <option value="full_screen" {{ old('position', $popup->position) == 'full_screen' ? 'selected' : '' }}>Full Screen Modal</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="label">Priority (Higher numbers load first)</label>
                        <input type="number" name="priority" value="{{ old('priority', $popup->priority) }}" min="0" class="input">
                    </div>

                    <div class="form-group flex items-end">
                        <label class="flex items-center gap-3 p-3 w-full rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition">
                            <input type="checkbox" name="show_close_button" value="1" {{ old('show_close_button', $popup->show_close_button) ? 'checked' : '' }} class="checkbox">
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
                            <option value="none" {{ old('click_action', $popup->click_action) == 'none' ? 'selected' : '' }}>None (Clicking does nothing)</option>
                            <option value="url" {{ old('click_action', $popup->click_action) == 'url' ? 'selected' : '' }}>Redirect URL (External Link)</option>
                            <option value="page" {{ old('click_action', $popup->click_action) == 'page' ? 'selected' : '' }}>Internal App Page</option>
                            <option value="product" {{ old('click_action', $popup->click_action) == 'product' ? 'selected' : '' }}>Product Details</option>
                            <option value="category" {{ old('click_action', $popup->click_action) == 'category' ? 'selected' : '' }}>Category Products</option>
                            <option value="store" {{ old('click_action', $popup->click_action) == 'store' ? 'selected' : '' }}>Store Details</option>
                            <option value="coupon" {{ old('click_action', $popup->click_action) == 'coupon' ? 'selected' : '' }}>Coupon / Offer Destination</option>
                            <option value="membership" {{ old('click_action', $popup->click_action) == 'membership' ? 'selected' : '' }}>VIP Membership Page</option>
                        </select>
                    </div>

                    <div class="form-group" id="target_container">
                        <label class="label">Target Destination</label>

                        <div id="wrapper_target_url" class="hidden">
                            <input type="text" id="target_url_input" value="{{ old('click_action_target', $popup->click_action_target) }}" placeholder="https://example.com" class="input">
                        </div>

                        <div id="wrapper_target_page" class="hidden">
                            <select id="target_page_select" class="input searchable-select">
                                <option value="">Search App Page...</option>
                                <option value="/cart" {{ old('click_action_target', $popup->click_action_target) == '/cart' ? 'selected' : '' }}>Cart Page</option>
                                <option value="/wishlist" {{ old('click_action_target', $popup->click_action_target) == '/wishlist' ? 'selected' : '' }}>Wishlist Page</option>
                                <option value="/orders" {{ old('click_action_target', $popup->click_action_target) == '/orders' ? 'selected' : '' }}>Orders List</option>
                                <option value="/profile" {{ old('click_action_target', $popup->click_action_target) == '/profile' ? 'selected' : '' }}>Profile / Account</option>
                                <option value="/vip-membership" {{ old('click_action_target', $popup->click_action_target) == '/vip-membership' ? 'selected' : '' }}>VIP Membership Page</option>
                                <option value="/wallet" {{ old('click_action_target', $popup->click_action_target) == '/wallet' ? 'selected' : '' }}>Wallet Page</option>
                                <option value="/referral" {{ old('click_action_target', $popup->click_action_target) == '/referral' ? 'selected' : '' }}>Referral Page</option>
                                <option value="/ai-chat" {{ old('click_action_target', $popup->click_action_target) == '/ai-chat' ? 'selected' : '' }}>AI Assistant Chat</option>
                            </select>
                        </div>

                        <div id="wrapper_target_product" class="hidden">
                            <select id="target_product_select" class="input searchable-select">
                                <option value="">Search & Select Product...</option>
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}" {{ old('click_action_target', $popup->click_action_target) == $prod->id ? 'selected' : '' }}>{{ $prod->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="wrapper_target_category" class="hidden">
                            <select id="target_category_select" class="input searchable-select">
                                <option value="">Search & Select Category...</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('click_action_target', $popup->click_action_target) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="wrapper_target_store" class="hidden">
                            <select id="target_store_select" class="input searchable-select">
                                <option value="">Search & Select Store...</option>
                                @foreach($stores as $st)
                                    <option value="{{ $st->id }}" {{ old('click_action_target', $popup->click_action_target) == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="wrapper_target_coupon" class="hidden">
                            <select id="target_coupon_select" class="input searchable-select">
                                <option value="">Search & Select Coupon Code...</option>
                                @foreach($coupons as $cpn)
                                    <option value="{{ $cpn->code }}" {{ old('click_action_target', $popup->click_action_target) == $cpn->code ? 'selected' : '' }}>{{ $cpn->code }}</option>
                                @endforeach
                            </select>
                        </div>

                        <input type="hidden" name="click_action_target" id="final_action_target" value="{{ old('click_action_target', $popup->click_action_target) }}">
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
                            <option value="every_app_open" {{ old('display_trigger', $popup->display_trigger) == 'every_app_open' ? 'selected' : '' }}>Every App Open</option>
                            <option value="first_app_open" {{ old('display_trigger', $popup->display_trigger) == 'first_app_open' ? 'selected' : '' }}>First App Open Only</option>
                            <option value="second_app_open" {{ old('display_trigger', $popup->display_trigger) == 'second_app_open' ? 'selected' : '' }}>2nd App Open Only</option>
                            <option value="once_per_day" {{ old('display_trigger', $popup->display_trigger) == 'once_per_day' ? 'selected' : '' }}>Once Per Day</option>
                            <option value="once_per_session" {{ old('display_trigger', $popup->display_trigger) == 'once_per_session' ? 'selected' : '' }}>Once Per Session</option>
                            <option value="once_per_user" {{ old('display_trigger', $popup->display_trigger) == 'once_per_user' ? 'selected' : '' }}>Once Per User (Lifetime)</option>
                            <option value="after_x_seconds" {{ old('display_trigger', $popup->display_trigger) == 'after_x_seconds' ? 'selected' : '' }}>After X Seconds Delay</option>
                            <option value="after_x_opens" {{ old('display_trigger', $popup->display_trigger) == 'after_x_opens' ? 'selected' : '' }}>After X App Opens</option>
                            <option value="on_app_exit" {{ old('display_trigger', $popup->display_trigger) == 'on_app_exit' ? 'selected' : '' }}>On App Exit (Back Press)</option>
                            <option value="after_order_completion" {{ old('display_trigger', $popup->display_trigger) == 'after_order_completion' ? 'selected' : '' }}>After Order Completion</option>
                            <option value="after_login" {{ old('display_trigger', $popup->display_trigger) == 'after_login' ? 'selected' : '' }}>After Login</option>
                            <option value="before_checkout" {{ old('display_trigger', $popup->display_trigger) == 'before_checkout' ? 'selected' : '' }}>Before Checkout Page</option>
                            <option value="after_checkout" {{ old('display_trigger', $popup->display_trigger) == 'after_checkout' ? 'selected' : '' }}>After Checkout</option>
                            <option value="specific_product_in_cart" {{ old('display_trigger', $popup->display_trigger) == 'specific_product_in_cart' ? 'selected' : '' }}>When Cart Contains Specific Product</option>
                            <option value="cart_amount_reached" {{ old('display_trigger', $popup->display_trigger) == 'cart_amount_reached' ? 'selected' : '' }}>When Cart Amount Reaches Min Threshold</option>
                        </select>
                    </div>

                    <div class="form-group hidden" id="trigger_value_container">
                        <label class="label" id="trigger_value_label">Trigger Value</label>
                        <input type="text" name="trigger_value" id="trigger_value_input" value="{{ old('trigger_value', $popup->trigger_value) }}" placeholder="e.g. 10" class="input">
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
                            <option value="all" {{ old('audience_type', $popup->audience_type) == 'all' ? 'selected' : '' }}>All Users (Guest & Logged In)</option>
                            <option value="new" {{ old('audience_type', $popup->audience_type) == 'new' ? 'selected' : '' }}>New Users Only</option>
                            <option value="existing" {{ old('audience_type', $popup->audience_type) == 'existing' ? 'selected' : '' }}>Existing Users Only</option>
                            <option value="vip" {{ old('audience_type', $popup->audience_type) == 'vip' ? 'selected' : '' }}>VIP Members Only</option>
                            <option value="non_vip" {{ old('audience_type', $popup->audience_type) == 'non_vip' ? 'selected' : '' }}>Non-VIP Users Only</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="label">Target Zones</label>
                        <select name="zone_ids[]" multiple class="searchable-multi-select" placeholder="Search & select target zones...">
                            @foreach($zones as $z)
                                <option value="{{ $z->id }}" {{ in_array($z->id, $popup->zone_ids ?? []) ? 'selected' : '' }}>{{ $z->name }}</option>
                            @endforeach
                        </select>
                        <p class="form-hint">Leave empty for All Zones</p>
                    </div>

                    <div class="form-group">
                        <label class="label">Target Languages</label>
                        <select name="language_codes[]" multiple class="searchable-multi-select" placeholder="Search & select target languages...">
                            @foreach($languages as $lang)
                                <option value="{{ $lang->code }}" {{ in_array($lang->code, $popup->language_codes ?? []) ? 'selected' : '' }}>{{ $lang->name }} ({{ $lang->code }})</option>
                            @endforeach
                        </select>
                        <p class="form-hint">Leave empty for All Languages</p>
                    </div>

                    <div class="form-group">
                        <label class="label">Target Stores</label>
                        <select name="store_ids[]" multiple class="searchable-multi-select" placeholder="Search & select target stores...">
                            @foreach($stores as $st)
                                <option value="{{ $st->id }}" {{ in_array($st->id, $popup->store_ids ?? []) ? 'selected' : '' }}>{{ $st->name }}</option>
                            @endforeach
                        </select>
                        <p class="form-hint">Leave empty for All Stores</p>
                    </div>

                    <div class="form-group">
                        <label class="label">Target Categories</label>
                        <select name="category_ids[]" multiple class="searchable-multi-select" placeholder="Search & select target categories...">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ in_array($cat->id, $popup->category_ids ?? []) ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <p class="form-hint">Leave empty for All Categories</p>
                    </div>

                    <div class="form-group">
                        <label class="label">Target Products</label>
                        <select name="product_ids[]" multiple class="searchable-multi-select" placeholder="Search & select target products...">
                            @foreach($products as $prod)
                                <option value="{{ $prod->id }}" {{ in_array($prod->id, $popup->product_ids ?? []) ? 'selected' : '' }}>{{ $prod->name }}</option>
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
                        <input type="datetime-local" name="start_at" value="{{ old('start_at', $popup->start_at ? $popup->start_at->format('Y-m-d\TH:i') : '') }}" class="input">
                    </div>

                    <div class="form-group">
                        <label class="label">End Date & Time (Optional)</label>
                        <input type="datetime-local" name="end_at" value="{{ old('end_at', $popup->end_at ? $popup->end_at->format('Y-m-d\TH:i') : '') }}" class="input">
                    </div>
                </div>
            </div>
        </div>

        <!-- ACTION BUTTONS -->
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.popups.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" onclick="syncActionTarget()" class="btn-primary">Update Popup</button>
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
