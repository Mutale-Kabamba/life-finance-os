<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetResource\Pages;
use App\Models\Asset;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AssetResource extends Resource
{
    protected static ?string $model = Asset::class;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Wealth Building';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Asset Details')->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\Select::make('type')
                    ->required()
                    ->options([
                        'land'        => 'Land',
                        'building'    => 'Building / Property',
                        'vehicle'     => 'Vehicle',
                        'furniture'   => 'Furniture',
                        'machinery'   => 'Machinery',
                        'equipment'   => 'Equipment',
                        'electronics' => 'Electronics',
                        'livestock'   => 'Livestock',
                        'other'       => 'Other',
                    ]),
                Forms\Components\TextInput::make('purchase_price')
                    ->required()->numeric()->prefix('ZMW'),
                Forms\Components\DatePicker::make('purchase_date')->required(),
                Forms\Components\TextInput::make('current_value')
                    ->numeric()->prefix('ZMW'),
                Forms\Components\TextInput::make('depreciation_rate')
                    ->numeric()->suffix('%')->default(0),
                Forms\Components\TextInput::make('location')->maxLength(255),
                Forms\Components\TextInput::make('serial_number')->maxLength(100),
            ])->columns(2),

            Forms\Components\Section::make('Insurance')->schema([
                Forms\Components\Toggle::make('is_insured')->default(false),
                Forms\Components\TextInput::make('insurance_provider')->maxLength(255),
                Forms\Components\DatePicker::make('insurance_expiry'),
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
                Tables\Columns\TextColumn::make('purchase_price')->money('ZMW')->sortable(),
                Tables\Columns\TextColumn::make('current_value')->money('ZMW')->sortable(),
                Tables\Columns\TextColumn::make('purchase_date')->date()->sortable(),
                Tables\Columns\IconColumn::make('is_insured')->boolean()->label('Insured'),
                Tables\Columns\TextColumn::make('insurance_expiry')->date()->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type'),
                Tables\Filters\TernaryFilter::make('is_insured')->label('Insured'),
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAssets::route('/'),
            'create' => Pages\CreateAsset::route('/create'),
            'edit'   => Pages\EditAsset::route('/{record}/edit'),
        ];
    }
}
