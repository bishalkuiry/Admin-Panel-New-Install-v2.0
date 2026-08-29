<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Plugins\Website\Http\Controllers\ProductWebController;

class WebProductController extends Controller
{
    /**
     * Show product landing page with full website layout and product details
     */
    public function show(Request $request, $id)
    {
        return app(ProductWebController::class)->show($request, $id);
    }
}
