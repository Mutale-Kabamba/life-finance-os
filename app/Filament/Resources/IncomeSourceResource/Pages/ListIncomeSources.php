<?php

namespace App\Filament\Resources\IncomeSourceResource\Pages;

use App\Filament\Resources\IncomeSourceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIncomeSources extends ListRecords
{
    protected static string $resource = IncomeSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
