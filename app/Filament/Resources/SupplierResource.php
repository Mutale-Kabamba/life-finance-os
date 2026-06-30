<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Models\Business;
use App\Models\Supplier;
use App\Support\CsvActions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Business Finance';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Supplier Details')->schema([
                Forms\Components\Select::make('business_id')
                    ->label('Business')
                    ->options(fn () => Business::query()->where('user_id', auth()->id())->pluck('name', 'id'))
                    ->default(fn () => Business::query()->where('user_id', auth()->id())->value('id'))
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\TextInput::make('email')->email()->maxLength(255),
                Forms\Components\TextInput::make('phone')->tel()->maxLength(20),
                Forms\Components\TextInput::make('tax_number')->maxLength(50),
                Forms\Components\Textarea::make('address'),
                Forms\Components\Textarea::make('notes'),
                Forms\Components\Toggle::make('is_active')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('business.name')->label('Business')->toggleable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('phone'),
                Tables\Columns\TextColumn::make('outstanding_balance')->money('ZMW')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('business_id')
                    ->label('Business')
                    ->options(fn () => Business::query()->where('user_id', auth()->id())->pluck('name', 'id')),
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->headerActions([
                CsvActions::export([
                    'name'                => 'Name',
                    'business.name'       => 'Business',
                    'email'               => 'Email',
                    'phone'               => 'Phone',
                    'outstanding_balance' => 'Outstanding',
                ], 'suppliers'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Supplier $record, Tables\Actions\DeleteAction $action): void {
                        if ($record->ledgerTransactions()->exists()) {
                            Notification::make()
                                ->danger()
                                ->title('Cannot delete supplier')
                                ->body('This supplier has transactions. Deactivate it instead.')
                                ->send();
                            $action->cancel();
                        }
                    }),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('business', fn (Builder $q) => $q->where('user_id', auth()->id()));
    }

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) auth()->user()?->profile?->hasFeature('has_business');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit'   => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
