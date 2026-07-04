<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Invoice;
use App\Models\Supplier;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class BusinessOverviewWidget extends Widget
{
    protected static string $view = 'filament.widgets.business-overview';
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

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

    public function setPeriod(string $period): void
    {
        $allowedPeriods = ['daily', 'weekly', 'monthly', 'yearly'];

        if (! in_array($period, $allowedPeriods, true)) {
            return;
        }

        $this->activePeriod = $period;
        $this->useCustomRange = false;

        session([
            'dashboard_filters.business.period' => $this->activePeriod,
            'dashboard_filters.business.range_start' => $this->rangeStart,
            'dashboard_filters.business.range_end' => $this->rangeEnd,
            'dashboard_filters.business.use_custom_range' => $this->useCustomRange,
        ]);

        $this->dispatch('business-period-changed',
            period: $this->activePeriod,
            useCustomRange: $this->useCustomRange,
            rangeStart: $this->rangeStart,
            rangeEnd: $this->rangeEnd,
        );
    }

    public function applyDateRange(): void
    {
        if (! $this->rangeStart || ! $this->rangeEnd) {
            return;
        }

        try {
            $start = Carbon::parse($this->rangeStart)->startOfDay();
            $end = Carbon::parse($this->rangeEnd)->endOfDay();
        } catch (\Throwable) {
            return;
        }

        if ($start->gt($end)) {
            [$this->rangeStart, $this->rangeEnd] = [$end->toDateString(), $start->toDateString()];
        }

        $this->useCustomRange = true;

        session([
            'dashboard_filters.business.period' => $this->activePeriod,
            'dashboard_filters.business.range_start' => $this->rangeStart,
            'dashboard_filters.business.range_end' => $this->rangeEnd,
            'dashboard_filters.business.use_custom_range' => $this->useCustomRange,
        ]);

        $this->dispatch('business-period-changed',
            period: $this->activePeriod,
            useCustomRange: $this->useCustomRange,
            rangeStart: $this->rangeStart,
            rangeEnd: $this->rangeEnd,
        );
    }

    public function clearDateRange(): void
    {
        $this->useCustomRange = false;

        session([
            'dashboard_filters.business.period' => $this->activePeriod,
            'dashboard_filters.business.range_start' => $this->rangeStart,
            'dashboard_filters.business.range_end' => $this->rangeEnd,
            'dashboard_filters.business.use_custom_range' => $this->useCustomRange,
        ]);

        $this->dispatch('business-period-changed',
            period: $this->activePeriod,
            useCustomRange: $this->useCustomRange,
            rangeStart: $this->rangeStart,
            rangeEnd: $this->rangeEnd,
        );
    }

    public function getViewData(): array
    {
        $period = $this->activePeriod;
        $allowedPeriods = ['daily', 'weekly', 'monthly', 'yearly'];

        if (! in_array($period, $allowedPeriods, true)) {
            $period = 'monthly';
            $this->activePeriod = 'monthly';
        }

        [$periodLabel, $periodStart, $periodEnd] = match ($period) {
            'daily' => ['Daily', now()->startOfDay(), now()->endOfDay()],
            'weekly' => ['Weekly', now()->startOfWeek(), now()->endOfWeek()],
            'yearly' => ['Yearly', now()->startOfYear(), now()->endOfYear()],
            default => ['Monthly', now()->startOfMonth(), now()->endOfMonth()],
        };

        $start = $periodStart;
        $end = $periodEnd;

        if ($this->useCustomRange && $this->rangeStart && $this->rangeEnd) {
            try {
                $start = Carbon::parse($this->rangeStart)->startOfDay();
                $end = Carbon::parse($this->rangeEnd)->endOfDay();
            } catch (\Throwable) {
                $start = $periodStart;
                $end = $periodEnd;
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
            : $this->getPeriodDescription($period);

        $businessId = (int) Business::query()
            ->where('user_id', auth()->id())
            ->value('id');

        if (! $businessId) {
            return [
                'hasBusiness' => false,
                'description' => $description,
                'activePeriod' => $period,
                'periodLabel' => $periodLabel,
                'periodFilters' => [
                    ['value' => 'daily', 'label' => 'Daily'],
                    ['value' => 'weekly', 'label' => 'Weekly'],
                    ['value' => 'monthly', 'label' => 'Monthly'],
                    ['value' => 'yearly', 'label' => 'Yearly'],
                ],
                'rangeStart' => $this->rangeStart,
                'rangeEnd' => $this->rangeEnd,
                'useCustomRange' => $this->useCustomRange,
                'cards' => [],
            ];
        }

        $customerCount = Customer::query()->where('business_id', $businessId)->count();
        $supplierCount = Supplier::query()->where('business_id', $businessId)->count();

        $productsCount = Inventory::query()
            ->where('business_id', $businessId)
            ->where('unit', '!=', 'service')
            ->count();

        $servicesCount = Inventory::query()
            ->where('business_id', $businessId)
            ->where('unit', 'service')
            ->count();

        $inventoryValue = (float) Inventory::query()
            ->where('business_id', $businessId)
            ->where('unit', '!=', 'service')
            ->sum(DB::raw('quantity_on_hand * cost_price'));

        $salesInPeriod = (float) Invoice::query()
            ->where('business_id', $businessId)
            ->whereIn('type', ['invoice', 'receipt'])
            ->whereBetween('issue_date', [$start->toDateString(), $end->toDateString()])
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount');

        $outstandingReceivables = (float) Invoice::query()
            ->where('business_id', $businessId)
            ->whereIn('type', ['invoice', 'receipt'])
            ->whereIn('status', ['sent', 'partial', 'overdue'])
            ->sum(DB::raw('GREATEST(total_amount - amount_paid, 0)'));

        $lowStockCount = Inventory::query()
            ->where('business_id', $businessId)
            ->where('unit', '!=', 'service')
            ->where('is_active', true)
            ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
            ->count();

        return [
            'hasBusiness' => true,
            'description' => $description,
            'activePeriod' => $period,
            'periodLabel' => $periodLabel,
            'periodFilters' => [
                ['value' => 'daily', 'label' => 'Daily'],
                ['value' => 'weekly', 'label' => 'Weekly'],
                ['value' => 'monthly', 'label' => 'Monthly'],
                ['value' => 'yearly', 'label' => 'Yearly'],
            ],
            'rangeStart' => $this->rangeStart,
            'rangeEnd' => $this->rangeEnd,
            'useCustomRange' => $this->useCustomRange,
            'cards' => [
                [
                    'title' => 'Customers',
                    'value' => number_format($customerCount),
                    'note' => 'Active business customer records',
                    'icon' => 'heroicon-m-user-group',
                    'theme' => 'blue',
                ],
                [
                    'title' => 'Suppliers',
                    'value' => number_format($supplierCount),
                    'note' => 'Vendors and supplier partners',
                    'icon' => 'heroicon-m-truck',
                    'theme' => 'mint',
                ],
                [
                    'title' => 'Products',
                    'value' => number_format($productsCount),
                    'note' => 'Stock-tracked inventory items',
                    'icon' => 'heroicon-m-cube',
                    'theme' => 'indigo',
                ],
                [
                    'title' => 'Services',
                    'value' => number_format($servicesCount),
                    'note' => 'Non-stock service offerings',
                    'icon' => 'heroicon-m-briefcase',
                    'theme' => 'cyan',
                ],
                [
                    'title' => 'Inventory Value',
                    'value' => 'ZMW ' . number_format($inventoryValue, 2),
                    'note' => 'Estimated value at cost price',
                    'icon' => 'heroicon-m-archive-box',
                    'theme' => 'amber',
                ],
                [
                    'title' => 'Sales (' . ($this->useCustomRange ? 'Selected Range' : $periodLabel) . ')',
                    'value' => 'ZMW ' . number_format($salesInPeriod, 2),
                    'note' => 'Invoices and receipts for selected period',
                    'icon' => 'heroicon-m-arrow-trending-up',
                    'theme' => 'mint',
                ],
                [
                    'title' => 'Outstanding Receivables',
                    'value' => 'ZMW ' . number_format($outstandingReceivables, 2),
                    'note' => 'Open balances to collect',
                    'icon' => 'heroicon-m-banknotes',
                    'theme' => 'rose',
                ],
                [
                    'title' => 'Low Stock Items',
                    'value' => number_format($lowStockCount),
                    'note' => 'Items at or below reorder level',
                    'icon' => 'heroicon-m-exclamation-triangle',
                    'theme' => $lowStockCount > 0 ? 'rose' : 'cyan',
                ],
            ],
        ];
    }

    protected function getPeriodDescription(string $period): string
    {
        $today = now();

        return match ($period) {
            'daily' => 'Showing Today\'s Transactions | ' . $today->format('D, M j, Y'),
            'weekly' => 'Showing This Week\'s Transactions | Week ' . $this->getWeekOfMonth($today) . ', ' . $today->format('M Y'),
            'yearly' => 'Showing This Year\'s Transactions | ' . $today->format('Y'),
            default => 'Showing This Month\'s Transactions | ' . $today->format('M, Y'),
        };
    }

    protected function getWeekOfMonth(Carbon $date): int
    {
        return (int) ceil(($date->day + $date->copy()->startOfMonth()->dayOfWeekIso - 1) / 7);
    }
}
