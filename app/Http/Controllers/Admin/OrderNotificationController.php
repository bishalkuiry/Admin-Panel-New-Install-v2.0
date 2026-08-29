<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OrderNotificationController extends Controller
{
    /**
     * Poll latest orders across InAllCart core store and active plugins
     */
    public function checkNewOrders(Request $request)
    {
        $lastOrderSeenId   = (int) $request->query('last_order_id', 0);
        $lastRideSeenId    = (int) $request->query('last_ride_id', 0);
        $lastBookingSeenId = (int) $request->query('last_booking_id', 0);

        $newNotifications = [];

        // 1. Check main E-Commerce Store Orders
        $maxOrderId = $lastOrderSeenId;
        if (Schema::hasTable('orders')) {
            $latestOrder = DB::table('orders')
                ->select('id', 'order_number', 'total', 'created_at')
                ->orderBy('id', 'desc')
                ->first();

            if ($latestOrder) {
                if ($lastOrderSeenId > 0 && $latestOrder->id > $lastOrderSeenId) {
                    $newNotifications[] = [
                        'type'   => 'order',
                        'icon'   => '🛍️',
                        'id'     => $latestOrder->id,
                        'title'  => 'New E-Commerce Order!',
                        'number' => $latestOrder->order_number ?? ('#' . $latestOrder->id),
                        'amount' => '$' . number_format($latestOrder->total ?? 0, 2),
                        'time'   => 'Just now',
                        'url'    => route('admin.dispatch.index'),
                    ];
                }
                $maxOrderId = $latestOrder->id;
            }
        }

        // 2. Check Ride Sharing Plugin
        $maxRideId = $lastRideSeenId;
        if (Schema::hasTable('rides')) {
            $latestRide = DB::table('rides')
                ->select('id', 'ride_number', 'total_fare', 'created_at')
                ->orderBy('id', 'desc')
                ->first();

            if ($latestRide) {
                if ($lastRideSeenId > 0 && $latestRide->id > $lastRideSeenId) {
                    $newNotifications[] = [
                        'type'   => 'ride',
                        'icon'   => '🚖',
                        'id'     => $latestRide->id,
                        'title'  => 'New Ride Booking!',
                        'number' => $latestRide->ride_number ?? ('RIDE-' . $latestRide->id),
                        'amount' => '$' . number_format($latestRide->total_fare ?? 0, 2),
                        'time'   => 'Just now',
                        'url'    => route('admin.dispatch.index'),
                    ];
                }
                $maxRideId = $latestRide->id;
            }
        }

        // 3. Check Service Booking Plugin
        $maxBookingId = $lastBookingSeenId;
        if (Schema::hasTable('service_bookings')) {
            $latestBooking = DB::table('service_bookings')
                ->select('id', 'booking_number', 'total_amount', 'created_at')
                ->orderBy('id', 'desc')
                ->first();

            if ($latestBooking) {
                if ($lastBookingSeenId > 0 && $latestBooking->id > $lastBookingSeenId) {
                    $newNotifications[] = [
                        'type'   => 'service',
                        'icon'   => '🛠️',
                        'id'     => $latestBooking->id,
                        'title'  => 'New Service Booking!',
                        'number' => $latestBooking->booking_number ?? ('SRV-' . $latestBooking->id),
                        'amount' => '$' . number_format($latestBooking->total_amount ?? 0, 2),
                        'time'   => 'Just now',
                        'url'    => route('admin.dispatch.index'),
                    ];
                }
                $maxBookingId = $latestBooking->id;
            }
        }

        return response()->json([
            'has_new'           => count($newNotifications) > 0,
            'notifications'     => $newNotifications,
            'latest_order_id'   => $maxOrderId,
            'latest_ride_id'    => $maxRideId,
            'latest_booking_id' => $maxBookingId,
        ]);
    }
}
