<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvestmentResource\Pages;
use App\Models\Investment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvestmentResource extends Resource
{
    protected static ?string $model = Investment::class;
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Wealth Building';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Investment Details')->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\Select::make('type')
                    ->required()
                    ->options([
                        'stocks'        => 'Stocks / Shares',
                        'bonds'         => 'Bonds',
                        'treasury_bills' => 'Treasury Bills',
                        'fixed_deposit' => 'Fixed Deposit',
                        'unit_trust'    => 'Unit Trust',
                        'mutual_fund'   => 'Mutual Fund',
                        'real_estate'   => 'Real Estate',
                        'cryptocurrency' => 'Cryptocurrency',
                        'business'      => 'Business Investment',
                        'farming'       => 'Farming',
                        'other'         => 'Other',
                    ]),
                Forms\Components\TextInput::make('institution')->maxLength(255),
                Forms\Components\TextInput::make('initial_amount')
                    ->required()->numeric()->prefix('ZMW'),
                Forms\Components\TextInput::make('current_value')
                    ->required()->numeric()->prefix('ZMW'),
                Forms\Components\TextInput::make('expected_return_rate')
                    ->numeric()->suffix('%')->default(0),
                Forms\Components\DatePicker::make('start_date')->required(),
                Forms\Components\DatePicker::make('maturity_date'),
                Forms\Components\Select::make('status')
                    ->options(['active' => 'Active', 'matured' => 'Matured', 'sold' => 'Sold', 'cancelled' => 'Cancelled'])
                    ->default('active'),
                Forms\Components\Textarea::make('notes')->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('institution'),
                Tables\Columns\TextColumn::make('initial_amount')->money('ZMW')->sortable(),
                Tables\Columns\TextColumn::make('current_value')->money('ZMW')->sortable(),
                Tables\Columns\TextColumn::make('return_percent')
                    ->label('Return %')
                    ->suffix('%')
                    ->color(fn ($record) => $record->return_amount >= 0 ? 'success' : 'danger'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['success' => 'active', 'info' => 'matured', 'gray' => fn ($s) => in_array($s, ['sold', 'cancelled'])]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type'),
                Tables\Filters\SelectFilter::make('status')
                    ->options(['active' => 'Active', 'matured' => 'Matured', 'sold' => 'Sold']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) auth()->user()?->profile?->hasFeature('has_investments');
    }


    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListInvestments::route('/'),
            'create' => Pages\CreateInvestment::route('/create'),
            'edit'   => Pages\EditInvestment::route('/{record}/edit'),
        ];
    }
}
