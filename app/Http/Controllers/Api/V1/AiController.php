<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AiService;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\Unit;
use App\Models\Tag;
use App\Models\Product;
use App\Models\Setting;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiController extends Controller
{
    public function __construct(protected AiService $aiService) {}

    /**
     * Generate product details for sellers
     */
    public function generateProduct(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $context = [
            'categories' => Category::active()->get(['id', 'name'])->toArray(),
            'attributes' => Attribute::with('values:id,attribute_id,value')->get(['id', 'name'])->toArray(),
            'units' => Unit::all(['id', 'name', 'short_name'])->toArray(),
            'tags' => Tag::all(['id', 'name'])->toArray(),
        ];

        try {
            $details = $this->aiService->generateProductDetails($request->title, $context);
            return response()->json([
                'success' => true,
                'data' => $details
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * AI Chatbot for users
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'history' => 'nullable|array',
            'image' => 'nullable|string', // Base64 image data
        ]);

        // Search for relevant products based on the message to provide context
        $products = Product::search($request->message)
            ->where('is_active', true)
            ->take(20)
            ->get(['id', 'name', 'price', 'description', 'category_id']);

        // Fallback: If no relevant products found, fetch some popular/random active products
        // This ensures the AI always has some context to work with instead of 0 items
        if ($products->isEmpty()) {
            $products = Product::where('is_active', true)
                ->inRandomOrder()
                ->take(10)
                ->get(['id', 'name', 'price', 'description', 'category_id']);
        }

        $user = Auth::guard('sanctum')->user();
        $context = [
            'products' => $products->toArray(),
            'categories' => Category::active()->get(['id', 'name'])->toArray(),
            'user' => $user ? [
                'name' => $user->name,
                'id' => $user->id
            ] : null,
        ];

        $media = [];
        if ($request->image) {
            $media[] = [
                'type' => 'image',
                'data' => $request->image,
                'mime_type' => 'image/jpeg' // Default to jpeg, could be improved to detect
            ];
        }

        try {
            // Check if Mock AI Mode is enabled
            if (Setting::get('mock_ai_enabled')) {
                // Fetch 3 random active products to simulate recommendations
                $mockProducts = Product::where('is_active', true)
                    ->inRandomOrder()
                    ->take(3)
                    ->get();
                
                $itemCards = $mockProducts->map(fn($p) => "[ITEM_CARD:{$p->id}]")->implode("\n");
                
                $response = [
                    'content' => "This is a simulated AI response (Mock Mode is ON).\n\nBased on your request for '{$request->message}', here are some top recommendations:\n\n{$itemCards}\n\nLet me know if you'd like to see more!"
                ];
                
                // Simulate a short network delay for realism
                sleep(1);
            } else {
                $response = $this->aiService->chat($request->message, $request->history ?? [], $context, $media);
            }
            
            // Post-process the response to find item cards and attach product details
            $content = $response['content'];
            $items = [];
            
            preg_match_all('/\[ITEM_CARD:(\d+)\]/', $content, $matches);
            if (!empty($matches[1])) {
                $productIds = $matches[1];
                $productsData = Product::whereIn('id', $productIds)
                    ->with(['primaryImage', 'images', 'category'])
                    ->withCount('reviews')
                    ->withAvg('reviews', 'rating')
                    ->get();
                
                $items = ProductResource::collection($productsData);
            }

            // Clean the content by removing the [ITEM_CARD:ID] tags for the user display
            $cleanContent = preg_replace('/\[ITEM_CARD:\d+\]/', '', $content);

            return response()->json([
                'success' => true,
                'message' => trim($cleanContent),
                'items' => $items
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
