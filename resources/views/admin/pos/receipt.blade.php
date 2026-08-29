<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt #{{ $order->order_number }}</title>
    <style>
        body { font-family: monospace; font-size: 12px; width: 80mm; margin: 0 auto; padding: 10px; }
        .center { text-align: center; }
        .flex { display: flex; justify-content: space-between; }
        .line { border-bottom: 1px dashed #000; margin: 8px 0; }
        @media print {
            body { width: 100%; margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="center">
        <h2>{{ $order->store?->name ?? 'InAllCart Store' }}</h2>
        <p>{{ $order->store?->address ?? 'Main Branch' }}</p>
        <p>Ph: {{ $order->store?->phone ?? 'Support' }}</p>
    </div>
    
    <div class="line"></div>
    <div class="flex"><span>Order #:</span> <span>{{ $order->order_number }}</span></div>
    <div class="flex"><span>Date:</span> <span>{{ $order->created_at->format('d/m/Y H:i') }}</span></div>
    <div class="flex"><span>Customer:</span> <span>{{ $order->user?->name }}</span></div>
    <div class="line"></div>

    @foreach($order->items as $item)
        <div>
            <div>{{ $item->product_name }}</div>
            <div class="flex">
                <span>{{ $item->quantity }} x ₹{{ number_format($item->price, 2) }}</span>
                <span>₹{{ number_format($item->total, 2) }}</span>
            </div>
        </div>
    @endforeach

    <div class="line"></div>
    <div class="flex"><span>Subtotal:</span> <span>₹{{ number_format($order->subtotal, 2) }}</span></div>
    @if($order->discount > 0)
        <div class="flex"><span>Discount:</span> <span>-₹{{ number_format($order->discount, 2) }}</span></div>
    @endif
    @if($order->tax > 0)
        <div class="flex"><span>Tax:</span> <span>+₹{{ number_format($order->tax, 2) }}</span></div>
    @endif
    <div class="line"></div>
    <div class="flex" style="font-weight: bold; font-size: 14px;">
        <span>TOTAL:</span> <span>₹{{ number_format($order->total, 2) }}</span>
    </div>
    <div class="flex"><span>Payment:</span> <span>{{ strtoupper($order->payment_method) }}</span></div>

    <div class="line"></div>
    <div class="center" style="margin-top: 15px;">
        <p>Thank you for shopping with us!</p>
        <p>Powered by InAllCart Multi-Vendor System</p>
    </div>
</body>
</html>
