<?php

namespace App\Http\Controllers\Api\V1\Seller;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    /**
     * List all attributes with their values
     */
    public function index()
    {
        $attributes = Attribute::with('values')->get();

        return response()->json([
            'success' => true,
            'data' => $attributes
        ]);
    }
}
