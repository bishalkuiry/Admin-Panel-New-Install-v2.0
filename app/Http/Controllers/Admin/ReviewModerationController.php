<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewModerationController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with(['user', 'order', 'product'])->latest();

        if ($request->filled('status')) {
            if ($request->status === 'approved') {
                $query->where('is_approved', 1);
            } elseif ($request->status === 'pending') {
                $query->where('is_approved', 0);
            }
        }

        if ($request->filled('type')) {
            if ($request->type === 'product') {
                $query->whereNotNull('product_id');
            } elseif ($request->type === 'driver') {
                $query->whereNotNull('delivery_rating');
            }
        }

        $reviews = $query->paginate(20);

        return view('admin.reviews.index', compact('reviews'));
    }

    public function updateStatus(Request $request, Review $review)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved',
        ]);

        $review->update([
            'is_approved' => $validated['status'] === 'approved' ? 1 : 0,
        ]);

        return redirect()->back()->with('success', "Review #{$review->id} status updated to {$validated['status']}");
    }

    public function updateSettings(Request $request)
    {
        \App\Models\Setting::set('show_star_rating', $request->has('show_star_rating') ? '1' : '0');
        \App\Models\Setting::set('show_review_count', $request->has('show_review_count') ? '1' : '0');

        return redirect()->back()->with('success', 'Rating and Review display settings saved successfully!');
    }

    public function destroy(Review $review)
    {
        $review->delete();
        return redirect()->back()->with('success', 'Review deleted successfully!');
    }
}
