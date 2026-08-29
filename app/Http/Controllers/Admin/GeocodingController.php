<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeocodingController extends Controller
{
    /**
     * Search for locations using Nominatim geocoding API
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:200',
        ]);

        try {
            // Call Nominatim API with proper headers
            $response = Http::withHeaders([
                'User-Agent' => 'InAllCart Store Management',
                'Accept' => 'application/json',
            ])->get('https://nominatim.openstreetmap.org/search', [
                'format' => 'json',
                'q' => $request->q,
                'limit' => 5,
                'addressdetails' => 1,
            ]);

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'error' => 'Failed to fetch results'
            ], $response->status());

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reverse geocoding - get address from coordinates
     */
    public function reverse(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lon' => 'required|numeric|between:-180,180',
        ]);

        try {
            // Call Nominatim reverse geocoding API
            $response = Http::withHeaders([
                'User-Agent' => 'InAllCart Store Management',
                'Accept' => 'application/json',
            ])->get('https://nominatim.openstreetmap.org/reverse', [
                'format' => 'json',
                'lat' => $request->lat,
                'lon' => $request->lon,
                'addressdetails' => 1,
            ]);

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'error' => 'Failed to fetch address'
            ], $response->status());

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Reverse geocoding failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
