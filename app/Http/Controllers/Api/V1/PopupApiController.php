<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Popup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PopupApiController extends Controller
{
    /**
     * Get active popups for mobile app with full configuration payload
     */
    public function index(Request $request): JsonResponse
    {
        $popups = Popup::active()
            ->orderBy('priority', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $data = $popups->map(function ($popup) {
            $mediaUrl = $popup->media_url;
            if (!empty($mediaUrl) && !str_starts_with($mediaUrl, 'http://') && !str_starts_with($mediaUrl, 'https://')) {
                $mediaUrl = asset('storage/' . ltrim($mediaUrl, '/'));
            }

            return [
                'id' => $popup->id,
                'name' => $popup->name,
                'media_url' => $mediaUrl,
                'media_type' => $popup->media_type,
                'status' => $popup->status,
                'position' => $popup->position,
                'show_close_button' => (bool) $popup->show_close_button,
                'click_action' => $popup->click_action,
                'click_action_target' => (string) ($popup->click_action_target ?? ''),
                'display_trigger' => $popup->display_trigger,
                'trigger_value' => (string) ($popup->trigger_value ?? ''),
                'audience_type' => $popup->audience_type,
                'zone_ids' => $popup->zone_ids ?? [],
                'country_ids' => $popup->country_ids ?? [],
                'language_codes' => $popup->language_codes ?? [],
                'store_ids' => $popup->store_ids ?? [],
                'category_ids' => $popup->category_ids ?? [],
                'product_ids' => $popup->product_ids ?? [],
                'priority' => (int) $popup->priority,
                'start_at' => $popup->start_at ? $popup->start_at->toIso8601String() : null,
                'end_at' => $popup->end_at ? $popup->end_at->toIso8601String() : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
