<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DebtResource\Pages;
use App\Models\Debt;
use App\Models\DebtPayment;
use App\Support\CsvActions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DebtResource extends Resource
{
    protected static ?string $model = Debt::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Personal Finance';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Debt Details')->schema([
                Forms\Components\TextInput::make('creditor_name')->required()->maxLength(255),
                Forms\Components\Select::make('type')
                    ->required()
                    ->options([
                        'bank_loan'     => 'Bank Loan',
                        'mobile_loan'   => 'Mobile Loan',
                        'mortgage'      => 'Mortgage',
                        'vehicle_loan'  => 'Vehicle Loan',
                        'personal_loan' => 'Personal Loan',
                        'hire_purchase' => 'Hire Purchase',
                        'credit_card'   => 'Credit Card',
                        'student_loan'  => 'Student Loan',
                        'other'         => 'Other',
                    ]),
                Forms\Components\TextInput::make('original_amount')
                    ->required()->numeric()->prefix('ZMW'),
                Forms\Components\TextInput::make('outstanding_balance')
                    ->required()->numeric()->prefix('ZMW'),
                Forms\Components\TextInput::make('monthly_installment')
                    ->required()->numeric()->prefix('ZMW'),
                Forms\Components\TextInput::make('interest_rate')
                    ->numeric()->suffix('%')->default(0),
                Forms\Components\DatePicker::make('start_date'),
                Forms\Components\DatePicker::make('due_date'),
                Forms\Components\TextInput::make('account_number')->maxLength(100),
                Forms\Components\Select::make('status')
                    ->options(['active' => 'Active', 'paid_off' => 'Paid Off', 'defaulted' => 'Defaulted', 'restructured' => 'Restructured'])
                    ->default('active'),
                Forms\Components\Textarea::make('notes')->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('creditor_name')->searchable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('outstanding_balance')->money('ZMW')->sortable(),
                Tables\Columns\TextColumn::make('monthly_installment')->money('ZMW'),
                Tables\Columns\TextColumn::make('interest_rate')->suffix('%'),
                Tables\Columns\TextColumn::make('due_date')->date()->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['danger' => 'active', 'success' => 'paid_off', 'warning' => 'restructured', 'gray' => 'defaulted']),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['active' => 'Active', 'paid_off' => 'Paid Off', 'defaulted' => 'Defaulted']),
                Tables\Filters\SelectFilter::make('type'),
            ])
            ->headerActions([
                CsvActions::export([
                    'creditor_name'       => 'Creditor',
                    'type'                => 'Type',
                    'original_amount'     => 'Original Amount',
                    'outstanding_balance' => 'Outstanding',
                    'monthly_installment' => 'Monthly Installment',
                    'interest_rate'       => 'Interest Rate',
                    'due_date'            => 'Due Date',
                    'status'              => 'Status',
                ], 'debts'),
                CsvActions::import(
                    Debt::class,
                    [
                        'creditor_name'       => 'Creditor',
                        'type'                => 'Type',
                        'original_amount'     => 'Original Amount',
                        'outstanding_balance' => 'Outstanding',
                        'monthly_installment' => 'Monthly Installment',
                        'interest_rate'       => 'Interest Rate',
                        'notes'               => 'Notes',
                    ],
                    fn () => ['user_id' => auth()->id(), 'status' => 'active'],
                    ['original_amount', 'outstanding_balance', 'monthly_installment', 'interest_rate'],
                ),
            ])
            ->actions([
                Tables\Actions\Action::make('recordPayment')
                    ->label('Record payment')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (Debt $record): bool => (float) $record->outstanding_balance > 0)
                    ->modalHeading('Record a debt payment')
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->numeric()->prefix('ZMW')->required()->minValue(0.01)
                            ->default(fn (Debt $record) => min((float) $record->monthly_installment, (float) $record->outstanding_balance))
                            ->helperText(fn (Debt $record) => 'Outstanding: ZMW ' . number_format((float) $record->outstanding_balance, 2)),
                        Forms\Components\DatePicker::make('payment_date')->default(now())->required(),
                        Forms\Components\TextInput::make('reference')->maxLength(255),
                        Forms\Components\Toggle::make('is_late')->label('Paid late'),
                        Forms\Components\Textarea::make('notes')->columnSpanFull(),
                    ])
                    ->action(function (Debt $record, array $data): void {
                        $amount = min((float) $data['amount'], (float) $record->outstanding_balance);

                        DebtPayment::create([
                            'debt_id'      => $record->id,
                            'user_id'      => auth()->id(),
                            'amount'       => $amount,
                            'payment_date' => $data['payment_date'],
                            'is_late'      => $data['is_late'] ?? false,
                            'reference'    => $data['reference'] ?? null,
                            'notes'        => $data['notes'] ?? null,
                        ]);

                        $record->refresh();

                        Notification::make()
                            ->title('Payment recorded')
                            ->body('Outstanding balance is now ZMW ' . number_format((float) $record->outstanding_balance, 2)
                                . ($record->status === 'paid_off' ? ' — debt cleared!' : ''))
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
            DebtResource\RelationManagers\PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDebts::route('/'),
            'create' => Pages\CreateDebt::route('/create'),
            'edit'   => Pages\EditDebt::route('/{record}/edit'),
        ];
    }
}
