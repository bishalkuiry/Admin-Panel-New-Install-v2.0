<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServiceBookingSettingsController extends Controller
{
    /**
     * Show Service Booking Plugin Settings page
     */
    public function index()
    {
        $settings = [
            'module_name'        => Setting::get('service_booking_module_name', 'Service Booking'),
            'module_icon'        => Setting::get('service_booking_module_icon', ''),
            'module_description' => Setting::get('service_booking_module_description', 'Book trusted home services, repairs & cleaning'),
            'is_active'          => Setting::get('service_booking_is_active', '1'),
            'base_price'         => Setting::get('service_booking_base_price', '50.00'),
            'commission_rate'    => Setting::get('service_booking_commission_rate', '10'),
        ];

        return view('admin.plugins.service_booking_settings', compact('settings'));
    }

    /**
     * Update Service Booking Plugin Settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'module_name'        => 'required|string|max:255',
            'module_icon_file'   => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
            'module_icon_url'    => 'nullable|string|max:1000',
            'module_description' => 'nullable|string|max:1000',
            'is_active'          => 'nullable|boolean',
            'base_price'         => 'nullable|numeric|min:0',
            'commission_rate'    => 'nullable|numeric|min:0|max:100',
        ]);

        Setting::set('service_booking_module_name', $request->input('module_name'));
        Setting::set('service_booking_module_description', $request->input('module_description', ''));
        Setting::set('service_booking_is_active', $request->has('is_active') ? '1' : '0');
        Setting::set('service_booking_base_price', $request->input('base_price', '50.00'));
        Setting::set('service_booking_commission_rate', $request->input('commission_rate', '10'));

        // Handle Module Icon Upload
        if ($request->hasFile('module_icon_file')) {
            $path = $request->file('module_icon_file')->store('plugins/icons', 'public');
            $iconUrl = asset('storage/' . $path);
            Setting::set('service_booking_module_icon', $iconUrl);
        } elseif ($request->filled('module_icon_url')) {
            Setting::set('service_booking_module_icon', $request->input('module_icon_url'));
        }

        return back()->with('success', 'Service Booking plugin settings updated successfully.');
    }
}
