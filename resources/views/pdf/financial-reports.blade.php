<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Financial Reports</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #111827;
            font-size: 12px;
            margin: 0;
            padding: 26px 30px 40px;
        }
        .header { margin-bottom: 16px; }
        .title { font-size: 20px; font-weight: 700; margin: 0; }
        .subtitle { color: #6b7280; margin-top: 4px; }
        .section { margin-top: 16px; }
        .section h2 {
            font-size: 14px;
            margin: 0 0 8px;
            color: #0f766e;
        }
        .kv {
            width: 100%;
            border-collapse: collapse;
        }
        .kv td {
            padding: 6px 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        .kv td:last-child { text-align: right; font-weight: 600; }
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
        <p class="title">Financial Reports</p>
        <div class="subtitle">
            {{ $business->name }} | Period: {{ \Illuminate\Support\Carbon::parse($start)->format('d M Y') }} - {{ \Illuminate\Support\Carbon::parse($end)->format('d M Y') }}
        </div>
    </div>

    <div class="section">
        <h2>Income Statement</h2>
        <table class="kv">
            <tr><td>Total income</td><td>{{ $money($incomeStatement['total_income'] ?? 0) }}</td></tr>
            <tr><td>Direct costs</td><td>{{ $money($incomeStatement['direct_costs'] ?? 0) }}</td></tr>
            <tr><td>Gross profit</td><td>{{ $money($incomeStatement['gross_profit'] ?? 0) }}</td></tr>
            <tr><td>General expenses</td><td>{{ $money($incomeStatement['general_expenses'] ?? 0) }}</td></tr>
            <tr><td><strong>Net profit</strong></td><td><strong>{{ $money($incomeStatement['net_profit'] ?? 0) }}</strong></td></tr>
        </table>
    </div>

    <div class="section">
        <h2>Balance Sheet</h2>
        <table class="kv">
            <tr><td>Total assets</td><td>{{ $money($balanceSheet['total_assets'] ?? 0) }}</td></tr>
            <tr><td>Total liabilities</td><td>{{ $money($balanceSheet['total_liabilities'] ?? 0) }}</td></tr>
            <tr><td>Equity</td><td>{{ $money($balanceSheet['equity'] ?? 0) }}</td></tr>
            <tr><td>Equation gap (should be 0)</td><td>{{ $money($balanceSheet['equation_gap'] ?? 0) }}</td></tr>
        </table>
    </div>

    <div class="section">
        <h2>Trial Balance</h2>
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 16%;">Code</th>
                    <th>Account</th>
                    <th class="right" style="width: 20%;">Debit</th>
                    <th class="right" style="width: 20%;">Credit</th>
                </tr>
            </thead>
            <tbody>
                @foreach (($trialBalance['accounts'] ?? []) as $row)
                    <tr>
                        <td>{{ $row['code'] }}</td>
                        <td>{{ $row['name'] }}</td>
                        <td class="right">{{ $money($row['debit_total']) }}</td>
                        <td class="right">{{ $money($row['credit_total']) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="2"><strong>Totals</strong></td>
                    <td class="right"><strong>{{ $money($trialBalance['total_debit'] ?? 0) }}</strong></td>
                    <td class="right"><strong>{{ $money($trialBalance['total_credit'] ?? 0) }}</strong></td>
                </tr>
                <tr>
                    <td colspan="2">Difference (should be 0)</td>
                    <td colspan="2" class="right">{{ $money($trialBalance['difference'] ?? 0) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="watermark">(c)2026 Ori Studio Systems</div>
</body>
</html>
