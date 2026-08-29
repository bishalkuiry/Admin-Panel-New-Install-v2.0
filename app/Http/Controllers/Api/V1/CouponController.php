<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class CouponController extends Controller
{
    /**
     * List all publicly visible, currently valid coupons.
     */
    public function index(): JsonResponse
    {
        $coupons = Cache::remember('public_coupons', 300, function () {
            return Coupon::valid()
                ->select(['id', 'code', 'name', 'type', 'value', 'min_order_amount', 'max_discount', 'expires_at', 'is_first_order_only'])
                ->orderByDesc('value')
                ->get();
        });

        return response()->json([
            'success' => true,
            'data' => $coupons->map(fn($c) => [
                'id'                 => $c->id,
                'code'               => $c->code,
                'name'               => $c->name,
                'type'               => $c->type,
                'value'              => (float) $c->value,
                'min_order_amount'   => $c->min_order_amount ? (float) $c->min_order_amount : null,
                'max_discount'       => $c->max_discount ? (float) $c->max_discount : null,
                'expires_at'         => $c->expires_at?->toDateString(),
                'is_first_order_only'=> $c->is_first_order_only,
            ]),
        ]);
    }
}
