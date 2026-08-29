<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Services\ReferralService;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function __construct(
        private ReferralService $referralService
    ) {}

    /**
     * Get user's referral stats and code
     */
    public function getStats(Request $request)
    {
        $stats = $this->referralService->getStats($request->user());
        
        $referral = Referral::where('referee_id', $request->user()->id)->first();
        $freeDeliveriesRemaining = $referral ? $referral->remaining_free_deliveries : 0;

        return response()->json([
            'success' => true,
            'data' => array_merge($stats, [
                'free_deliveries_remaining' => $freeDeliveriesRemaining,
                'invite_text' => "Use my code {$stats['referral_code']} to join " . config('app.name') . " and get free deliveries!\n\nJoin here: " . url("/invite/{$stats['referral_code']}"),
            ])
        ]);
    }

    /**
     * Get shareable invite link
     */
    public function inviteLink(Request $request)
    {
        $code = $request->user()->referral_code;
        $baseUrl = config('app.url');
        
        return response()->json([
            'success' => true,
            'data' => [
                'referral_code' => $code,
                'invite_link' => "{$baseUrl}/invite/{$code}",
            ]
        ]);
    }
}
