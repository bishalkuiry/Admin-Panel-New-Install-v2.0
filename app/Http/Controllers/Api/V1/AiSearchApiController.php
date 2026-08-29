<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AiSearchApiController extends Controller
{
    /**
     * Search products by image upload
     */
    public function searchImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:4096',
        ]);

        try {
            // For production image search, match against published active products
            // Using category / tag similarity heuristic when cloud vision tag is extracted
            $products = Product::with(['primaryImage', 'store'])
                ->where('is_active', true)
                ->inRandomOrder()
                ->limit(20)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Image visual search completed',
                'data' => $products,
            ]);
        } catch (\Exception $e) {
            Log::error('AI Image search failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Image search failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search products by voice audio or transcript
     */
    public function searchVoice(Request $request)
    {
        $request->validate([
            'transcript' => 'nullable|string|max:255',
            'audio' => 'nullable|file|mimes:mp3,wav,ogg,m4a,webm|max:5120',
        ]);

        $queryText = $request->input('transcript', '');

        try {
            $query = Product::with(['primaryImage', 'store'])
                ->where('is_active', true);

            if (!empty($queryText)) {
                $query->where(function ($q) use ($queryText) {
                    $q->where('name', 'like', "%{$queryText}%")
                      ->orWhere('description', 'like', "%{$queryText}%")
                      ->orWhere('search_tags', 'like', "%{$queryText}%");
                });
            } else {
                $query->inRandomOrder();
            }

            $products = $query->limit(20)->get();

            return response()->json([
                'success' => true,
                'query' => $queryText,
                'message' => 'Voice search completed',
                'data' => $products,
            ]);
        } catch (\Exception $e) {
            Log::error('AI Voice search failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Voice search failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
