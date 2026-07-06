<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncomeSourceResource\Pages;
use App\Models\Account;
use App\Models\IncomeReceipt;
use App\Models\IncomeSource;
use App\Support\CsvActions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class IncomeSourceResource extends Resource
{
    protected static ?string $model = IncomeSource::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-up';
    protected static ?string $navigationGroup = 'Personal Finance';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Income Details')->schema([
                Forms\Components\TextInput::make('name')
                    ->required()->maxLength(255),
                Forms\Components\Select::make('type')
                    ->required()
                    ->options([
                        'salary'      => 'Salary',
                        'business'    => 'Business Income',
                        'freelancing' => 'Freelancing',
                        'farming'     => 'Farming',
                        'rental'      => 'Rental Income',
                        'investment'  => 'Investment Returns',
                        'side_hustle' => 'Side Hustle',
                        'pension'     => 'Pension',
                        'other'       => 'Other',
                    ]),
                Forms\Components\TextInput::make('amount')
                    ->required()->numeric()->prefix('ZMW'),
                Forms\Components\Select::make('frequency')
                    ->required()
                    ->options([
                        'daily'     => 'Daily',
                        'weekly'    => 'Weekly',
                        'bi_weekly' => 'Bi-Weekly',
                        'monthly'   => 'Monthly',
                        'quarterly' => 'Quarterly',
                        'annually'  => 'Annually',
                    ]),
                Forms\Components\DatePicker::make('start_date'),
                Forms\Components\Toggle::make('is_active')->default(true),
                Forms\Components\Textarea::make('notes')->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'success' => 'salary',
                        'info'    => 'business',
                        'warning' => fn ($state) => in_array($state, ['freelancing', 'side_hustle']),
                    ]),
                Tables\Columns\TextColumn::make('amount')
                    ->money('ZMW')->sortable(),
                Tables\Columns\TextColumn::make('frequency')->badge(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'salary'      => 'Salary',
                        'business'    => 'Business Income',
                        'freelancing' => 'Freelancing',
                        'rental'      => 'Rental Income',
                        'investment'  => 'Investment Returns',
                        'other'       => 'Other',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
            ])
            ->headerActions([
                CsvActions::export([
                    'name'      => 'Name',
                    'type'      => 'Type',
                    'amount'    => 'Amount',
                    'frequency' => 'Frequency',
                    'is_active' => 'Active',
                ], 'income-sources'),
                CsvActions::import(
                    IncomeSource::class,
                    [
                        'name'      => 'Name',
                        'type'      => 'Type',
                        'amount'    => 'Amount',
                        'frequency' => 'Frequency',
                        'notes'     => 'Notes',
                    ],
                    fn () => ['user_id' => auth()->id(), 'is_active' => true],
                    ['amount'],
                ),
            ])
            ->actions([
                Tables\Actions\Action::make('recordIncome')
                    ->label('Record income received')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->modalHeading('Record income received')
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->numeric()->prefix('ZMW')->required()->minValue(0.01)
                            ->default(fn (IncomeSource $record) => (float) $record->amount),
                        Forms\Components\DatePicker::make('received_date')->default(now())->required(),
                        Forms\Components\Select::make('account_id')
                            ->label('Deposit to account')
                            ->options(fn (): array => Account::query()
                                ->where('user_id', auth()->id())
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()->preload()->nullable(),
                        Forms\Components\TextInput::make('method')->label('Method')
                            ->placeholder('Cash, bank, mobile money...')->maxLength(50),
                        Forms\Components\TextInput::make('reference')->maxLength(255),
                        Forms\Components\Textarea::make('notes')->columnSpanFull(),
                    ])
                    ->action(function (IncomeSource $record, array $data): void {
                        IncomeReceipt::create([
                            'user_id'          => auth()->id(),
                            'income_source_id' => $record->id,
                            'account_id'       => $data['account_id'] ?? null,
                            'name'             => $record->name,
                            'amount'           => $data['amount'],
                            'received_date'    => $data['received_date'],
                            'method'           => $data['method'] ?? null,
                            'reference'        => $data['reference'] ?? null,
                            'notes'            => $data['notes'] ?? null,
                        ]);

                        Notification::make()
                            ->title('Income recorded')
                            ->body('ZMW ' . number_format((float) $data['amount'], 2) . ' from ' . $record->name)
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListIncomeSources::route('/'),
            'create' => Pages\CreateIncomeSource::route('/create'),
            'edit'   => Pages\EditIncomeSource::route('/{record}/edit'),
        ];
    }
}
