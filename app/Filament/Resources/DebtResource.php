<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DebtResource\Pages;
use App\Models\Debt;
use App\Models\DebtPayment;
use App\Support\CsvActions;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
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
                    ])
                    ->live()
                    ->native(false),
                Forms\Components\TextInput::make('original_amount')
                    ->label(fn (Get $get): string => $get('type') === 'hire_purchase' ? 'Cash price (item value)' : 'Loan amount (principal)')
                    ->required()->numeric()->prefix('ZMW')->minValue(0)
                    ->helperText(fn (Get $get): string => $get('type') === 'hire_purchase'
                        ? 'For hire purchase, enter the item cash price. Deposit and term are captured below.'
                        : 'Principal borrowed amount.')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, Get $get): void {
                        if (self::floatValue($get('outstanding_balance')) <= 0) {
                            $set('outstanding_balance', $get('original_amount'));
                        }

                        self::syncLoanAmounts($set, $get, 'original_amount');
                    }),
                Forms\Components\TextInput::make('details.item_name')
                    ->label('Item being financed')
                    ->placeholder('e.g. Vehicle, fridge, solar kit')
                    ->visible(fn (Get $get): bool => $get('type') === 'hire_purchase')
                    ->required(fn (Get $get): bool => $get('type') === 'hire_purchase')
                    ->maxLength(255),
                Forms\Components\TextInput::make('details.deposit_amount')
                    ->label('Deposit paid')
                    ->numeric()->prefix('ZMW')->default(0)->minValue(0)
                    ->visible(fn (Get $get): bool => in_array($get('type'), ['hire_purchase', 'vehicle_loan', 'mortgage'], true))
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::syncLoanAmounts($set, $get, 'details.deposit_amount')),
                Forms\Components\TextInput::make('outstanding_balance')
                    ->label('Outstanding balance')
                    ->numeric()->prefix('ZMW')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Auto-managed by principal and recorded payments.'),
                Forms\Components\TextInput::make('monthly_installment')
                    ->label('Installment amount')
                    ->numeric()->prefix('ZMW')->default(0)->minValue(0)
                    ->readOnly(fn (Get $get): bool => $get('type') === 'hire_purchase')
                    ->helperText(function (Get $get): string {
                        if ($get('type') !== 'hire_purchase') {
                            return 'Amount paid per repayment cycle.';
                        }

                        $suggested = self::floatValue($get('details.suggested_installment'));
                        $remainingTerm = max((int) self::floatValue($get('details.remaining_term_months')), 0);
                        $financed = self::floatValue($get('details.financed_amount'));

                        if ($suggested > 0 && $remainingTerm > 0) {
                            return 'Suggested plan: ZMW ' . number_format($suggested, 2) . ' per month for about '
                                . $remainingTerm . ' month(s) on ZMW ' . number_format($financed, 2) . ' remaining financed amount.';
                        }

                        return 'Set term + total repayment to get an automatic monthly plan suggestion.';
                    })
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::syncLoanAmounts($set, $get, 'monthly_installment')),
                Forms\Components\TextInput::make('details.term_months')
                    ->label('Repayment term (months)')
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->visible(fn (Get $get): bool => in_array($get('type'), ['hire_purchase', 'vehicle_loan', 'mortgage', 'bank_loan', 'personal_loan', 'student_loan'], true))
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::syncLoanAmounts($set, $get, 'details.term_months')),
                Forms\Components\TextInput::make('interest_rate')
                    ->label('Interest rate')
                    ->numeric()->suffix('%')->default(0)->minValue(0)
                    ->visible(fn (Get $get): bool => $get('type') === 'personal_loan')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, Get $get): void {
                        self::syncLoanAmounts($set, $get, 'interest_rate');
                    }),
                Forms\Components\Select::make('repayment_frequency')
                    ->label('Repayment frequency')
                    ->options([
                        'daily' => 'Daily',
                        'weekly' => 'Weekly',
                        'bi_weekly' => 'Bi-weekly',
                        'monthly' => 'Monthly',
                    ])
                    ->placeholder('Flexible / not fixed')
                    ->native(false),
                Forms\Components\Select::make('details.mobile_provider')
                    ->label('Mobile lender')
                    ->options([
                        'airtel_money' => 'Airtel Money',
                        'mtn_momo' => 'MTN MoMo',
                        'zamtel_kwacha' => 'Zamtel Kwacha',
                        'other' => 'Other',
                    ])
                    ->visible(fn (Get $get): bool => $get('type') === 'mobile_loan')
                    ->native(false),
                Forms\Components\TextInput::make('total_repayment_amount')
                    ->label(fn (Get $get): string => self::isPurchaseType((string) $get('type')) ? 'Hire purchase price' : 'Total repayment amount')
                    ->numeric()->prefix('ZMW')->minValue(0)
                    ->required()
                    ->live(onBlur: true)
                    ->helperText('Required. Used to auto-derive effective interest and payment plan.')
                    ->afterStateUpdated(function (Set $set, Get $get): void {
                        self::syncLoanAmounts($set, $get, 'total_repayment_amount');
                    }),
                Forms\Components\Repeater::make('details.installments')
                    ->label('Installments')
                    ->visible(fn (Get $get): bool => $get('type') === 'personal_loan' && (int) self::floatValue($get('details.term_months')) > 0)
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false)
                    ->default([])
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get, ?array $state): void {
                        self::syncPersonalLoanInstallments($set, $get, $state ?? []);
                    })
                    ->helperText(fn (Get $get): string => self::personalInstallmentHelperText($get))
                    ->schema([
                        Forms\Components\TextInput::make('installment_no')
                            ->label('Installment #')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(true),
                        Forms\Components\TextInput::make('amount')
                            ->label('Amount')
                            ->numeric()
                            ->prefix('ZMW')
                            ->minValue(0)
                            ->required(),
                    ])
                    ->columns(2),
                Forms\Components\DatePicker::make('start_date')->label(fn (Get $get): string => self::isPurchaseType((string) $get('type')) ? 'Date purchased' : 'Date borrowed'),
                Forms\Components\DatePicker::make('due_date')->label('Expected repayment date'),
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
                Tables\Columns\TextColumn::make('repayment_frequency')->badge()->placeholder('Flexible'),
                Tables\Columns\TextColumn::make('total_repayment_amount')->money('ZMW')->label('Total repayment'),
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
                    'repayment_frequency' => 'Repayment Frequency',
                    'total_repayment_amount' => 'Total Repayment',
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
                        'repayment_frequency' => 'Repayment Frequency',
                        'interest_rate'       => 'Interest Rate',
                        'total_repayment_amount' => 'Total Repayment',
                        'notes'               => 'Notes',
                    ],
                    fn () => ['user_id' => auth()->id(), 'status' => 'active'],
                    ['original_amount', 'outstanding_balance', 'monthly_installment', 'total_repayment_amount'],
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

    private static function syncLoanAmounts(Set $set, Get $get, string $changedField): void
    {
        $type = (string) ($get('type') ?? 'personal_loan');
        $principal = self::floatValue($get('original_amount'));
        if ($principal <= 0) {
            return;
        }

        $deposit = self::floatValue($get('details.deposit_amount'));
        $termMonths = max((int) self::floatValue($get('details.term_months')), 0);
        $installment = self::floatValue($get('monthly_installment'));
        $interest = self::floatValue($get('interest_rate'));
        $total = self::floatValue($get('total_repayment_amount'));

        if ($type === 'hire_purchase') {
            if ($changedField !== 'total_repayment_amount' && $installment > 0 && $termMonths > 0) {
                $total = round(($installment * $termMonths) + $deposit, 2);
                $set('total_repayment_amount', $total);
            }

            if ($total <= 0) {
                $total = $principal;
            }

            $financedRepayment = max($total - $deposit, 0);
            $set('details.financed_amount', round($financedRepayment, 2));

            if ($termMonths > 0 && $financedRepayment > 0) {
                $suggestedInstallment = round($financedRepayment / $termMonths, 2);
                $set('details.suggested_installment', $suggestedInstallment);
                $set('details.remaining_term_months', $termMonths);
                $set('monthly_installment', $suggestedInstallment);
            }

            if ($total > 0) {
                $financedCashPrice = max($principal - $deposit, 0);
                if ($financedCashPrice > 0) {
                    $set('interest_rate', round(max((($financedRepayment - $financedCashPrice) / $financedCashPrice) * 100, 0), 2));
                }
            }

            return;
        }

        if ($type === 'personal_loan') {
            if ($changedField === 'interest_rate' && $interest >= 0) {
                $total = round($principal * (1 + ($interest / 100)), 2);
                $set('total_repayment_amount', $total);
            } elseif ($total > 0) {
                $derivedInterest = (($total - $principal) / $principal) * 100;
                $set('interest_rate', round(max(0, $derivedInterest), 2));
            }

            if ($termMonths > 0 && $total > 0) {
                $suggestedInstallment = round($total / $termMonths, 2);
                $set('details.suggested_installment', $suggestedInstallment);
                $set('details.remaining_term_months', $termMonths);

                if ($installment <= 0 || in_array($changedField, ['original_amount', 'interest_rate', 'total_repayment_amount', 'details.term_months'], true)) {
                    $set('monthly_installment', $suggestedInstallment);
                }

                self::syncPersonalLoanInstallments($set, $get);
            }

            return;
        }

        if ($total > 0) {
            $derivedInterest = (($total - $principal) / $principal) * 100;
            $set('interest_rate', round(max(0, $derivedInterest), 2));

            return;
        }

        if ($interest >= 0) {
            $derivedTotal = $principal * (1 + ($interest / 100));
            $set('total_repayment_amount', round($derivedTotal, 2));
        }
    }

    private static function floatValue(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        return (float) str_replace(',', '', (string) $value);
    }

    private static function isPurchaseType(string $type): bool
    {
        return in_array($type, ['hire_purchase', 'vehicle_loan', 'mortgage'], true);
    }

    private static function personalInstallmentHelperText(Get $get): string
    {
        $total = self::floatValue($get('total_repayment_amount'));
        $installments = (array) ($get('details.installments') ?? []);

        if ($total <= 0 || empty($installments)) {
            return 'Installments are auto-distributed by term. Enter earlier installments and the last one is auto-balanced.';
        }

        $sum = collect($installments)->sum(fn (array $row): float => self::floatValue($row['amount'] ?? 0));
        $remaining = max($total - $sum, 0);

        if ($remaining <= 0) {
            return 'Installments fully distributed. Total: ZMW ' . number_format($total, 2);
        }

        return 'Remaining to distribute: ZMW ' . number_format($remaining, 2);
    }

    /**
     * @param  array<int, array<string, mixed>>|null  $state
     */
    private static function syncPersonalLoanInstallments(Set $set, Get $get, ?array $state = null): void
    {
        $type = (string) ($get('type') ?? '');
        if ($type !== 'personal_loan') {
            return;
        }

        $termMonths = max((int) self::floatValue($get('details.term_months')), 0);
        $total = self::floatValue($get('total_repayment_amount'));

        if ($termMonths <= 0 || $total <= 0) {
            $set('details.installments', []);

            return;
        }

        $existing = $state ?? (array) ($get('details.installments') ?? []);
        $rows = [];

        for ($i = 1; $i <= $termMonths; $i++) {
            $rows[] = [
                'installment_no' => $i,
                'amount' => self::floatValue($existing[$i - 1]['amount'] ?? 0),
            ];
        }

        $allocated = 0.0;
        for ($i = 0; $i < $termMonths - 1; $i++) {
            $amount = max(0, (float) ($rows[$i]['amount'] ?? 0));
            $rows[$i]['amount'] = $amount;
            $allocated += $amount;
        }

        $rows[$termMonths - 1]['amount'] = round(max($total - $allocated, 0), 2);
        $set('details.installments', $rows);
    }
}
