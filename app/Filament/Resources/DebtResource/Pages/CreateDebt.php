<?php

namespace App\Filament\Resources\DebtResource\Pages;

use App\Filament\Resources\DebtResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDebt extends CreateRecord
{
    protected static string $resource = DebtResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array { $data['user_id'] = auth()->id(); return $data; }
}
