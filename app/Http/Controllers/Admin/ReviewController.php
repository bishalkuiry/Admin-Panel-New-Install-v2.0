<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with(['user', 'product', 'order']);

        if ($request->search) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%");
            })->orWhereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%");
            });
        }

        if ($request->rating) {
            $query->where('rating', $request->rating);
        }

        if ($request->has('status')) {
            if ($request->status === 'approved') {
                $query->approved();
            } elseif ($request->status === 'pending') {
                $query->pending();
            }
        }

        if ($request->has('verified')) {
            $query->where('is_verified_purchase', $request->boolean('verified'));
        }

        $reviews = $query->latest()->paginate(20);

        $stats = [
            'total' => Review::count(),
            'pending' => Review::pending()->count(),
            'approved' => Review::approved()->count(),
            'average' => Review::approved()->avg('rating') ?? 0,
        ];

        return view('admin.reviews.index', compact('reviews', 'stats'));
    }

    public function approve(Review $review)
    {
        $review->update(['is_approved' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Review approved',
        ]);
    }

    public function reject(Review $review)
    {
        $review->update(['is_approved' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Review rejected',
        ]);
    }

    public function destroy(Review $review)
    {
        $review->delete();

        return redirect()->route('admin.reviews.index')->with('success', 'Review deleted');
    }

    public function bulkApprove(Request $request)
    {
        $ids = $request->input('ids', []);
        Review::whereIn('id', $ids)->update(['is_approved' => true]);

        return response()->json([
            'success' => true,
            'message' => count($ids) . ' reviews approved',
        ]);
    }
}
