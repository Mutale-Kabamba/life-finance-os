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

class BusinessOverviewWidget extends Widget
{
    protected static string $view = 'filament.widgets.business-overview';
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    public function getViewData(): array
    {
        $businessId = (int) Business::query()
            ->where('user_id', auth()->id())
            ->value('id');

        if (! $businessId) {
            return [
                'hasBusiness' => false,
                'cards' => [],
            ];
        }

        $start = now()->startOfMonth()->toDateString();
        $end = now()->endOfMonth()->toDateString();

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
            ->whereBetween('issue_date', [$start, $end])
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
                    'title' => 'Sales (This Month)',
                    'value' => 'ZMW ' . number_format($salesInPeriod, 2),
                    'note' => 'Invoices and receipts in current month',
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
}
