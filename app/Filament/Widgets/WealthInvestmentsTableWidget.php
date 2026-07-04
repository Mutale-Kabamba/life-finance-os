<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Resources\InvestmentResource;
use App\Models\Investment;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class WealthInvestmentsTableWidget extends TableWidget
{
    protected static ?int $sort = 3;

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
            Investment::query()->where('user_id', auth()->id()),
            $horizon,
        )->latest('start_date');

        return $table
            ->query($query)
            ->heading('Investments List - ' . $horizonLabel)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->weight('semibold'),
                Tables\Columns\TextColumn::make('type')
                    ->badge(),
                Tables\Columns\TextColumn::make('institution')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('initial_amount')
                    ->label('Initial')
                    ->money('ZMW')
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_value')
                    ->label('Current')
                    ->money('ZMW')
                    ->sortable(),
                Tables\Columns\TextColumn::make('return_percent')
                    ->label('Return %')
                    ->suffix('%')
                    ->color(fn (Investment $record): string => $record->return_amount >= 0 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
            ])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label('Open')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Investment $record): string => InvestmentResource::getUrl('edit', ['record' => $record])),
            ])
            ->paginated([5, 10, 25]);
    }

    protected function applyHorizonFilter(Builder $query, string $horizon): Builder
    {
        $termYearsSql = "TIMESTAMPDIFF(YEAR, start_date, COALESCE(maturity_date, CURDATE()))";

        return match ($horizon) {
            'all' => $query,
            'medium' => $query
                ->whereNotNull('start_date')
                ->whereRaw($termYearsSql . ' > 3')
                ->whereRaw($termYearsSql . ' <= 10'),
            'long' => $query
                ->whereNotNull('start_date')
                ->whereRaw($termYearsSql . ' > 10'),
            default => $query
                ->whereNotNull('start_date')
                ->whereRaw($termYearsSql . ' <= 3'),
        };
    }
}
