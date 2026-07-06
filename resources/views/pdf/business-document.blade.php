<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $documentType }} {{ $invoice->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            font-size: 12px;
            margin: 0;
            padding: 32px 34px;
        }

        .doc-header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 26px;
        }
        .doc-header td { vertical-align: top; }

        .brand-name { font-size: 20px; font-weight: 700; color: #0f766e; margin: 0 0 4px; }
        .brand-meta { color: #6b7280; line-height: 1.5; }

        .doc-title { font-size: 30px; font-weight: 700; color: #111827; margin: 0; text-transform: uppercase; letter-spacing: 1px; }
        .doc-number { color: #6b7280; margin-top: 4px; }
        .doc-status {
            display: inline-block;
            margin-top: 8px;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: #ecfdf5;
            color: #047857;
        }

        .meta-grid { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .meta-grid td { vertical-align: top; width: 50%; padding-right: 12px; }
        .panel-label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #9ca3af; margin-bottom: 4px; font-weight: 700; }
        .panel-body { line-height: 1.55; }

        .dates { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .dates td { padding: 4px 0; color: #374151; }
        .dates .muted { color: #9ca3af; }

        table.items { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.items thead th {
            background: #0f766e;
            color: #ffffff;
            text-align: left;
            padding: 9px 10px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        table.items tbody td { padding: 9px 10px; border-bottom: 1px solid #e5e7eb; }
        table.items tbody tr:nth-child(even) td { background: #f9fafb; }
        .right { text-align: right; }
        .center { text-align: center; }

        .totals { width: 42%; margin-left: auto; margin-top: 16px; border-collapse: collapse; }
        .totals td { padding: 7px 10px; }
        .totals tr td:first-child { color: #6b7280; }
        .totals tr td:last-child { text-align: right; }
        .totals .grand td {
            border-top: 2px solid #0f766e;
            font-weight: 700;
            font-size: 14px;
            color: #0f766e;
        }

        .notes { margin-top: 22px; padding: 12px 14px; background: #f9fafb; border-left: 3px solid #0f766e; border-radius: 4px; }
        .notes-label { font-weight: 700; margin-bottom: 4px; }

        .footer { margin-top: 30px; padding-top: 12px; border-top: 1px solid #e5e7eb; font-size: 10px; color: #9ca3af; text-align: center; }
        .watermark {
            position: fixed;
            left: 10px;
            bottom: 6px;
            font-size: 10px;
            color: #9ca3af;
            text-align: left;
        }
    </style>
</head>
<body>
    @php
        $isQuotation = strtolower($documentType) === 'quotation';
        $isReceipt = strtolower($documentType) === 'receipt';
    @endphp

    <table class="doc-header">
        <tr>
            <td>
                <p class="brand-name">{{ $invoice->business?->name ?? 'Business' }}</p>
                <div class="brand-meta">
                    @if($invoice->business?->email){{ $invoice->business->email }}<br>@endif
                    @if($invoice->business?->phone){{ $invoice->business->phone }}<br>@endif
                    @if($invoice->business?->address){{ $invoice->business->address }}@endif
                </div>
            </td>
            <td class="right">
                <h1 class="doc-title">{{ $documentType }}</h1>
                <div class="doc-number">{{ $invoice->invoice_number }}</div>
                <span class="doc-status">{{ ucfirst((string) $invoice->status) }}</span>
            </td>
        </tr>
    </table>

    <table class="meta-grid">
        <tr>
            <td>
                <div class="panel-label">{{ $isQuotation ? 'Prepared For' : 'Bill To' }}</div>
                <div class="panel-body">
                    <strong>{{ $invoice->customer?->name ?? 'N/A' }}</strong><br>
                    @if($invoice->customer?->email){{ $invoice->customer->email }}<br>@endif
                    @if($invoice->customer?->phone){{ $invoice->customer->phone }}<br>@endif
                    @if($invoice->customer?->address){{ $invoice->customer->address }}@endif
                </div>
            </td>
            <td>
                <table class="dates">
                    <tr>
                        <td class="muted">{{ $isQuotation ? 'Quotation Date' : 'Issue Date' }}</td>
                        <td class="right">{{ optional($invoice->issue_date)->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td class="muted">{{ $isQuotation ? 'Valid Until' : 'Due Date' }}</td>
                        <td class="right">{{ optional($invoice->due_date)->format('d M Y') ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="muted">Currency</td>
                        <td class="right">ZMW</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th style="width: 46%;">Description</th>
                <th class="right" style="width: 14%;">Qty</th>
                <th class="right" style="width: 20%;">Unit Price</th>
                <th class="right" style="width: 20%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="right">{{ number_format((float) $item->quantity, 2) }}</td>
                    <td class="right">{{ number_format((float) $item->unit_price, 2) }}</td>
                    <td class="right">{{ number_format((float) $item->total_price, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="center" style="color:#9ca3af;">No items</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>Subtotal</td>
            <td>{{ number_format((float) $invoice->subtotal, 2) }}</td>
        </tr>
        @if((float) $invoice->tax_amount > 0)
        <tr>
            <td>Tax</td>
            <td>{{ number_format((float) $invoice->tax_amount, 2) }}</td>
        </tr>
        @endif
        @if((float) $invoice->discount_amount > 0)
        <tr>
            <td>Discount</td>
            <td>- {{ number_format((float) $invoice->discount_amount, 2) }}</td>
        </tr>
        @endif
        <tr class="grand">
            <td>Total (ZMW)</td>
            <td>{{ number_format((float) $invoice->total_amount, 2) }}</td>
        </tr>
        @unless($isQuotation)
        <tr>
            <td>Amount Paid</td>
            <td>{{ number_format((float) $invoice->amount_paid, 2) }}</td>
        </tr>
        <tr>
            <td>Balance Due</td>
            <td>{{ number_format((float) $invoice->balance_due, 2) }}</td>
        </tr>
        @endunless
    </table>

    @if(!empty($invoice->notes))
        <div class="notes">
            <div class="notes-label">Notes</div>
            {{ $invoice->notes }}
        </div>
    @endif

    <div class="footer">
        @if($isQuotation)
            This quotation is valid until {{ optional($invoice->due_date)->format('d M Y') ?: 'the date specified above' }}. ·
        @elseif($isReceipt)
            Thank you for your business. ·
        @endif
        Generated on {{ now()->format('d M Y H:i') }}
    </div>

    <div class="watermark">(c)2026 Ori Studio Systems</div>
</body>
</html>
