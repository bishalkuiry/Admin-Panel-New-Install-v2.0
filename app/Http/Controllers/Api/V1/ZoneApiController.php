<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use Illuminate\Http\Request;

class ZoneApiController extends Controller
{
    /**
     * Get list of active delivery zones for registration and store setup
     */
    public function index(Request $request)
    {
        $zones = Zone::where('is_active', true)
            ->select('id', 'name', 'city', 'state', 'country', 'latitude', 'longitude', 'base_delivery_fee')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $zones,
        ]);
    }
}
