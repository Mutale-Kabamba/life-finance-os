<?php

namespace App\Filament\Resources\BusinessResource\Pages;

use App\Filament\Resources\BusinessResource;
use App\Filament\Resources\Pages\CreateRecord;

class CreateBusiness extends CreateRecord
{
    protected static string $resource = BusinessResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array { $data['user_id'] = auth()->id(); return $data; }
}
