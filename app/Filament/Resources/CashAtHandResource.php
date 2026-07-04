<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashAtHandResource\Pages;
use App\Models\Business;
use App\Models\CashAtHand;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CashAtHandResource extends Resource
{
    protected static ?string $model = CashAtHand::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Business Finance';
    protected static ?string $navigationParentItem = 'Business Operations';
    protected static ?string $navigationLabel = 'Cash at Hand';
    protected static ?string $slug = 'cash-at-hand';
    protected static ?int $navigationSort = 22;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Cash Movement')->schema([
                Forms\Components\Select::make('business_id')
                    ->label('Business')
                    ->options(fn () => Business::query()->where('user_id', auth()->id())->pluck('name', 'id'))
                    ->default(fn () => Business::query()->where('user_id', auth()->id())->value('id'))
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('type')
                    ->options([
                        'opening_balance' => 'Opening balance',
                        'deposit'         => 'Cash in (deposit)',
                        'withdrawal'      => 'Cash out (withdrawal)',
                    ])
                    ->default('deposit')
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->numeric()->prefix('ZMW')->required()->minValue(0.01),
                Forms\Components\DatePicker::make('date')->required()->default(now()),
                Forms\Components\Textarea::make('description')->columnSpanFull(),
                Forms\Components\Toggle::make('create_transaction')
                    ->label('Also post to the ledger')
                    ->helperText('Creates a linked accounting transaction (income for deposits, expense for withdrawals).')
                    ->default(false),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('date')->date()->sortable(),
                Tables\Columns\TextColumn::make('reference')->searchable(),
                Tables\Columns\TextColumn::make('business.name')->label('Business')->toggleable()->sortable(),
                Tables\Columns\TextColumn::make('type')->badge()->color(fn (string $state): string => match ($state) {
                    'deposit', 'opening_balance' => 'success',
                    'withdrawal', 'bank_deposit' => 'warning',
                    default                      => 'gray',
                }),
                Tables\Columns\TextColumn::make('amount')->money('ZMW')->sortable(),
                Tables\Columns\TextColumn::make('description')->limit(40)->toggleable(),
                Tables\Columns\IconColumn::make('is_reconciled')->boolean()->label('Reconciled'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('business_id')
                    ->label('Business')
                    ->options(fn () => Business::query()->where('user_id', auth()->id())->pluck('name', 'id')),
                Tables\Filters\SelectFilter::make('type')->options([
                    'opening_balance' => 'Opening balance', 'deposit' => 'Deposit',
                    'withdrawal' => 'Withdrawal', 'bank_deposit' => 'Bank deposit',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hidden(fn (CashAtHand $record): bool => $record->is_reconciled),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn (CashAtHand $record): bool => $record->is_reconciled),
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
            'index'  => Pages\ListCashAtHand::route('/'),
            'create' => Pages\CreateCashAtHand::route('/create'),
            'edit'   => Pages\EditCashAtHand::route('/{record}/edit'),
        ];
    }
}
