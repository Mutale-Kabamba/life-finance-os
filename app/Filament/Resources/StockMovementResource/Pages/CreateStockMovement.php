<?php

namespace App\Filament\Resources\StockMovementResource\Pages;

use App\Filament\Resources\StockMovementResource;
use App\Models\Inventory;
use App\Models\LedgerTransaction;
use App\Models\StockMovement;
use App\Services\Accounting\PostingRuleService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreateStockMovement extends CreateRecord
{
    protected static string $resource = StockMovementResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $data['user_id'] = auth()->id();
        $product = Inventory::findOrFail($data['inventory_id']);

        $outflow = (StockMovement::DIRECTIONS[$data['type']] ?? 1) < 0;
        if ($outflow && $product->quantity_on_hand < (int) $data['quantity']) {
            Notification::make()
                ->title("Insufficient stock for {$product->name} (have {$product->quantity_on_hand}).")
                ->danger()->send();

            throw ValidationException::withMessages([
                'data.quantity' => 'Not enough stock on hand for this movement.',
            ]);
        }

        $movement = StockMovement::create($data);

        if ($data['type'] === 'purchase' && ($this->data['post_to_accounts'] ?? false)) {
            $this->postPurchase($movement);
        }

        return $movement;
    }

    private function postPurchase(StockMovement $movement): void
    {
        $total = round((float) $movement->unit_cost * (int) $movement->quantity, 2);

        if ($total <= 0) {
            return;
        }

        $inventoryAccount = app(PostingRuleService::class)
            ->accountByCode((int) $movement->business_id, '1200');

        if (! $inventoryAccount) {
            return;
        }

        $transaction = LedgerTransaction::create([
            'business_id'    => $movement->business_id,
            'user_id'        => $movement->user_id,
            'account_id'     => $inventoryAccount->id,
            'amount'         => $total,
            'date'           => now()->toDateString(),
            'description'    => 'Stock purchase: ' . $movement->inventory->name,
            'payment_status' => 'paid',
            'metadata'       => [
                'transaction_type' => 'valuables',
                'source'           => 'stock_purchase',
            ],
        ]);

        $movement->update(['ledger_transaction_id' => $transaction->id]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
