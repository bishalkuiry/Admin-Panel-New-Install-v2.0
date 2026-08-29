<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    @php
        $invoiceLogo = \App\Models\Setting::get('invoice_logo', '');
        $showLogo = \App\Models\Setting::get('invoice_show_logo', '1') === '1';
        $accentColor = \App\Models\Setting::get('invoice_accent_color', '#111827');
        $footerText = \App\Models\Setting::get('invoice_footer_text', 'Thank you for your business!');
        $termsText = \App\Models\Setting::get('invoice_terms', '');
    @endphp
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #1a1a1a; font-size: 13px; line-height: 1.5; background: #fff; }
        .invoice-container { max-width: 800px; margin: 0 auto; padding: 40px; }

        /* Header */
        .invoice-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; padding-bottom: 24px; border-bottom: 3px solid {{ $accentColor }}; }
        .business-info { display: flex; align-items: flex-start; gap: 16px; }
        .business-logo { width: 60px; height: 60px; object-fit: contain; border-radius: 8px; }
        .business-info h1 { font-size: 22px; font-weight: 800; color: {{ $accentColor }}; margin-bottom: 4px; }
        .business-info p { font-size: 12px; color: #555; line-height: 1.6; }
        .business-info .gstin { font-weight: 600; color: #333; margin-top: 4px; }
        .invoice-title { text-align: right; }
        .invoice-title h2 { font-size: 32px; font-weight: 900; color: {{ $accentColor }}20; text-transform: uppercase; letter-spacing: 4px; }
        .invoice-title .invoice-number { font-size: 14px; font-weight: 700; color: {{ $accentColor }}; margin-top: 6px; }
        .invoice-title .invoice-date { font-size: 12px; color: #666; }

        /* Bill To */
        .bill-to { margin-bottom: 32px; }
        .bill-to .label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; color: {{ $accentColor }}; margin-bottom: 6px; }
        .bill-to .name { font-weight: 700; font-size: 15px; color: #111; }
        .bill-to p { font-size: 12px; color: #555; }

        /* Items Table */
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 32px; }
        .items-table thead th { text-align: left; padding: 10px 12px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #fff; background: {{ $accentColor }}; }
        .items-table thead th:first-child { border-radius: 6px 0 0 6px; }
        .items-table thead th:last-child { border-radius: 0 6px 6px 0; }
        .items-table thead th:nth-child(2) { text-align: center; }
        .items-table thead th:nth-child(3),
        .items-table thead th:nth-child(4) { text-align: right; }
        .items-table tbody td { padding: 12px; border-bottom: 1px solid #f3f4f6; font-size: 13px; }
        .items-table tbody td:nth-child(2) { text-align: center; }
        .items-table tbody td:nth-child(3),
        .items-table tbody td:nth-child(4) { text-align: right; }
        .items-table .item-name { font-weight: 600; color: #111; }
        .items-table .item-variant { font-size: 11px; color: #888; }
        .items-table .item-total { font-weight: 700; }

        /* Totals */
        .totals-section { display: flex; justify-content: flex-end; margin-bottom: 32px; }
        .totals-box { width: 280px; }
        .totals-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 13px; }
        .totals-row.discount .value { color: #059669; }
        .totals-row .label { color: #666; }
        .totals-row .value { color: #111; }
        .totals-row.grand-total { border-top: 3px solid {{ $accentColor }}; padding-top: 12px; margin-top: 8px; font-size: 16px; font-weight: 800; }
        .totals-row.grand-total .label,
        .totals-row.grand-total .value { color: {{ $accentColor }}; }

        /* Notes & Terms */
        .notes-section { margin-top: 32px; padding-top: 20px; border-top: 1px solid #e5e7eb; }
        .notes-section .label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; color: {{ $accentColor }}; margin-bottom: 6px; }
        .notes-section p { font-size: 12px; color: #555; }

        /* Footer */
        .invoice-footer { margin-top: 40px; padding-top: 16px; border-top: 2px solid {{ $accentColor }}15; display: flex; justify-content: space-between; align-items: center; }
        .invoice-footer .generated { font-size: 10px; color: #999; }
        .invoice-footer .footer-text { font-size: 11px; color: #666; font-style: italic; text-align: center; flex: 1; }
        .status-badge { display: inline-block; padding: 6px 16px; border-radius: 999px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-issued { background: #dbeafe; color: #1e40af; }
        .status-draft { background: #fef3c7; color: #92400e; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }

        /* Print-specific */
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .invoice-container { padding: 20px; }
            .no-print { display: none !important; }
            @page { margin: 15mm; size: A4; }
        }

        /* Screen-only toolbar */
        .print-toolbar { background: #f9fafb; border-bottom: 1px solid #e5e7eb; padding: 12px 40px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100; }
        .print-toolbar button, .print-toolbar a { padding: 8px 20px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; border: none; transition: all 0.15s; text-decoration: none; display: inline-block; }
        .print-toolbar .btn-print { background: {{ $accentColor }}; color: #fff; }
        .print-toolbar .btn-print:hover { opacity: 0.9; }
        .print-toolbar .btn-back { background: #fff; color: #555; border: 1px solid #d1d5db; }
        .print-toolbar .btn-back:hover { background: #f3f4f6; }
    </style>
</head>
<body>
    <!-- Print Toolbar -->
    <div class="print-toolbar no-print">
        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn-back">← Back</a>
            <span style="font-size: 13px; color: #666;">{{ $invoice->invoice_number }}</span>
        </div>
        <button class="btn-print" onclick="window.print()">🖨  Print Invoice</button>
    </div>

    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="business-info">
                @if($showLogo && $invoiceLogo)
                    <img src="{{ storage_url($invoiceLogo) }}" alt="Logo" class="business-logo">
                @endif
                <div>
                    <h1>{{ $invoice->business_name ?? 'Business Name' }}</h1>
                    @if($invoice->business_address)<p>{{ $invoice->business_address }}</p>@endif
                    @if($invoice->business_phone)<p>{{ $invoice->business_phone }}</p>@endif
                    @if($invoice->business_email)<p>{{ $invoice->business_email }}</p>@endif
                    @if($invoice->business_gstin)<p class="gstin">GSTIN: {{ $invoice->business_gstin }}</p>@endif
                </div>
            </div>
            <div class="invoice-title">
                <h2>Invoice</h2>
                <p class="invoice-number">{{ $invoice->invoice_number }}</p>
                <p class="invoice-date">Date: {{ $invoice->issued_at ? $invoice->issued_at->format('d M Y') : $invoice->created_at->format('d M Y') }}</p>
                @if($invoice->due_date)<p class="invoice-date">Due: {{ $invoice->due_date->format('d M Y') }}</p>@endif
            </div>
        </div>

        <!-- Bill To -->
        <div class="bill-to">
            <p class="label">Bill To</p>
            <p class="name">{{ $invoice->customer_name }}</p>
            @if($invoice->customer_email)<p>{{ $invoice->customer_email }}</p>@endif
            @if($invoice->customer_phone)<p>{{ $invoice->customer_phone }}</p>@endif
            @if($invoice->shipping_address)<p>{{ $invoice->shipping_address }}</p>@endif
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @if($invoice->order && $invoice->order->items)
                    @foreach($invoice->order->items as $item)
                    <tr>
                        <td>
                            <span class="item-name">{{ $item->product_name }}</span>
                            @if($item->variant_name)<br><span class="item-variant">{{ $item->variant_name }}</span>@endif
                        </td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ \App\Helpers\CurrencyHelper::format($item->price) }}</td>
                        <td class="item-total">{{ \App\Helpers\CurrencyHelper::format($item->price * $item->quantity) }}</td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="4" style="text-align: center; color: #aaa; padding: 24px;">No items available</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <div class="totals-box">
                <div class="totals-row">
                    <span class="label">Subtotal</span>
                    <span class="value">{{ \App\Helpers\CurrencyHelper::format($invoice->subtotal) }}</span>
                </div>
                @if($invoice->discount > 0)
                <div class="totals-row discount">
                    <span class="label">Discount</span>
                    <span class="value">-{{ \App\Helpers\CurrencyHelper::format($invoice->discount) }}</span>
                </div>
                @endif
                @if($invoice->delivery_fee > 0)
                <div class="totals-row">
                    <span class="label">Delivery Fee</span>
                    <span class="value">{{ \App\Helpers\CurrencyHelper::format($invoice->delivery_fee) }}</span>
                </div>
                @endif
                @if(($invoice->driver_tip ?? $invoice->order?->driver_tip ?? 0) > 0)
                <div class="totals-row">
                    <span class="label">🛵 Rider Tip</span>
                    <span class="value">+{{ \App\Helpers\CurrencyHelper::format($invoice->driver_tip ?? $invoice->order?->driver_tip) }}</span>
                </div>
                @endif
                @if($invoice->tax > 0)
                <div class="totals-row">
                    <span class="label">Tax</span>
                    <span class="value">{{ \App\Helpers\CurrencyHelper::format($invoice->tax) }}</span>
                </div>
                @endif
                <div class="totals-row grand-total">
                    <span class="label">Total</span>
                    <span class="value">{{ \App\Helpers\CurrencyHelper::format($invoice->total) }}</span>
                </div>
            </div>
        </div>

        <!-- Notes -->
        @if($invoice->notes)
        <div class="notes-section">
            <p class="label">Notes</p>
            <p>{{ $invoice->notes }}</p>
        </div>
        @endif

        <!-- Terms -->
        @if($termsText)
        <div class="notes-section" style="margin-top: 16px;">
            <p class="label">Terms & Conditions</p>
            <p>{{ $termsText }}</p>
        </div>
        @endif

        <!-- Footer -->
        <div class="invoice-footer">
            <span class="generated">Generated on {{ $invoice->created_at->format('d M Y, h:i A') }}</span>
            @if($footerText)
                <span class="footer-text">{{ $footerText }}</span>
            @endif
            <span class="status-badge status-{{ $invoice->status }}">
                {{ $invoice->status === 'paid' ? '✓ ' : '' }}{{ ucfirst($invoice->status) }}
            </span>
        </div>
    </div>
</body>
</html>
