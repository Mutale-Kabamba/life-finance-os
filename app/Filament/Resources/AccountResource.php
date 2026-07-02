<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountResource\Pages;
use App\Models\Account;
use App\Models\AccountTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;
    protected static ?string $navigationIcon = 'heroicon-o-wallet';
    protected static ?string $navigationGroup = 'Personal Finance';
    protected static ?string $navigationLabel = 'Accounts';
    protected static ?string $slug = 'accounts';
    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Account details')->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->required()
                    ->options([
                        'bank' => 'Bank',
                        'virtual_card' => 'Virtual Card',
                        'mobile_money' => 'Mobile Money',
                        'cash_wallet' => 'Cash Wallet',
                        'savings_wallet' => 'Savings Wallet',
                        'other' => 'Other',
                    ])
                    ->default('bank'),
                Forms\Components\TextInput::make('provider')
                    ->maxLength(255)
                    ->placeholder('Bank/Mobile network/provider'),
                Forms\Components\TextInput::make('account_number')
                    ->maxLength(100),
                Forms\Components\TextInput::make('currency')
                    ->default('ZMW')
                    ->maxLength(3)
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->default(true),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Balances')->schema([
                Forms\Components\TextInput::make('opening_balance')
                    ->label('Opening balance')
                    ->numeric()
                    ->prefix('ZMW')
                    ->default(0)
                    ->required(),
                Forms\Components\TextInput::make('current_balance')
                    ->label('Current balance')
                    ->prefix('ZMW')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 2))
                    ->helperText('Calculated from opening balance + credits - debits.'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')->badge()->formatStateUsing(fn (string $state): string => match ($state) {
                    'virtual_card' => 'Virtual Card',
                    'mobile_money' => 'Mobile Money',
                    'cash_wallet' => 'Cash Wallet',
                    'savings_wallet' => 'Savings Wallet',
                    default => ucfirst(str_replace('_', ' ', $state)),
                }),
                Tables\Columns\TextColumn::make('provider')->toggleable(),
                Tables\Columns\TextColumn::make('currency')->sortable(),
                Tables\Columns\TextColumn::make('current_balance')->money('ZMW')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->options([
                    'bank' => 'Bank',
                    'virtual_card' => 'Virtual Card',
                    'mobile_money' => 'Mobile Money',
                    'cash_wallet' => 'Cash Wallet',
                    'savings_wallet' => 'Savings Wallet',
                    'other' => 'Other',
                ]),
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
            ])
            ->actions([
                Tables\Actions\Action::make('transact')
                    ->label('Transact')
                    ->icon('heroicon-o-arrows-right-left')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('type')
                            ->options([
                                'credit' => 'Credit (+)',
                                'debit' => 'Debit (-)',
                                'transfer_in' => 'Transfer In (+)',
                                'transfer_out' => 'Transfer Out (-)',
                                'adjustment_in' => 'Adjustment In (+)',
                                'adjustment_out' => 'Adjustment Out (-)',
                            ])
                            ->default('credit')
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->numeric()->prefix('ZMW')->required()->minValue(0.01),
                        Forms\Components\DatePicker::make('transaction_date')
                            ->default(now())
                            ->required(),
                        Forms\Components\TextInput::make('reference')->maxLength(255),
                        Forms\Components\Textarea::make('description')->columnSpanFull(),
                    ])
                    ->action(function (Account $record, array $data): void {
                        AccountTransaction::create([
                            'account_id' => $record->id,
                            'user_id' => auth()->id(),
                            'type' => $data['type'],
                            'amount' => $data['amount'],
                            'transaction_date' => $data['transaction_date'],
                            'reference' => $data['reference'] ?? null,
                            'description' => $data['description'] ?? null,
                        ]);

                        $record->refresh();

                        Notification::make()
                            ->title('Transaction recorded')
                            ->body('Current balance: ZMW ' . number_format((float) $record->current_balance, 2))
                            ->success()
                            ->send();
                    }),
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
        return [
            AccountResource\RelationManagers\TransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccounts::route('/'),
            'create' => Pages\CreateAccount::route('/create'),
            'edit' => Pages\EditAccount::route('/{record}/edit'),
        ];
    }
}
