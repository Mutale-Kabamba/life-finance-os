<?php

namespace App\Filament\Resources\IncomeSourceResource\Pages;

use App\Filament\Resources\IncomeSourceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIncomeSource extends EditRecord
{
    protected static string $resource = IncomeSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
