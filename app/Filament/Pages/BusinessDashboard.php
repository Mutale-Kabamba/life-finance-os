<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Clusters\BusinessReports;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Invoice;
use App\Models\Supplier;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class BusinessDashboard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $cluster = BusinessReports::class;
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationGroup = 'Business Finance';
    protected static ?string $navigationLabel = 'Business Dashboard';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.business-dashboard';

    public ?array $data = [];

    /** @var array<string, mixed> */
    public array $stats = [];

    /** @var array<int, array<string, mixed>> */
    public array $recentInvoices = [];

    /** @var array<int, array<string, mixed>> */
    public array $lowStockItems = [];

    public function mount(): void
    {
        $this->form->fill([
            'business_id' => Business::query()->where('user_id', auth()->id())->value('id'),
            'start' => now()->startOfMonth()->toDateString(),
            'end' => now()->endOfMonth()->toDateString(),
        ]);

        $this->refreshStats();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('business_id')
                    ->label('Business')
                    ->options(fn () => Business::query()->where('user_id', auth()->id())->pluck('name', 'id'))
                    ->required(),
                DatePicker::make('start')
                    ->label('From')
                    ->required(),
                DatePicker::make('end')
                    ->label('To')
                    ->required(),
            ])
            ->columns(3)
            ->statePath('data');
    }

    public function refreshStats(): void
    {
        $state = $this->form->getState();

        $businessId = (int) ($state['business_id'] ?? 0);

        if (! $businessId) {
            return;
        }

        $start = (string) ($state['start'] ?? now()->startOfMonth()->toDateString());
        $end = (string) ($state['end'] ?? now()->endOfMonth()->toDateString());

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

        $this->stats = [
            'customers' => $customerCount,
            'suppliers' => $supplierCount,
            'products' => $productsCount,
            'services' => $servicesCount,
            'inventory_value' => $inventoryValue,
            'sales_in_period' => $salesInPeriod,
            'outstanding_receivables' => $outstandingReceivables,
            'low_stock' => $lowStockCount,
        ];

        $this->recentInvoices = Invoice::query()
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

        $this->lowStockItems = Inventory::query()
            ->where('business_id', $businessId)
            ->where('unit', '!=', 'service')
            ->where('is_active', true)
            ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
            ->orderBy('quantity_on_hand')
            ->limit(8)
            ->get()
            ->map(fn (Inventory $item): array => [
                'name' => $item->name,
                'sku' => $item->sku,
                'qty' => (int) $item->quantity_on_hand,
                'reorder' => (int) $item->reorder_level,
                'unit' => $item->unit,
            ])
            ->all();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->action('refreshStats'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) auth()->user()?->profile?->hasFeature('has_business');
    }
}
