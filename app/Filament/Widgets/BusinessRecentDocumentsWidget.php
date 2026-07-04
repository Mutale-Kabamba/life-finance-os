<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Business;
use App\Models\Invoice;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class BusinessRecentDocumentsWidget extends Widget
{
    protected static string $view = 'filament.widgets.business-recent-documents';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected $listeners = ['business-period-changed' => 'syncFilters'];

    public string $activePeriod = 'monthly';
    public ?string $rangeStart = null;
    public ?string $rangeEnd = null;
    public bool $useCustomRange = false;

    public function mount(): void
    {
        $this->activePeriod = (string) session('dashboard_filters.business.period', 'monthly');
        $this->rangeStart = (string) session('dashboard_filters.business.range_start', now()->startOfMonth()->toDateString());
        $this->rangeEnd = (string) session('dashboard_filters.business.range_end', now()->endOfMonth()->toDateString());
        $this->useCustomRange = (bool) session('dashboard_filters.business.use_custom_range', false);
    }

    public function syncFilters(string $period, bool $useCustomRange, ?string $rangeStart, ?string $rangeEnd): void
    {
        $this->activePeriod = $period;
        $this->useCustomRange = $useCustomRange;
        $this->rangeStart = $rangeStart;
        $this->rangeEnd = $rangeEnd;
    }

    public function getViewData(): array
    {
        $period = $this->activePeriod;
        $allowedPeriods = ['daily', 'weekly', 'monthly', 'yearly'];

        if (! in_array($period, $allowedPeriods, true)) {
            $period = 'monthly';
            $this->activePeriod = 'monthly';
        }

        [$start, $end] = match ($period) {
            'daily' => [now()->startOfDay(), now()->endOfDay()],
            'weekly' => [now()->startOfWeek(), now()->endOfWeek()],
            'yearly' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };

        if ($this->useCustomRange && $this->rangeStart && $this->rangeEnd) {
            try {
                $start = Carbon::parse($this->rangeStart)->startOfDay();
                $end = Carbon::parse($this->rangeEnd)->endOfDay();
            } catch (\Throwable) {
                $this->useCustomRange = false;
            }

            if ($start->gt($end)) {
                [$start, $end] = [$end, $start];
                $this->rangeStart = $start->toDateString();
                $this->rangeEnd = $end->toDateString();
            }
        }

        $description = $this->useCustomRange
            ? 'Showing transactions from ' . $start->format('M j, Y') . ' to ' . $end->format('M j, Y') . '.'
            : match ($period) {
                'daily' => 'Showing Today\'s Transactions | ' . now()->format('D, M j, Y'),
                'weekly' => 'Showing This Week\'s Transactions | Week ' . $this->getWeekOfMonth(now()) . ', ' . now()->format('M Y'),
                'yearly' => 'Showing This Year\'s Transactions | ' . now()->format('Y'),
                default => 'Showing This Month\'s Transactions | ' . now()->format('M, Y'),
            };

        $businessId = (int) Business::query()
            ->where('user_id', auth()->id())
            ->value('id');

        if (! $businessId) {
            return ['rows' => [], 'description' => $description];
        }

        $rows = Invoice::query()
            ->with('customer:id,name')
            ->where('business_id', $businessId)
            ->whereIn('type', ['invoice', 'receipt', 'quotation'])
            ->whereBetween('issue_date', [$start->toDateString(), $end->toDateString()])
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

        return ['rows' => $rows, 'description' => $description];
    }

    protected function getWeekOfMonth(Carbon $date): int
    {
        return (int) ceil(($date->day + $date->copy()->startOfMonth()->dayOfWeekIso - 1) / 7);
    }
}
