<?php

namespace App\Filament\Resources\ChildResource\Pages;

use App\Filament\Resources\ChildResource;
use Filament\Resources\Pages\CreateRecord;

class CreateChild extends CreateRecord
{
    protected static string $resource = ChildResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array { $data['user_id'] = auth()->id(); return $data; }
}
