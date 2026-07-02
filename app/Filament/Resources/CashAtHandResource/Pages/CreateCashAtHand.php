<?php

namespace App\Filament\Resources\CashAtHandResource\Pages;

use App\Filament\Resources\CashAtHandResource;
use App\Services\Accounting\CashAtHandService;
use App\Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateCashAtHand extends CreateRecord
{
    protected static string $resource = CashAtHandResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CashAtHandService::class)->recordCashMovement([
            'business_id'        => (int) $data['business_id'],
            'type'               => $data['type'],
            'amount'             => $data['amount'],
            'date'               => $data['date'] ?? null,
            'description'        => $data['description'] ?? null,
            'create_transaction' => $data['create_transaction'] ?? false,
            'user_id'            => auth()->id(),
        ]);
    }
}
