<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReceivableResource\Pages;
use App\Models\Receivable;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReceivableResource extends Resource
{
    protected static ?string $model = Receivable::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-up';
    protected static ?string $navigationGroup = 'Personal Finance';
    protected static ?int $navigationSort = 6;
    protected static ?string $navigationLabel = 'Owed to Me';
    protected static ?string $modelLabel = 'Receivable';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Who owes you')->schema([
                Forms\Components\TextInput::make('debtor_name')
                    ->label('Person / entity')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label('Phone')
                    ->tel()
                    ->maxLength(20),
                Forms\Components\DatePicker::make('due_date')
                    ->label('Expected by'),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending'        => 'Pending',
                        'partially_paid' => 'Partially paid',
                        'paid'           => 'Paid',
                        'written_off'    => 'Written off',
                    ])
                    ->default('pending')
                    ->native(false)
                    ->required(),
            ])->columns(2),

            Forms\Components\Section::make('Amounts')->schema([
                Forms\Components\TextInput::make('amount')
                    ->label('Amount owed')
                    ->numeric()
                    ->prefix('ZMW')
                    ->required()
                    ->minValue(0),
                Forms\Components\TextInput::make('amount_paid')
                    ->label('Amount received')
                    ->numeric()
                    ->prefix('ZMW')
                    ->default(0)
                    ->minValue(0),
                Forms\Components\Textarea::make('notes')->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('debtor_name')->label('Debtor')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('amount')->money('ZMW')->sortable(),
                Tables\Columns\TextColumn::make('amount_paid')->label('Received')->money('ZMW'),
                Tables\Columns\TextColumn::make('outstanding')->label('Outstanding')->money('ZMW')
                    ->color(fn (Receivable $record): string => $record->outstanding > 0 ? 'warning' : 'success'),
                Tables\Columns\TextColumn::make('due_date')->date()->sortable()
                    ->color(fn (Receivable $record): ?string => $record->is_overdue ? 'danger' : null),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'paid'           => 'success',
                    'partially_paid' => 'info',
                    'written_off'    => 'gray',
                    default          => 'warning',
                }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'pending'        => 'Pending',
                    'partially_paid' => 'Partially paid',
                    'paid'           => 'Paid',
                    'written_off'    => 'Written off',
                ]),
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
            'index'  => Pages\ListReceivables::route('/'),
            'create' => Pages\CreateReceivable::route('/create'),
            'edit'   => Pages\EditReceivable::route('/{record}/edit'),
        ];
    }
}
