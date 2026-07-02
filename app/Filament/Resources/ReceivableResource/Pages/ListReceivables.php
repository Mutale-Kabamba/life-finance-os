<?php

namespace App\Filament\Resources\ReceivableResource\Pages;

use App\Filament\Resources\ReceivableResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListReceivables extends ListRecords
{
    protected static string $resource = ReceivableResource::class;

    public function getDefaultActiveTab(): string | int | null
    {
        return 'owing';
    }

    public function getTabs(): array
    {
        return [
            'owing' => Tab::make('Owed to me')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query
                    ->whereRaw('amount > amount_paid')
                    ->whereNot('status', 'written_off')),
            'all' => Tab::make('All receivables'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
