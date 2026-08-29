<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case PACKED = 'packed';
    case PROCESSING = 'processing';
    case PICKED_UP = 'picked_up';
    case OUT_FOR_DELIVERY = 'out_for_delivery';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::CONFIRMED => 'Confirmed',
            self::PACKED => 'Packed',
            self::PROCESSING => 'Processing',
            self::PICKED_UP => 'Picked Up',
            self::OUT_FOR_DELIVERY => 'Out for Delivery',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'bg-amber-100 text-amber-700 border border-amber-200',
            self::CONFIRMED => 'bg-blue-100 text-blue-700 border border-blue-200',
            self::PACKED => 'bg-indigo-100 text-indigo-700 border border-indigo-200',
            self::PROCESSING => 'bg-sky-100 text-sky-700 border border-sky-200',
            self::PICKED_UP => 'bg-purple-100 text-purple-700 border border-purple-200',
            self::OUT_FOR_DELIVERY => 'bg-orange-100 text-orange-700 border border-orange-200',
            self::DELIVERED => 'bg-emerald-100 text-emerald-700 border border-emerald-200',
            self::CANCELLED => 'bg-rose-100 text-rose-700 border border-rose-200',
        };
    }
}
