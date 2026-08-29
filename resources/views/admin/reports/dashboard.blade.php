<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dashboard Report - {{ $reportDate }}</title>
    <style>
        @page {
            margin: 0cm 0cm;
        }
        body {
            font-family: 'Helvetica', sans-serif;
            color: #334155;
            background-color: #f8fafc;
            margin: 0;
            padding: 2cm;
        }
        .header {
            background-color: #ea580c;
            color: white;
            padding: 40px;
            margin-bottom: 40px;
            border-radius: 0 0 40px 40px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            letter-spacing: -1px;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 20px;
            border-left: 4px solid #ea580c;
            padding-left: 10px;
        }
        .stats-grid {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 15px;
            margin-bottom: 30px;
        }
        .stat-card {
            display: table-cell;
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            width: 25%;
            border: 1px solid #e2e8f0;
        }
        .stat-card .label {
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-card .value {
            font-size: 20px;
            font-weight: bold;
            color: #0f172a;
        }
        
        .chart-container {
            background: white;
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 40px;
            border: 1px solid #e2e8f0;
        }
        .chart-row {
            margin-bottom: 15px;
        }
        .chart-label {
            font-size: 11px;
            color: #64748b;
            margin-bottom: 5px;
        }
        .chart-bar-bg {
            background: #f1f5f9;
            height: 12px;
            border-radius: 6px;
            width: 100%;
            overflow: hidden;
        }
        .chart-bar-fill {
            background: linear-gradient(90deg, #f97316, #ea580c);
            height: 100%;
            border-radius: 6px;
        }
        
        .footer {
            position: fixed;
            bottom: 30px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
        }
        
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 30px;
            border: 1px solid #e2e8f0;
        }
        table.data-table th {
            background: #f8fafc;
            padding: 12px 15px;
            text-align: left;
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            border-bottom: 1px solid #e2e8f0;
        }
        table.data-table td {
            padding: 12px 15px;
            font-size: 12px;
            border-bottom: 1px solid #f1f5f9;
        }
        .total-row {
            background: #fff7ed;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Platform Performance Report</h1>
        <p>Generated on {{ $reportDate }} at {{ $generatedAt }}</p>
    </div>

    <div class="section-title">Key Performance Indicators</div>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="label">Gross Revenue</div>
            <div class="value">{{ \App\Helpers\CurrencyHelper::format($stats['total_revenue']) }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Total Commission</div>
            <div class="value">{{ \App\Helpers\CurrencyHelper::format($stats['total_commission']) }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Active Stores</div>
            <div class="value">{{ $stats['active_stores'] }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Total Customers</div>
            <div class="value">{{ $stats['total_customers'] }}</div>
        </div>
    </div>

    <div class="section-title">Revenue Trends (Last 7 Days)</div>
    <div class="chart-container">
        @php
            $maxRevenue = max($chartData['data']) ?: 1;
        @endphp
        @foreach($chartData['labels'] as $index => $label)
            @php
                $revenue = $chartData['data'][$index];
                $percentage = ($revenue / $maxRevenue) * 100;
            @endphp
            <div class="chart-row">
                <div class="chart-label">
                    <span style="float: left;">{{ $label }}</span>
                    <span style="float: right; font-weight: bold;">{{ \App\Helpers\CurrencyHelper::format($revenue) }}</span>
                    <div style="clear: both;"></div>
                </div>
                <div class="chart-bar-bg">
                    <div class="chart-bar-fill" style="width: {{ $percentage }}%;"></div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="section-title">Operational Overview</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Category</th>
                <th>Metric</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Products</td>
                <td>{{ number_format($stats['total_products']) }}</td>
            </tr>
            <tr>
                <td>Active Products</td>
                <td>{{ number_format($stats['active_products']) }}</td>
            </tr>
            <tr>
                <td>Pending Orders</td>
                <td>{{ number_format($stats['pending_orders']) }}</td>
            </tr>
            <tr>
                <td>Processing Orders</td>
                <td>{{ number_format($stats['processing_orders']) }}</td>
            </tr>
            <tr class="total-row">
                <td>Est. Platform Growth</td>
                <td>78%</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Confidential Platform Report &copy; {{ date('Y') }} InAllCart. All rights reserved.
    </div>
</body>
</html>
