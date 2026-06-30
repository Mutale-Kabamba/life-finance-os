<?php

namespace App\Filament\Resources\InventoryResource\Pages;

use App\Filament\Resources\InventoryResource;
use App\Models\StockMovement;
use Filament\Resources\Pages\CreateRecord;

class CreateInventory extends CreateRecord
{
    protected static string $resource = InventoryResource::class;

    protected function afterCreate(): void
    {
        $opening = (int) ($this->data['opening_quantity'] ?? 0);

        if ($opening > 0) {
            StockMovement::create([
                'business_id'  => $this->record->business_id,
                'inventory_id' => $this->record->id,
                'user_id'      => auth()->id(),
                'type'         => 'opening',
                'quantity'     => $opening,
                'unit_cost'    => $this->record->cost_price,
                'notes'        => 'Opening stock',
            ]);
        }
    }
}
