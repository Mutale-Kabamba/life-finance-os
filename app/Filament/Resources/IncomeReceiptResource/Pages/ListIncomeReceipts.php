<?php

namespace App\Filament\Resources\IncomeReceiptResource\Pages;

use App\Filament\Resources\IncomeReceiptResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIncomeReceipts extends ListRecords
{
    protected static string $resource = IncomeReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
