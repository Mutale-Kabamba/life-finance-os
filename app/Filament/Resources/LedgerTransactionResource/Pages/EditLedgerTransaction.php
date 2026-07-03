<?php

namespace App\Filament\Resources\LedgerTransactionResource\Pages;

use App\Filament\Resources\LedgerTransactionResource;
use App\Models\LedgerTransaction;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLedgerTransaction extends EditRecord
{
    protected static string $resource = LedgerTransactionResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['transaction_type'] = $data['metadata']['transaction_type'] ?? null;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $type = $data['transaction_type'] ?? ($data['metadata']['transaction_type'] ?? null);
        unset($data['transaction_type']);

        $data['metadata'] = array_merge($data['metadata'] ?? [], ['transaction_type' => $type]);

        if ($type !== 'debts') {
            $data['payment_status'] = LedgerTransaction::PAYMENT_STATUS_PAID;
            $data['supplier_id'] = null;
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
