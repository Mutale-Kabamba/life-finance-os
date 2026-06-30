<?php

namespace App\Filament\Resources\IncomeReceiptResource\Pages;

use App\Filament\Resources\IncomeReceiptResource;
use Filament\Resources\Pages\CreateRecord;

class CreateIncomeReceipt extends CreateRecord
{
    protected static string $resource = IncomeReceiptResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }
}
