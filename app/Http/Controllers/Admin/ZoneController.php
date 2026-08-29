<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;

class ZoneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $zones = Zone::withCount('stores')->latest()->paginate(10);
        return view('admin.zones.index', compact('zones'));
    }

    /**
     * Create zone form
     */
    public function create()
    {
        $googleMapsApiKey = Setting::get('google_maps_api_key');
        return view('admin.zones.create', compact('googleMapsApiKey'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'currency' => 'nullable|string|max:10',
            'base_delivery_fee' => 'required|numeric|min:0',
            'per_km_fee' => 'required|numeric|min:0',
            'min_order_amount' => 'required|numeric|min:0',
            'coordinates' => 'required|json', // GeoJSON string
            'is_active' => 'boolean',
            'surge_status' => 'boolean',
            'surge_type' => 'required_if:surge_status,true|in:percent,fixed',
            'surge_value' => 'required_if:surge_status,true|numeric|min:0',
            'surge_message' => 'nullable|string|max:255',
        ]);

        $coordinatesJson = $validated['coordinates'];
        $coordinatesArray = json_decode($coordinatesJson, true);

        // Generate slug the same way the model boot does
        $slug = Str::slug(($validated['name'] ?? '') . '-' . ($validated['city'] ?? ''));

        // Insert with area set inline so the NOT NULL constraint is satisfied
        DB::statement(
            "INSERT INTO zones
                (name, slug, city, state, country, currency, coordinates, area,
                 base_delivery_fee, per_km_fee, min_order_amount,
                 is_active, surge_status, surge_type, surge_value, surge_message,
                 created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ST_GeomFromGeoJSON(?),
                     ?, ?, ?,
                     ?, ?, ?, ?, ?,
                     NOW(), NOW())",
            [
                $validated['name'],
                $slug,
                $validated['city'],
                $validated['state'] ?? null,
                $validated['country'] ?? 'India',
                $validated['currency'] ?? null,
                json_encode($coordinatesArray),
                $coordinatesJson,
                $validated['base_delivery_fee'],
                $validated['per_km_fee'],
                $validated['min_order_amount'],
                isset($validated['is_active']) ? (int) $validated['is_active'] : 1,
                isset($validated['surge_status']) ? (int) $validated['surge_status'] : 0,
                $validated['surge_type'] ?? 'percent',
                $validated['surge_value'] ?? 0,
                $validated['surge_message'] ?? null,
            ]
        );

        return redirect()->route('admin.zones.index')->with('success', 'Zone created successfully');
    }

    // ...

    /**
     * Edit zone
     */
    public function edit(Zone $zone)
    {
        $googleMapsApiKey = Setting::get('google_maps_api_key');
        return view('admin.zones.edit', compact('zone', 'googleMapsApiKey'));
    }

    /**
     * Update zone
     */
    public function update(Request $request, Zone $zone)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'currency' => 'nullable|string|max:10',
            'base_delivery_fee' => 'required|numeric|min:0',
            'per_km_fee' => 'required|numeric|min:0',
            'min_order_amount' => 'required|numeric|min:0',
            'coordinates' => 'required|json',
            'is_active' => 'boolean',
            'surge_status' => 'boolean',
            'surge_type' => 'required_if:surge_status,true|in:percent,fixed',
            'surge_value' => 'required_if:surge_status,true|numeric|min:0',
            'surge_message' => 'nullable|string|max:255',
        ]);

        $coordinatesJson = $validated['coordinates'];
        $coordinatesArray = json_decode($coordinatesJson, true);
        unset($validated['coordinates']);

        // Update all regular columns
        $zone->update(array_merge($validated, ['coordinates' => $coordinatesArray]));

        // Update the spatial column separately (cannot use Eloquent for geometry)
        DB::statement(
            "UPDATE zones SET area = ST_GeomFromGeoJSON(?) WHERE id = ?",
            [$coordinatesJson, $zone->id]
        );

        return redirect()->route('admin.zones.index')->with('success', 'Zone updated successfully');
    }

    /**
     * Delete zone
     */
    public function destroy(Zone $zone)
    {
        if ($zone->stores()->count() > 0) {
            return back()->with('error', 'Cannot delete zone with assigned stores');
        }

        $zone->delete();

        return redirect()->route('admin.zones.index')->with('success', 'Zone deleted');
    }

    /**
     * Toggle zone status
     */
    public function toggleStatus(Zone $zone)
    {
        $zone->update(['is_active' => !$zone->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $zone->is_active,
        ]);
    }

    /**
     * Reorder zones
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'zones' => 'required|array',
            'zones.*.id' => 'required|exists:zones,id',
            'zones.*.sort_order' => 'required|integer',
        ]);

        foreach ($request->zones as $item) {
            Zone::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Get stores in zone
     */
    public function stores(Zone $zone)
    {
        $stores = $zone->stores()->with('owner')->paginate(20);
        return view('admin.zones.stores', compact('zone', 'stores'));
    }
}
