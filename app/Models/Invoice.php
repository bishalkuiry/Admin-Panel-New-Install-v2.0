<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number', 'order_id', 'store_id', 'user_id',
        'subtotal', 'discount', 'delivery_fee', 'tax', 'total',
        'business_name', 'business_address', 'business_phone', 'business_email', 'business_gstin',
        'customer_name', 'customer_email', 'customer_phone', 'shipping_address',
        'notes', 'status', 'issued_at', 'due_date',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'issued_at' => 'datetime',
        'due_date' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a unique invoice number
     */
    public static function generateNumber(): string
    {
        $prefix = Setting::get('invoice_prefix', 'INV');
        $year = now()->format('Y');
        $month = now()->format('m');
        
        $lastInvoice = self::where('invoice_number', 'like', "{$prefix}-{$year}{$month}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return "{$prefix}-{$year}{$month}-" . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create invoice from an order
     */
    public static function createFromOrder(Order $order): self
    {
        $order->load(['user', 'store', 'address']);

        $address = $order->address
            ? implode(', ', array_filter([
                $order->address->address_line_1,
                $order->address->address_line_2,
                $order->address->city,
                $order->address->state,
                $order->address->postal_code,
                $order->address->country,
            ]))
            : '';

        // Determine business info: use store info if configured + store exists, otherwise use invoice settings
        $useStoreInfo = Setting::get('invoice_show_store_info', '1') === '1' && $order->store;

        $businessName = $useStoreInfo 
            ? ($order->store->name ?? Setting::get('invoice_company_name', config('app.name')))
            : Setting::get('invoice_company_name', config('app.name'));
        $businessAddress = $useStoreInfo 
            ? ($order->store->address ?? Setting::get('invoice_company_address', ''))
            : Setting::get('invoice_company_address', '');
        $businessPhone = $useStoreInfo 
            ? ($order->store->phone ?? Setting::get('invoice_company_phone', ''))
            : Setting::get('invoice_company_phone', '');
        $businessEmail = $useStoreInfo 
            ? ($order->store->email ?? Setting::get('invoice_company_email', ''))
            : Setting::get('invoice_company_email', '');

        return self::create([
            'invoice_number' => self::generateNumber(),
            'order_id' => $order->id,
            'store_id' => $order->store_id,
            'user_id' => $order->user_id,
            'subtotal' => $order->subtotal,
            'discount' => $order->discount,
            'delivery_fee' => $order->delivery_fee ?? 0,
            'tax' => $order->tax ?? 0,
            'total' => $order->total,
            'business_name' => $businessName,
            'business_address' => $businessAddress,
            'business_phone' => $businessPhone,
            'business_email' => $businessEmail,
            'business_gstin' => Setting::get('invoice_gstin', ''),
            'customer_name' => $order->user?->name ?? 'Guest',
            'customer_email' => $order->user?->email ?? '',
            'customer_phone' => $order->user?->phone ?? '',
            'shipping_address' => $address,
            'notes' => Setting::get('invoice_footer_text', ''),
            'status' => 'issued',
            'issued_at' => now(),
        ]);
    }
}
