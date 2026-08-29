<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class RideSharingSettingsController extends Controller
{
    /**
     * Show Ride Sharing Plugin Settings page
     */
    public function index()
    {
        $settings = [
            'module_name'        => Setting::get('ride_sharing_module_name', 'Ride Sharing'),
            'module_icon'        => Setting::get('ride_sharing_module_icon', ''),
            'module_description' => Setting::get('ride_sharing_module_description', 'Book cabs, taxis & parcel rides instantly'),
            'is_active'          => Setting::get('ride_sharing_is_active', '1'),
            'base_fare'          => Setting::get('ride_sharing_base_fare', '40.00'),
            'per_km_rate'        => Setting::get('ride_sharing_per_km_rate', '12.00'),
        ];

        return view('admin.plugins.ride_sharing_settings', compact('settings'));
    }

    /**
     * Update Ride Sharing Plugin Settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'module_name'        => 'required|string|max:255',
            'module_icon_file'   => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
            'module_icon_url'    => 'nullable|string|max:1000',
            'module_description' => 'nullable|string|max:1000',
            'is_active'          => 'nullable|boolean',
            'base_fare'          => 'nullable|numeric|min:0',
            'per_km_rate'        => 'nullable|numeric|min:0',
        ]);

        Setting::set('ride_sharing_module_name', $request->input('module_name'));
        Setting::set('ride_sharing_module_description', $request->input('module_description', ''));
        Setting::set('ride_sharing_is_active', $request->has('is_active') ? '1' : '0');
        Setting::set('ride_sharing_base_fare', $request->input('base_fare', '40.00'));
        Setting::set('ride_sharing_per_km_rate', $request->input('per_km_rate', '12.00'));

        // Handle Module Icon Upload
        if ($request->hasFile('module_icon_file')) {
            $path = app(\App\Services\StorageService::class)->store($request->file('module_icon_file'), 'plugins/icons');
            $iconUrl = storage_url($path);
            Setting::set('ride_sharing_module_icon', $iconUrl);
        } elseif ($request->filled('module_icon_url')) {
            Setting::set('ride_sharing_module_icon', $request->input('module_icon_url'));
        }

        return back()->with('success', 'Ride Sharing plugin settings updated successfully.');
    }
}
