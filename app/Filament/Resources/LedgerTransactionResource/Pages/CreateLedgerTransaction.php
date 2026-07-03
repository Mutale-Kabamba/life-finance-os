<?php

namespace App\Filament\Resources\LedgerTransactionResource\Pages;

use App\Filament\Resources\LedgerTransactionResource;
use App\Models\LedgerTransaction;
use App\Filament\Resources\Pages\CreateRecord;

class CreateLedgerTransaction extends CreateRecord
{
    protected static string $resource = LedgerTransactionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $type = $data['transaction_type'] ?? null;
        unset($data['transaction_type']);

        $data['user_id'] = auth()->id();
        $data['metadata'] = ['transaction_type' => $type, 'source' => 'filament'];

        if ($type !== 'debts') {
            $data['payment_status'] = LedgerTransaction::PAYMENT_STATUS_PAID;
            $data['supplier_id'] = null;
        }

        return $data;
    }
}
