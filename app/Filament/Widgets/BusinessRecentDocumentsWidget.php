<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Business;
use App\Models\Invoice;
use Filament\Widgets\Widget;

class BusinessRecentDocumentsWidget extends Widget
{
    protected static string $view = 'filament.widgets.business-recent-documents';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public function getViewData(): array
    {
        $businessId = (int) Business::query()
            ->where('user_id', auth()->id())
            ->value('id');

        if (! $businessId) {
            return ['rows' => []];
        }

        $rows = Invoice::query()
            ->with('customer:id,name')
            ->where('business_id', $businessId)
            ->whereIn('type', ['invoice', 'receipt', 'quotation'])
            ->latest('issue_date')
            ->latest('id')
            ->limit(8)
            ->get()
            ->map(fn (Invoice $invoice): array => [
                'number' => $invoice->invoice_number,
                'customer' => $invoice->customer?->name ?? '-',
                'type' => (string) $invoice->type,
                'status' => (string) $invoice->status,
                'issue_date' => $invoice->issue_date?->format('Y-m-d'),
                'total_amount' => (float) $invoice->total_amount,
                'balance_due' => (float) $invoice->balance_due,
            ])
            ->all();

        return ['rows' => $rows];
    }
}
