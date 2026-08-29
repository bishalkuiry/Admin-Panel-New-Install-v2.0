<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $store = $request->get('current_store');
        
        $reviews = Review::whereHas('product', function($q) use ($store) {
                $q->where('store_id', $store->id);
            })
            ->with(['user', 'product'])
            ->latest()
            ->paginate(15);

        return view('seller.reviews.index', compact('reviews', 'store'));
    }
}
