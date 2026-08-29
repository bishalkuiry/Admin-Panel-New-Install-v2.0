<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StoreStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Language;
use App\Models\Popup;
use App\Models\Product;
use App\Models\Store;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PopupController extends Controller
{
    /**
     * Display a listing of popups with search and filters.
     */
    public function index(Request $request)
    {
        $query = Popup::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('trigger')) {
            $query->where('display_trigger', $request->trigger);
        }

        if ($request->filled('audience')) {
            $query->where('audience_type', $request->audience);
        }

        $popups = $query->orderBy('priority', 'desc')->orderBy('id', 'desc')->paginate(15);

        return view('admin.popups.index', compact('popups'));
    }

    /**
     * Show the form for creating a new popup.
     */
    public function create()
    {
        $products = Product::where('is_active', true)->select('id', 'name')->orderBy('name')->get();
        $categories = Category::where('is_active', true)->select('id', 'name')->orderBy('name')->get();
        $stores = Store::where('status', StoreStatus::ACTIVE)->select('id', 'name')->orderBy('name')->get();
        $zones = Zone::where('is_active', true)->select('id', 'name')->orderBy('name')->get();
        $coupons = Coupon::where('is_active', true)->select('id', 'code')->orderBy('code')->get();
        $languages = Language::where('is_active', true)->select('id', 'code', 'name')->get();

        return view('admin.popups.create', compact('products', 'categories', 'stores', 'zones', 'coupons', 'languages'));
    }

    /**
     * Store a newly created popup in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'media' => 'required|file|mimes:jpeg,png,jpg,webp,gif|max:10240',
            'status' => 'required|in:draft,scheduled,active,expired,inactive',
            'position' => 'required|in:center,top,bottom,floating,full_screen',
            'show_close_button' => 'nullable|boolean',
            'click_action' => 'required|in:none,url,page,product,category,store,coupon,membership',
            'click_action_target' => 'nullable|string|max:1000',
            'display_trigger' => 'required|in:first_app_open,second_app_open,every_app_open,once_per_day,once_per_session,once_per_user,after_x_seconds,after_x_opens,on_app_exit,after_order_completion,after_login,before_checkout,after_checkout,specific_product_in_cart,cart_amount_reached',
            'trigger_value' => 'nullable|string|max:255',
            'audience_type' => 'required|in:all,new,existing,vip,non_vip',
            'zone_ids' => 'nullable|array',
            'country_ids' => 'nullable|array',
            'language_codes' => 'nullable|array',
            'store_ids' => 'nullable|array',
            'category_ids' => 'nullable|array',
            'product_ids' => 'nullable|array',
            'priority' => 'required|integer|min:0',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
        ]);

        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $extension = strtolower($file->getClientOriginalExtension());
            $mediaType = ($extension === 'gif') ? 'gif' : 'image';
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $path = app(\App\Services\StorageService::class)->store($file, 'popups');
            $validated['media_url'] = $path;
            $validated['media_type'] = $mediaType;
        }

        $validated['show_close_button'] = $request->boolean('show_close_button');
        $validated['zone_ids'] = $request->input('zone_ids', []);
        $validated['country_ids'] = $request->input('country_ids', []);
        $validated['language_codes'] = $request->input('language_codes', []);
        $validated['store_ids'] = $request->input('store_ids', []);
        $validated['category_ids'] = $request->input('category_ids', []);
        $validated['product_ids'] = $request->input('product_ids', []);

        Popup::create($validated);

        return redirect()->route('admin.popups.index')->with('success', 'Popup created successfully.');
    }

    /**
     * Show the form for editing the specified popup.
     */
    public function edit(Popup $popup)
    {
        $products = Product::where('is_active', true)->select('id', 'name')->orderBy('name')->get();
        $categories = Category::where('is_active', true)->select('id', 'name')->orderBy('name')->get();
        $stores = Store::where('status', StoreStatus::ACTIVE)->select('id', 'name')->orderBy('name')->get();
        $zones = Zone::where('is_active', true)->select('id', 'name')->orderBy('name')->get();
        $coupons = Coupon::where('is_active', true)->select('id', 'code')->orderBy('code')->get();
        $languages = Language::where('is_active', true)->select('id', 'code', 'name')->get();

        return view('admin.popups.edit', compact('popup', 'products', 'categories', 'stores', 'zones', 'coupons', 'languages'));
    }

    /**
     * Update the specified popup in storage.
     */
    public function update(Request $request, Popup $popup)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'media' => 'nullable|file|mimes:jpeg,png,jpg,webp,gif|max:10240',
            'status' => 'required|in:draft,scheduled,active,expired,inactive',
            'position' => 'required|in:center,top,bottom,floating,full_screen',
            'show_close_button' => 'nullable|boolean',
            'click_action' => 'required|in:none,url,page,product,category,store,coupon,membership',
            'click_action_target' => 'nullable|string|max:1000',
            'display_trigger' => 'required|in:first_app_open,second_app_open,every_app_open,once_per_day,once_per_session,once_per_user,after_x_seconds,after_x_opens,on_app_exit,after_order_completion,after_login,before_checkout,after_checkout,specific_product_in_cart,cart_amount_reached',
            'trigger_value' => 'nullable|string|max:255',
            'audience_type' => 'required|in:all,new,existing,vip,non_vip',
            'zone_ids' => 'nullable|array',
            'country_ids' => 'nullable|array',
            'language_codes' => 'nullable|array',
            'store_ids' => 'nullable|array',
            'category_ids' => 'nullable|array',
            'product_ids' => 'nullable|array',
            'priority' => 'required|integer|min:0',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
        ]);

        if ($request->hasFile('media')) {
            // Remove old media file
            if (!empty($popup->media_url)) {
                app(\App\Services\StorageService::class)->delete($popup->media_url);
            }
            $file = $request->file('media');
            $extension = strtolower($file->getClientOriginalExtension());
            $mediaType = ($extension === 'gif') ? 'gif' : 'image';
            $path = app(\App\Services\StorageService::class)->store($file, 'popups');
            $validated['media_url'] = $path;
            $validated['media_type'] = $mediaType;
        }

        $validated['show_close_button'] = $request->boolean('show_close_button');
        $validated['zone_ids'] = $request->input('zone_ids', []);
        $validated['country_ids'] = $request->input('country_ids', []);
        $validated['language_codes'] = $request->input('language_codes', []);
        $validated['store_ids'] = $request->input('store_ids', []);
        $validated['category_ids'] = $request->input('category_ids', []);
        $validated['product_ids'] = $request->input('product_ids', []);

        $popup->update($validated);

        return redirect()->route('admin.popups.index')->with('success', 'Popup updated successfully.');
    }

    /**
     * Remove the specified popup from storage.
     */
    public function destroy(Popup $popup)
    {
        $popup->delete();
        return redirect()->route('admin.popups.index')->with('success', 'Popup deleted successfully.');
    }

    /**
     * Fast toggle status (active / inactive).
     */
    public function toggleStatus(Popup $popup)
    {
        $popup->status = ($popup->status === 'active') ? 'inactive' : 'active';
        $popup->save();

        return response()->json([
            'success' => true,
            'status' => $popup->status,
            'message' => 'Popup status updated successfully.'
        ]);
    }
}
