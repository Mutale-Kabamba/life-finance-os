<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Business;
use App\Models\Inventory;
use Filament\Widgets\Widget;

class BusinessLowStockWidget extends Widget
{
    protected static string $view = 'filament.widgets.business-low-stock';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function getViewData(): array
    {
        $businessId = (int) Business::query()
            ->where('user_id', auth()->id())
            ->value('id');

        if (! $businessId) {
            return ['items' => []];
        }

        $items = Inventory::query()
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

        return ['items' => $items];
    }
}
