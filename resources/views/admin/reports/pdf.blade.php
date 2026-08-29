<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>InAllCart Analytical Report - {{ strtoupper(str_replace('_', ' ', $reportType)) }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #1e293b; margin: 20px; }
        .header { text-align: center; border-b: 2px solid #f97316; padding-bottom: 12px; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #0f172a; font-size: 20px; }
        .header p { margin: 4px 0 0; color: #64748b; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #cbd5e1; padding: 8px 10px; text-align: left; }
        th { background-color: #f8fafc; font-weight: bold; }
        .text-right { text-align: right; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #94a3b8; border-t: 1px solid #e2e8f0; padding-top: 10px; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background-color: #f97316; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">
            Print / Save as PDF
        </button>
    </div>

    <div class="header">
        <h1>InAllCart Multi-Vendor E-Commerce Platform</h1>
        <p>Analytical Report: <strong>{{ strtoupper(str_replace('_', ' ', $reportType)) }}</strong> | Date Range: {{ $startDate }} to {{ $endDate }}</p>
    </div>

    <table>
        @if($reportType === 'sales')
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Orders</th>
                    <th>Gross Sales (₹)</th>
                    <th>Discounts (₹)</th>
                    <th>Tax (₹)</th>
                    <th class="text-right">Net Sales Volume (₹)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                    <tr>
                        <td>{{ $row->date }}</td>
                        <td>{{ $row->total_orders }}</td>
                        <td>₹{{ number_format($row->gross_sales, 2) }}</td>
                        <td>-₹{{ number_format($row->total_discount, 2) }}</td>
                        <td>₹{{ number_format($row->total_tax, 2) }}</td>
                        <td class="text-right font-bold">₹{{ number_format($row->net_sales, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        @elseif($reportType === 'admin_earnings')
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Completed Orders</th>
                    <th>Gross Sales Volume (₹)</th>
                    <th>Tax Collected (₹)</th>
                    <th class="text-right">Admin Commission (15%) (₹)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                    <tr>
                        <td>{{ $row->date }}</td>
                        <td>{{ $row->orders_count }}</td>
                        <td>₹{{ number_format($row->gross_volume, 2) }}</td>
                        <td>₹{{ number_format($row->tax_collected, 2) }}</td>
                        <td class="text-right font-bold">₹{{ number_format($row->admin_commission, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        @elseif($reportType === 'vendor_earnings')
            <thead>
                <tr>
                    <th>Store Name</th>
                    <th>Completed Orders</th>
                    <th>Gross Sales (₹)</th>
                    <th>Admin Commission (15%) (₹)</th>
                    <th class="text-right">Vendor Net Payout (85%) (₹)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                    <tr>
                        <td>{{ $row['store_name'] }}</td>
                        <td>{{ $row['total_orders'] }}</td>
                        <td>₹{{ number_format($row['total_sales'], 2) }}</td>
                        <td>₹{{ number_format($row['admin_commission'], 2) }}</td>
                        <td class="text-right font-bold">₹{{ number_format($row['vendor_earning'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        @else
            <thead>
                <tr>
                    <th>Report Summary</th>
                    <th class="text-right">Total Count</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ str_replace('_', ' ', $reportType) }} records</td>
                    <td class="text-right font-bold">{{ count($data) }} rows</td>
                </tr>
            </tbody>
        @endif
    </table>

    <div class="footer">
        Generated automatically by InAllCart Platform Engine on {{ date('Y-m-d H:i:s') }}
    </div>
</body>
</html>
