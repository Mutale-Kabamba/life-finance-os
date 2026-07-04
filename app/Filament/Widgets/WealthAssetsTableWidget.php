<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Resources\AssetResource;
use App\Models\Asset;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class WealthAssetsTableWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';
    protected $listeners = ['wealth-horizon-changed' => 'setHorizon'];

    public string $activeHorizon = 'all';

    public function mount(): void
    {
        $savedHorizon = (string) session('dashboard_filters.wealth_horizon', 'all');
        $allowedHorizons = ['all', 'short', 'medium', 'long'];

        $this->activeHorizon = in_array($savedHorizon, $allowedHorizons, true) ? $savedHorizon : 'all';
    }

    public function setHorizon(string $horizon): void
    {
        $allowedHorizons = ['all', 'short', 'medium', 'long'];

        if (! in_array($horizon, $allowedHorizons, true)) {
            return;
        }

        $this->activeHorizon = $horizon;
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        $horizon = $this->activeHorizon;
        $horizonLabel = match ($horizon) {
            'all' => 'All Horizons',
            'medium' => 'Medium-term',
            'long' => 'Long-term',
            default => 'Short-term',
        };

        $query = $this->applyHorizonFilter(
            Asset::query()->where('user_id', auth()->id()),
            $horizon,
        )->latest('purchase_date');

        return $table
            ->query($query)
            ->heading('Assets List - ' . $horizonLabel)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->weight('semibold'),
                Tables\Columns\TextColumn::make('type')
                    ->badge(),
                Tables\Columns\TextColumn::make('purchase_price')
                    ->label('Purchase Price')
                    ->money('ZMW')
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_value')
                    ->label('Current Value')
                    ->money('ZMW')
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchase_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_insured')
                    ->label('Insured')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label('Open')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Asset $record): string => AssetResource::getUrl('edit', ['record' => $record])),
            ])
            ->paginated([5, 10, 25]);
    }

    protected function applyHorizonFilter(Builder $query, string $horizon): Builder
    {
        return match ($horizon) {
            'all' => $query,
            'medium' => $query
                ->whereNotNull('purchase_date')
                ->whereRaw('TIMESTAMPDIFF(YEAR, purchase_date, CURDATE()) > 3')
                ->whereRaw('TIMESTAMPDIFF(YEAR, purchase_date, CURDATE()) <= 10'),
            'long' => $query
                ->whereNotNull('purchase_date')
                ->whereRaw('TIMESTAMPDIFF(YEAR, purchase_date, CURDATE()) > 10'),
            default => $query
                ->whereNotNull('purchase_date')
                ->whereRaw('TIMESTAMPDIFF(YEAR, purchase_date, CURDATE()) <= 3'),
        };
    }
}
