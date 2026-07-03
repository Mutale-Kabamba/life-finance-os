<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryResource\Pages;
use App\Models\Business;
use App\Models\Inventory;
use App\Models\InventoryCategory;
use App\Models\StockMovement;
use App\Support\CsvActions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Business Finance';
    protected static ?string $navigationLabel = 'Products';
    protected static ?string $modelLabel = 'product';
    protected static ?string $slug = 'products';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Product Details')->schema([
                Forms\Components\Select::make('business_id')
                    ->label('Business')
                    ->options(fn () => Business::query()->where('user_id', auth()->id())->pluck('name', 'id'))
                    ->required()
                    ->live()
                    ->searchable(),
                Forms\Components\Select::make('inventory_category_id')
                    ->label('Category')
                    ->options(fn (Get $get) => InventoryCategory::query()
                        ->where('business_id', $get('business_id'))
                        ->pluck('name', 'id'))
                    ->searchable()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')->required()->maxLength(255),
                        Forms\Components\Textarea::make('description'),
                    ])
                    ->createOptionUsing(function (array $data, Get $get): int {
                        return InventoryCategory::create([
                            'business_id' => $get('business_id'),
                            'name'        => $data['name'],
                            'description' => $data['description'] ?? null,
                        ])->id;
                    }),
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\TextInput::make('sku')->label('SKU')->maxLength(255),
                Forms\Components\TextInput::make('barcode')->maxLength(255),
                Forms\Components\TextInput::make('unit')->default('each')->maxLength(20),
            ])->columns(2),

            Forms\Components\Section::make('Pricing & Stock')->schema([
                Forms\Components\TextInput::make('cost_price')->numeric()->prefix('ZMW')->default(0)->required(),
                Forms\Components\TextInput::make('selling_price')->numeric()->prefix('ZMW')->default(0)->required(),
                Forms\Components\TextInput::make('reorder_level')->numeric()->integer()->default(0),
                Forms\Components\TextInput::make('opening_quantity')
                    ->label('Opening stock')
                    ->numeric()->integer()->default(0)
                    ->dehydrated(false)
                    ->visible(fn (string $operation) => $operation === 'create')
                    ->helperText('Recorded as an opening stock movement.'),
                Forms\Components\TextInput::make('quantity_on_hand')
                    ->label('Stock on hand')
                    ->disabled()
                    ->dehydrated(false)
                    ->visible(fn (string $operation) => $operation === 'edit')
                    ->helperText('Adjust via stock movements.'),
                Forms\Components\Toggle::make('is_active')->default(true),
            ])->columns(2),

            Forms\Components\Textarea::make('description')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('sku')->label('SKU')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('category.name')->label('Category')->toggleable(),
                Tables\Columns\TextColumn::make('business.name')->label('Business')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('cost_price')->money('ZMW')->toggleable(),
                Tables\Columns\TextColumn::make('selling_price')->money('ZMW'),
                Tables\Columns\TextColumn::make('quantity_on_hand')
                    ->label('In stock')
                    ->badge()
                    ->color(fn (Inventory $record): string => $record->isLowStock() ? 'danger' : 'success')
                    ->formatStateUsing(fn ($state, Inventory $record) => $state . ' ' . $record->unit),
                Tables\Columns\IconColumn::make('is_active')->boolean()->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('business_id')
                    ->label('Business')
                    ->options(fn () => Business::query()->where('user_id', auth()->id())->pluck('name', 'id')),
                Tables\Filters\TernaryFilter::make('is_active'),
                Tables\Filters\Filter::make('low_stock')
                    ->label('Low stock only')
                    ->query(fn (Builder $query) => $query->whereColumn('quantity_on_hand', '<=', 'reorder_level')),
            ])
            ->headerActions([
                CsvActions::export([
                    'name'             => 'Name',
                    'sku'              => 'SKU',
                    'category.name'    => 'Category',
                    'business.name'    => 'Business',
                    'cost_price'       => 'Cost Price',
                    'selling_price'    => 'Selling Price',
                    'quantity_on_hand' => 'In Stock',
                    'reorder_level'    => 'Reorder Level',
                ], 'products'),
            ])
            ->actions([
                Tables\Actions\Action::make('adjustStock')
                    ->label('Adjust stock')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('type')
                            ->label('Movement')
                            ->options([
                                'adjustment_in'  => 'Increase (+)',
                                'adjustment_out' => 'Decrease (-)',
                            ])
                            ->default('adjustment_in')
                            ->required(),
                        Forms\Components\TextInput::make('quantity')
                            ->numeric()->integer()->minValue(1)->required(),
                        Forms\Components\Textarea::make('notes'),
                    ])
                    ->action(function (Inventory $record, array $data): void {
                        if ($data['type'] === 'adjustment_out' && $record->quantity_on_hand < (int) $data['quantity']) {
                            Notification::make()
                                ->title('Not enough stock to remove that quantity.')
                                ->danger()->send();

                            return;
                        }

                        StockMovement::create([
                            'business_id'  => $record->business_id,
                            'inventory_id' => $record->id,
                            'user_id'      => auth()->id(),
                            'type'         => $data['type'],
                            'quantity'     => (int) $data['quantity'],
                            'unit_cost'    => $record->cost_price,
                            'notes'        => $data['notes'] ?? 'Manual adjustment',
                        ]);

                        Notification::make()->title('Stock adjusted.')->success()->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
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
            'index'  => Pages\ListInventory::route('/'),
            'create' => Pages\CreateInventory::route('/create'),
            'edit'   => Pages\EditInventory::route('/{record}/edit'),
        ];
    }
}
