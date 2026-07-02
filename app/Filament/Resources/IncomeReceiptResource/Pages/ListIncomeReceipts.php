<?php

namespace App\Filament\Resources\IncomeReceiptResource\Pages;

use App\Filament\Resources\IncomeReceiptResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListIncomeReceipts extends ListRecords
{
    protected static string $resource = IncomeReceiptResource::class;

    public function getDefaultActiveTab(): string | int | null
    {
        return 'all';
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All money in'),
            'income_sources' => Tab::make('From income sources')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereNotNull('income_source_id')),
            'receivables' => Tab::make('From receivables')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereNotNull('receivable_payment_id')),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
