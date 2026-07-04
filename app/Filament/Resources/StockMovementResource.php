<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockMovementResource\Pages;
use App\Models\Business;
use App\Models\Inventory;
use App\Models\StockMovement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrows-up-down';
    protected static ?string $navigationGroup = 'Business Finance';
    protected static ?string $navigationParentItem = 'Business Operations';
    protected static ?string $navigationLabel = 'Stock Movements';
    protected static ?int $navigationSort = 5;

    /** Types a user can record manually (sales come from the POS). */
    public const MANUAL_TYPES = [
        'purchase'        => 'Purchase (stock in)',
        'opening'         => 'Opening balance',
        'return_in'       => 'Customer return (in)',
        'return_out'      => 'Return to supplier (out)',
        'adjustment_in'   => 'Adjustment (increase)',
        'adjustment_out'  => 'Adjustment (decrease)',
    ];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Stock Movement')->schema([
                Forms\Components\Select::make('business_id')
                    ->label('Business')
                    ->options(fn () => Business::query()->where('user_id', auth()->id())->pluck('name', 'id'))
                    ->required()
                    ->live(),
                Forms\Components\Select::make('inventory_id')
                    ->label('Product')
                    ->options(fn (Get $get) => Inventory::query()
                        ->where('business_id', $get('business_id'))
                        ->orderBy('name')
                        ->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        $product = Inventory::find($state);
                        if ($product) {
                            $set('unit_cost', $product->cost_price);
                        }
                    }),
                Forms\Components\Select::make('type')
                    ->options(self::MANUAL_TYPES)
                    ->default('purchase')
                    ->required()
                    ->live(),
                Forms\Components\TextInput::make('quantity')
                    ->numeric()->integer()->minValue(1)->required(),
                Forms\Components\TextInput::make('unit_cost')
                    ->numeric()->prefix('ZMW')->default(0),
                Forms\Components\TextInput::make('reference')->maxLength(255),
                Forms\Components\Toggle::make('post_to_accounts')
                    ->label('Post purchase to accounts (Dr Inventory / Cr Cash)')
                    ->default(true)
                    ->dehydrated(false)
                    ->visible(fn (Get $get) => $get('type') === 'purchase'),
                Forms\Components\Textarea::make('notes')->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Date')->dateTime('M j, Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('inventory.name')->label('Product')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('business.name')->label('Business')->toggleable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => StockMovement::TYPE_LABELS[$state] ?? $state)
                    ->color(fn (StockMovement $record) => $record->signedQuantity() >= 0 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Qty')
                    ->formatStateUsing(fn ($state, StockMovement $record) => ($record->signedQuantity() >= 0 ? '+' : '-') . $record->quantity),
                Tables\Columns\TextColumn::make('unit_cost')->money('ZMW')->toggleable(),
                Tables\Columns\TextColumn::make('reference')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('user.name')->label('By')->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('business_id')
                    ->label('Business')
                    ->options(fn () => Business::query()->where('user_id', auth()->id())->pluck('name', 'id')),
                Tables\Filters\SelectFilter::make('type')->options(StockMovement::TYPE_LABELS),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->label('Reverse')
                    ->modalHeading('Reverse stock movement')
                    ->modalDescription('This will undo its effect on stock on hand.'),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('business', fn ($q) => $q->where('user_id', auth()->id()));
    }

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) auth()->user()?->profile?->hasFeature('has_business');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStockMovements::route('/'),
            'create' => Pages\CreateStockMovement::route('/create'),
        ];
    }
}
