<?php

namespace App\Filament\Resources\CashAtHandResource\Pages;

use App\Filament\Resources\CashAtHandResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCashAtHand extends EditRecord
{
    protected static string $resource = CashAtHandResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
