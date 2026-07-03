<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $documentType }} {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 12px; }
        .header { margin-bottom: 18px; }
        .title { font-size: 24px; font-weight: 700; margin: 0; }
        .muted { color: #6b7280; }
        .grid { width: 100%; }
        .grid td { vertical-align: top; width: 50%; }
        .card { border: 1px solid #d1d5db; border-radius: 6px; padding: 10px; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 14px; }
        table.items th, table.items td { border: 1px solid #e5e7eb; padding: 8px; }
        table.items th { background: #f3f4f6; text-align: left; }
        .right { text-align: right; }
        .totals { width: 40%; margin-left: auto; margin-top: 14px; border-collapse: collapse; }
        .totals td { padding: 6px 8px; border: 1px solid #e5e7eb; }
        .totals .label { background: #f9fafb; width: 60%; }
        .totals .grand { font-weight: 700; }
        .footer { margin-top: 20px; font-size: 11px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">{{ strtoupper($documentType) }}</h1>
        <div class="muted">Number: {{ $invoice->invoice_number }}</div>
    </div>

    <table class="grid" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding-right: 8px;">
                <div class="card">
                    <strong>From</strong><br>
                    {{ $invoice->business?->name ?? 'N/A' }}<br>
                    {{ $invoice->business?->email ?? '' }}<br>
                    {{ $invoice->business?->phone ?? '' }}<br>
                    {{ $invoice->business?->address ?? '' }}
                </div>
            </td>
            <td style="padding-left: 8px;">
                <div class="card">
                    <strong>To</strong><br>
                    {{ $invoice->customer?->name ?? 'N/A' }}<br>
                    {{ $invoice->customer?->email ?? '' }}<br>
                    {{ $invoice->customer?->phone ?? '' }}<br>
                    {{ $invoice->customer?->address ?? '' }}
                </div>
            </td>
        </tr>
    </table>

    <table class="grid" style="margin-top: 12px;" cellpadding="0" cellspacing="0">
        <tr>
            <td><span class="muted">Issue Date:</span> {{ optional($invoice->issue_date)->format('d M Y') }}</td>
            <td class="right"><span class="muted">Due Date:</span> {{ optional($invoice->due_date)->format('d M Y') ?: '-' }}</td>
        </tr>
        <tr>
            <td><span class="muted">Status:</span> {{ ucfirst((string) $invoice->status) }}</td>
            <td class="right"><span class="muted">Currency:</span> ZMW</td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Description</th>
                <th class="right">Qty</th>
                <th class="right">Unit Price</th>
                <th class="right">Total</th>
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
                    <td colspan="4" class="right">No items</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="label">Subtotal</td>
            <td class="right">{{ number_format((float) $invoice->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td class="label">Tax</td>
            <td class="right">{{ number_format((float) $invoice->tax_amount, 2) }}</td>
        </tr>
        <tr>
            <td class="label">Discount</td>
            <td class="right">{{ number_format((float) $invoice->discount_amount, 2) }}</td>
        </tr>
        <tr>
            <td class="label grand">Total</td>
            <td class="right grand">{{ number_format((float) $invoice->total_amount, 2) }}</td>
        </tr>
        <tr>
            <td class="label">Amount Paid</td>
            <td class="right">{{ number_format((float) $invoice->amount_paid, 2) }}</td>
        </tr>
        <tr>
            <td class="label grand">Balance Due</td>
            <td class="right grand">{{ number_format((float) $invoice->balance_due, 2) }}</td>
        </tr>
    </table>

    @if(!empty($invoice->notes))
        <div style="margin-top: 16px;">
            <strong>Notes</strong><br>
            {{ $invoice->notes }}
        </div>
    @endif

    <div class="footer">
        Generated on {{ now()->format('d M Y H:i') }}
    </div>
</body>
</html>
