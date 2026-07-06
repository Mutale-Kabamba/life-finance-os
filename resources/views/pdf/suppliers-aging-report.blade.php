<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Suppliers Aging Report</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #111827;
            font-size: 12px;
            margin: 0;
            padding: 26px 30px 40px;
        }
        .header { margin-bottom: 14px; }
        .title { font-size: 20px; font-weight: 700; margin: 0; }
        .subtitle { color: #6b7280; margin-top: 4px; }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th {
            text-align: left;
            background: #0f766e;
            color: #fff;
            padding: 7px;
            font-size: 11px;
        }
        .table td {
            padding: 7px;
            border-bottom: 1px solid #e5e7eb;
        }
        .right { text-align: right; }
        .watermark {
            position: fixed;
            left: 10px;
            bottom: 6px;
            font-size: 10px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    @php($money = fn ($v) => 'ZMW ' . number_format((float) ($v ?? 0), 2))

    <div class="header">
        <p class="title">Suppliers Aging Report</p>
        <div class="subtitle">
            {{ $business->name }} | As of: {{ \Illuminate\Support\Carbon::parse($asOf)->format('d M Y') }}
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Supplier</th>
                <th class="right">Current</th>
                <th class="right">1-30</th>
                <th class="right">31-60</th>
                <th class="right">61-90</th>
                <th class="right">90+</th>
                <th class="right">Total due</th>
            </tr>
        </thead>
        <tbody>
            @forelse (($report['suppliers'] ?? []) as $row)
                <tr>
                    <td>{{ $row['supplier'] }}</td>
                    <td class="right">{{ $money($row['current']) }}</td>
                    <td class="right">{{ $money($row['days_1_30']) }}</td>
                    <td class="right">{{ $money($row['days_31_60']) }}</td>
                    <td class="right">{{ $money($row['days_61_90']) }}</td>
                    <td class="right">{{ $money($row['days_90_plus']) }}</td>
                    <td class="right"><strong>{{ $money($row['total_due']) }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No outstanding supplier balances.</td>
                </tr>
            @endforelse
            <tr>
                <td><strong>Totals</strong></td>
                <td class="right"><strong>{{ $money($report['totals']['current'] ?? 0) }}</strong></td>
                <td class="right"><strong>{{ $money($report['totals']['days_1_30'] ?? 0) }}</strong></td>
                <td class="right"><strong>{{ $money($report['totals']['days_31_60'] ?? 0) }}</strong></td>
                <td class="right"><strong>{{ $money($report['totals']['days_61_90'] ?? 0) }}</strong></td>
                <td class="right"><strong>{{ $money($report['totals']['days_90_plus'] ?? 0) }}</strong></td>
                <td class="right"><strong>{{ $money($report['totals']['total_due'] ?? 0) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="watermark">(c)2026 Ori Studio Systems</div>
</body>
</html>
