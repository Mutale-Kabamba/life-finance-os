<?php

namespace App\Filament\Resources\InvestmentResource\Pages;

use App\Filament\Resources\InvestmentResource;
use App\Filament\Resources\Pages\CreateRecord;

class CreateInvestment extends CreateRecord
{
    protected static string $resource = InvestmentResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array { $data['user_id'] = auth()->id(); return $data; }
}
