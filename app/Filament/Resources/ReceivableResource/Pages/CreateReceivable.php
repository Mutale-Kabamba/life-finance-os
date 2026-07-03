<?php

namespace App\Filament\Resources\ReceivableResource\Pages;

use App\Filament\Resources\ReceivableResource;
use App\Filament\Resources\Pages\CreateRecord;

class CreateReceivable extends CreateRecord
{
    protected static string $resource = ReceivableResource::class;
    protected static bool $canCreateAnother = true;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
