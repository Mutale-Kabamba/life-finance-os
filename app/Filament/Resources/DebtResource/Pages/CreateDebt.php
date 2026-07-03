<?php

namespace App\Filament\Resources\DebtResource\Pages;

use App\Filament\Resources\DebtResource;
use App\Filament\Resources\Pages\CreateRecord;

class CreateDebt extends CreateRecord
{
    protected static string $resource = DebtResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        $principal = (float) ($data['original_amount'] ?? 0);
        $data['outstanding_balance'] = max(0, $principal);

        return $data;
    }
}
