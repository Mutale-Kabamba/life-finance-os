<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\BusinessOperations;
use App\Filament\Resources\LedgerTransactionResource\Pages;
use App\Models\Business;
use App\Models\LedgerAccount;
use App\Models\LedgerCategory;
use App\Models\LedgerTransaction;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LedgerTransactionResource extends Resource
{
    protected static ?string $model = LedgerTransaction::class;
    protected static ?string $cluster = BusinessOperations::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationGroup = 'Business Finance';
    protected static ?string $navigationLabel = 'Transactions';
    protected static ?int $navigationSort = 21;

    /**
     * @var array<string, string>
     */
    private const TYPE_LABELS = [
        'money_in'          => 'Money In (sale / income)',
        'money_out_direct'  => 'Money Out — Direct cost',
        'money_out_general' => 'Money Out — General expense',
        'valuables'         => 'Buy an asset',
        'debts'             => 'Record a debt / bill',
    ];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Transaction')->schema([
                Forms\Components\Select::make('business_id')
                    ->label('Business')
                    ->options(fn () => Business::query()->where('user_id', auth()->id())->pluck('name', 'id'))
                    ->default(fn () => Business::query()->where('user_id', auth()->id())->value('id'))
                    ->required()
                    ->live()
                    ->searchable(),

                Forms\Components\Select::make('transaction_type')
                    ->label('Type')
                    ->options(self::TYPE_LABELS)
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Forms\Set $set) => $set('account_id', null)),

                Forms\Components\Select::make('account_id')
                    ->label('Ledger account')
                    ->options(function (Forms\Get $get) {
                        $businessId = $get('business_id');
                        $type = LedgerTransaction::TYPE_MAP[$get('transaction_type')] ?? null;
                        if (! $businessId || ! $type) {
                            return [];
                        }

                        return LedgerAccount::query()
                            ->where('business_id', $businessId)
                            ->where('type', $type)
                            ->where('is_active', true)
                            ->orderBy('code')
                            ->get()
                            ->mapWithKeys(fn (LedgerAccount $a) => [$a->id => "{$a->code} — {$a->name}"]);
                    })
                    ->required()
                    ->searchable()
                    ->helperText('Choose a business and type first.'),

                Forms\Components\TextInput::make('amount')
                    ->numeric()->prefix('ZMW')->required()->minValue(0.01),

                Forms\Components\DatePicker::make('date')
                    ->required()->default(now()),

                Forms\Components\Select::make('supplier_id')
                    ->label('Supplier')
                    ->options(fn (Forms\Get $get) => $get('business_id')
                        ? Supplier::query()->where('business_id', $get('business_id'))->where('is_active', true)->pluck('name', 'id')
                        : [])
                    ->searchable()
                    ->visible(fn (Forms\Get $get): bool => $get('transaction_type') === 'debts')
                    ->required(fn (Forms\Get $get): bool => $get('transaction_type') === 'debts'),

                Forms\Components\Select::make('category_id')
                    ->label('Category')
                    ->options(fn (Forms\Get $get) => $get('business_id')
                        ? LedgerCategory::query()->where('business_id', $get('business_id'))->pluck('name', 'id')
                        : [])
                    ->searchable(),

                Forms\Components\Select::make('payment_status')
                    ->options([
                        'pending'        => 'Pending',
                        'partially_paid' => 'Partially paid',
                        'paid'           => 'Paid',
                    ])
                    ->default('paid')
                    ->visible(fn (Forms\Get $get): bool => $get('transaction_type') === 'debts')
                    ->required(fn (Forms\Get $get): bool => $get('transaction_type') === 'debts'),

                Forms\Components\Textarea::make('description')->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('date')->date()->sortable(),
                Tables\Columns\TextColumn::make('business.name')->label('Business')->toggleable()->sortable(),
                Tables\Columns\TextColumn::make('account')->label('Account')
                    ->getStateUsing(fn (LedgerTransaction $r) => $r->account ? "{$r->account->code} — {$r->account->name}" : '-')
                    ->searchable(false),
                Tables\Columns\TextColumn::make('type')->label('Type')
                    ->getStateUsing(fn (LedgerTransaction $r) => $r->metadata['transaction_type'] ?? '-')
                    ->badge(),
                Tables\Columns\TextColumn::make('amount')->money('ZMW')->sortable(),
                Tables\Columns\TextColumn::make('supplier.name')->label('Supplier')->toggleable(),
                Tables\Columns\TextColumn::make('payment_status')->badge()->color(fn (string $state): string => match ($state) {
                    'paid'           => 'success',
                    'partially_paid' => 'warning',
                    default          => 'gray',
                }),
                Tables\Columns\IconColumn::make('is_reconciled')->boolean()->label('Reconciled')->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('business_id')
                    ->label('Business')
                    ->options(fn () => Business::query()->where('user_id', auth()->id())->pluck('name', 'id')),
                Tables\Filters\SelectFilter::make('payment_status')->options([
                    'pending' => 'Pending', 'partially_paid' => 'Partially paid', 'paid' => 'Paid',
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('recordPayment')
                    ->label('Record payment')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (LedgerTransaction $r): bool => ($r->metadata['transaction_type'] ?? null) === 'debts'
                        && ! $r->parent_transaction_id
                        && $r->payment_status !== 'paid')
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->numeric()->prefix('ZMW')->required()->minValue(0.01)
                            ->helperText(fn (LedgerTransaction $r) => 'Remaining: ZMW ' . number_format($r->remainingAmount(), 2)),
                        Forms\Components\DatePicker::make('date')->required()->default(now()),
                    ])
                    ->action(function (array $data, LedgerTransaction $r): void {
                        $amount = min((float) $data['amount'], $r->remainingAmount());
                        if ($amount <= 0) {
                            return;
                        }

                        LedgerTransaction::create([
                            'business_id'           => $r->business_id,
                            'user_id'               => $r->user_id,
                            'amount'                => $amount,
                            'date'                  => $data['date'],
                            'account_id'            => $r->account_id,
                            'supplier_id'           => $r->supplier_id,
                            'category_id'           => $r->category_id,
                            'parent_transaction_id' => $r->id,
                            'payment_status'        => 'paid',
                            'description'           => 'Payment for: ' . ($r->description ?? "TXN-{$r->id}"),
                            'metadata'              => ['transaction_type' => 'debt_payment', 'source' => 'filament'],
                        ]);

                        $r->syncPaymentStatus();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereNull('parent_transaction_id')
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
            'index'  => Pages\ListLedgerTransactions::route('/'),
            'create' => Pages\CreateLedgerTransaction::route('/create'),
            'edit'   => Pages\EditLedgerTransaction::route('/{record}/edit'),
        ];
    }
}
