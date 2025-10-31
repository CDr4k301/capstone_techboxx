<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            font-size: 24px;
            margin: 0;
            color: #333;
        }
        .header .subtitle {
            font-size: 16px;
            color: #666;
            margin: 5px 0;
        }
        .header .date {
            font-size: 14px;
            color: #888;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        .summary-item {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .summary-label {
            font-size: 11px;
            color: #666;
            margin-bottom: 5px;
        }
        .summary-value {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .table th {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            font-size: 11px;
        }
        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .currency {
            font-family: 'Courier New', monospace;
        }
        .no-data {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sales Report</h1>
        <div class="subtitle">Period: {{ ucfirst($period) }}</div>
        <div class="date">Generated on: {{ $generatedDate }}</div>
    </div>

    <div class="section">
        <div class="section-title">Summary</div>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Components Sold</div>
                <div class="summary-value">{{ number_format($summary['total_sold']) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Cost of Goods Sold</div>
                <div class="summary-value currency">₱{{ number_format($summary['cost_of_goods'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Revenue</div>
                <div class="summary-value currency">₱{{ number_format($summary['revenue'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Profit</div>
                <div class="summary-value currency">₱{{ number_format($summary['profit'], 2) }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Top Selling Products</div>
        @if($products->count() > 0)
            <table class="table">
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="45%">Product Name</th>
                        <th width="15%" class="text-center">Type</th>
                        <th width="15%" class="text-center">Sold</th>
                        <th width="20%" class="text-right">Earnings</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $index => $product)
                        @php
                            $earnings = ($product['selling_price'] * $product['total_sold']) - ($product['base_price'] * $product['total_sold']);
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $product['product_name'] }}</td>
                            <td class="text-center">{{ $product['product_type'] }}</td>
                            <td class="text-center">{{ $product['total_sold'] }}</td>
                            <td class="text-right currency">₱{{ number_format($earnings, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">No products found for this period.</div>
        @endif
    </div>

    <div class="footer">
        Sales Report generated by PCBuilder Pro System
    </div>
</body>
</html>