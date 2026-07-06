<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BudgetResource\Pages;
use App\Models\AccountTransaction;
use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\Expense;
use App\Support\ExpenseCategoryDefaults;
use App\Support\CsvActions;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BudgetResource extends Resource
{
    protected static ?string $model = Budget::class;
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationGroup = 'Personal Finance';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Budget')->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\Select::make('period')
                    ->required()
                    ->options([
                        'weekly'    => 'Weekly',
                        'monthly'   => 'Monthly',
                        'quarterly' => 'Quarterly',
                        'annual'    => 'Annual',
                        'custom'    => 'Custom',
                    ])->default('monthly'),
                Forms\Components\DatePicker::make('start_date')->required(),
                Forms\Components\DatePicker::make('end_date')->required(),
                Forms\Components\Select::make('status')
                    ->options(['draft' => 'Draft', 'active' => 'Active', 'completed' => 'Completed'])
                    ->default('active'),
                Forms\Components\Textarea::make('notes')->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Budget Items')
                ->description('Add what you plan to buy and how much you budget for each. You tick items as bought from the budget page.')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship()
                        ->schema([
                            Forms\Components\Select::make('expense_category_id')
                                ->label('Category')
                                ->options(fn (): array => ExpenseCategoryDefaults::options())
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('name')
                                        ->label('New category name')
                                        ->required()
                                        ->maxLength(100),
                                ])
                                ->createOptionUsing(fn (array $data): int => ExpenseCategoryDefaults::createFromName((string) ($data['name'] ?? '')))
                                ->searchable()->preload()
                                ->required(),
                            Forms\Components\TextInput::make('name')
                                ->label('What to buy')->required(),
                            Forms\Components\TextInput::make('budgeted_amount')
                                ->numeric()->prefix('ZMW')->required(),
                        ])->columns(3)
                        ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                        ->addActionLabel('Add item')
                        ->collapsible(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->description('Each budget is a shopping plan. Open one to manage its items and tick what you buy.')
            ->contentGrid(['md' => 2, 'xl' => 3])
            ->recordAction('manageBudget')
            ->recordClasses(fn (Budget $record): string => match (true) {
                $record->utilization_percent > 100 => 'border-s-4 border-s-danger-500',
                $record->utilization_percent >= 80 => 'border-s-4 border-s-warning-500',
                default => 'border-s-4 border-s-primary-500',
            })
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('name')
                        ->weight(FontWeight::Bold)
                        ->size(Tables\Columns\TextColumn\TextColumnSize::Large)
                        ->searchable(),

                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\TextColumn::make('period')
                            ->badge()->color('info')->icon('heroicon-m-calendar-days'),
                        Tables\Columns\TextColumn::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'active' => 'success',
                                'draft' => 'warning',
                                default => 'gray',
                            }),
                    ])->grow(false),

                    Tables\Columns\TextColumn::make('date_range')
                        ->state(fn (Budget $record): string => $record->start_date?->format('d M Y') . ' – ' . $record->end_date?->format('d M Y'))
                        ->color('gray')->size(Tables\Columns\TextColumn\TextColumnSize::Small),

                    Tables\Columns\TextColumn::make('total_budgeted')
                        ->label('Budgeted')->money('ZMW')
                        ->color('gray')->icon('heroicon-m-banknotes'),

                    Tables\Columns\TextColumn::make('total_actual')
                        ->label('Spent')->money('ZMW')
                        ->weight(FontWeight::SemiBold)
                        ->color(fn (Budget $record): string => (float) $record->total_actual > (float) $record->total_budgeted ? 'danger' : 'success')
                        ->icon('heroicon-m-shopping-bag'),

                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\TextColumn::make('utilization_percent')
                            ->label('Used')->badge()
                            ->state(fn (Budget $record): string => number_format($record->utilization_percent, 0) . '% used')
                            ->color(fn (Budget $record): string => match (true) {
                                $record->utilization_percent > 100 => 'danger',
                                $record->utilization_percent >= 80 => 'warning',
                                default => 'success',
                            }),
                        Tables\Columns\TextColumn::make('variance')
                            ->label('Remaining')->badge()
                            ->state(fn (Budget $record): float => $record->variance)
                            ->color(fn ($state): string => $state < 0 ? 'danger' : 'gray')
                            ->formatStateUsing(fn ($state): string => ($state < 0 ? 'Over ZMW ' : 'Left ZMW ') . number_format(abs((float) $state), 2)),
                    ]),
                ])->space(2),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['draft' => 'Draft', 'active' => 'Active', 'completed' => 'Completed']),
                Tables\Filters\SelectFilter::make('period')
                    ->options(['weekly' => 'Weekly', 'monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'annual' => 'Annual']),
            ])
            ->headerActions([
                CsvActions::export([
                    'name'           => 'Name',
                    'period'         => 'Period',
                    'start_date'     => 'Start Date',
                    'end_date'       => 'End Date',
                    'total_budgeted' => 'Total Budgeted',
                    'total_actual'   => 'Total Actual',
                    'status'         => 'Status',
                ], 'budgets'),
            ])
            ->actions([
                Tables\Actions\Action::make('manageBudget')
                    ->label('Manage')
                    ->icon('heroicon-m-pencil-square')
                    ->color('primary')
                    ->modalHeading(fn (Budget $record): string => $record->name)
                    ->modalDescription('Manage budget details, shopping items, and bought records in one popup.')
                    ->modalSubmitActionLabel('Save budget')
                    ->fillForm(fn (Budget $record): array => [
                        'name'       => $record->name,
                        'period'     => $record->period,
                        'start_date' => optional($record->start_date)->format('Y-m-d'),
                        'end_date'   => optional($record->end_date)->format('Y-m-d'),
                        'status'     => $record->status,
                        'notes'      => $record->notes,
                        'items'      => $record->items()->orderBy('id')->get()->map(fn (BudgetItem $item): array => [
                            'id'                => $item->id,
                            'expense_category_id' => $item->expense_category_id,
                            'name'              => $item->name,
                            'budgeted_amount'   => (float) $item->budgeted_amount,
                            'is_purchased'      => (bool) $item->is_purchased,
                            'actual_amount'     => (float) $item->actual_amount,
                            'purchased_at'      => optional($item->purchased_at)->format('Y-m-d'),
                            'account_id'        => $item->account_id,
                            'notes'             => $item->notes,
                        ])->all(),
                    ])
                    ->form([
                        Forms\Components\Section::make('Budget details')
                            ->schema([
                                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                                Forms\Components\Select::make('period')
                                    ->required()
                                    ->options([
                                        'weekly'    => 'Weekly',
                                        'monthly'   => 'Monthly',
                                        'quarterly' => 'Quarterly',
                                        'annual'    => 'Annual',
                                        'custom'    => 'Custom',
                                    ]),
                                Forms\Components\DatePicker::make('start_date')->required(),
                                Forms\Components\DatePicker::make('end_date')->required(),
                                Forms\Components\Select::make('status')
                                    ->options(['draft' => 'Draft', 'active' => 'Active', 'completed' => 'Completed'])
                                    ->required(),
                                Forms\Components\Textarea::make('notes')->columnSpanFull(),
                            ])->columns(2),

                        Forms\Components\Section::make('Shopping items')
                            ->description('Add/remove items, tick bought, and capture actual spend from this popup.')
                            ->schema([
                                Forms\Components\Repeater::make('items')
                                    ->schema([
                                        Forms\Components\Hidden::make('id'),
                                        Forms\Components\Select::make('expense_category_id')
                                            ->label('Category')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->options(fn (): array => ExpenseCategoryDefaults::options())
                                            ->createOptionForm([
                                                Forms\Components\TextInput::make('name')
                                                    ->label('New category name')
                                                    ->required()
                                                    ->maxLength(100),
                                            ])
                                            ->createOptionUsing(fn (array $data): int => ExpenseCategoryDefaults::createFromName((string) ($data['name'] ?? ''))),
                                        Forms\Components\TextInput::make('name')
                                            ->label('What to buy')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('budgeted_amount')
                                            ->label('Budgeted')
                                            ->numeric()->prefix('ZMW')->required()->minValue(0),
                                        Forms\Components\Toggle::make('is_purchased')
                                            ->label('Bought?')
                                            ->inline(false)
                                            ->live(),
                                        Forms\Components\TextInput::make('actual_amount')
                                            ->label('Bought at')
                                            ->numeric()->prefix('ZMW')->minValue(0)
                                            ->visible(fn (Get $get): bool => (bool) $get('is_purchased'))
                                            ->required(fn (Get $get): bool => (bool) $get('is_purchased')),
                                        Forms\Components\DatePicker::make('purchased_at')
                                            ->label('Purchase date')
                                            ->visible(fn (Get $get): bool => (bool) $get('is_purchased')),
                                        Forms\Components\Select::make('account_id')
                                            ->label('Paid from account')
                                            ->helperText('Optional. If selected, an account debit transaction is synced.')
                                            ->visible(fn (Get $get): bool => (bool) $get('is_purchased'))
                                            ->searchable()
                                            ->options(fn (): array => auth()->user()->accounts()
                                                ->where('is_active', true)
                                                ->orderBy('name')
                                                ->pluck('name', 'id')
                                                ->all()),
                                        Forms\Components\Textarea::make('notes')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(4)
                                    ->defaultItems(0)
                                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                                    ->addActionLabel('Add item')
                                    ->collapsible()
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->action(function (Budget $record, array $data): void {
                        self::syncBudgetFromModal($record, $data);

                        Notification::make()
                            ->title('Budget updated')
                            ->body('Budget details and shopping items were saved and synced.')
                            ->success()
                            ->send();
                    })
                    ->modalWidth('7xl')
                    ->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function syncBudgetFromModal(Budget $budget, array $data): void
    {
        $budget->update([
            'name'       => $data['name'],
            'period'     => $data['period'],
            'start_date' => $data['start_date'],
            'end_date'   => $data['end_date'],
            'status'     => $data['status'],
            'notes'      => $data['notes'] ?? null,
        ]);

        $existingItems = $budget->items()->get()->keyBy('id');
        $incomingItems = collect($data['items'] ?? []);
        $keptIds = [];

        foreach ($incomingItems as $itemData) {
            $itemId = (int) ($itemData['id'] ?? 0);
            $item = $itemId > 0 ? $existingItems->get($itemId) : null;

            if (! $item) {
                $item = new BudgetItem();
                $item->budget()->associate($budget);
            }

            self::syncBudgetItemFromModal($budget, $item, $itemData);
            $keptIds[] = $item->id;
        }

        $itemsToDelete = $budget->items()
            ->when(count($keptIds) > 0, fn ($q) => $q->whereNotIn('id', $keptIds), fn ($q) => $q)
            ->get();

        foreach ($itemsToDelete as $itemToDelete) {
            self::deleteLinkedPurchaseRecords($itemToDelete);
            $itemToDelete->delete();
        }
    }

    protected static function syncBudgetItemFromModal(Budget $budget, BudgetItem $item, array $itemData): void
    {
        $isPurchased = (bool) ($itemData['is_purchased'] ?? false);
        $budgeted = (float) ($itemData['budgeted_amount'] ?? 0);
        $actual = $isPurchased
            ? (float) ($itemData['actual_amount'] ?? $budgeted)
            : 0.0;
        $purchaseDate = $isPurchased
            ? ($itemData['purchased_at'] ?? now()->format('Y-m-d'))
            : null;
        $accountId = $isPurchased ? ($itemData['account_id'] ?? null) : null;

        $item->fill([
            'expense_category_id' => $itemData['expense_category_id'],
            'name'                => $itemData['name'],
            'budgeted_amount'     => $budgeted,
            'actual_amount'       => $actual,
            'notes'               => $itemData['notes'] ?? null,
            'is_purchased'        => $isPurchased,
            'purchased_at'        => $purchaseDate,
            'account_id'          => $accountId,
        ]);

        $item->save();

        if (! $isPurchased) {
            self::deleteLinkedPurchaseRecords($item);
            $item->forceFill([
                'actual_amount'          => 0,
                'purchased_at'           => null,
                'account_id'             => null,
                'expense_id'             => null,
                'account_transaction_id' => null,
            ])->save();
            return;
        }

        $reference = 'Budget: ' . $budget->name;
        $expense = $item->expense_id ? Expense::find($item->expense_id) : null;

        if ($expense) {
            $expense->update([
                'expense_category_id' => $item->expense_category_id,
                'name'                => $item->name,
                'amount'              => $actual,
                'expense_date'        => $purchaseDate,
                'reference'           => $reference,
                'notes'               => 'Auto-synced from budget modal.',
            ]);
        } else {
            $expense = Expense::create([
                'user_id'             => $budget->user_id,
                'expense_category_id' => $item->expense_category_id,
                'name'                => $item->name,
                'amount'              => $actual,
                'expense_date'        => $purchaseDate,
                'frequency'           => 'one_time',
                'is_recurring'        => false,
                'reference'           => $reference,
                'notes'               => 'Auto-synced from budget modal.',
            ]);
        }

        $transaction = $item->account_transaction_id
            ? AccountTransaction::find($item->account_transaction_id)
            : null;

        if ($accountId) {
            if ($transaction) {
                $transaction->update([
                    'account_id'       => $accountId,
                    'amount'           => $actual,
                    'transaction_date' => $purchaseDate,
                    'reference'        => $reference,
                    'description'      => 'Purchase: ' . $item->name,
                ]);
            } else {
                $transaction = AccountTransaction::create([
                    'account_id'       => $accountId,
                    'user_id'          => $budget->user_id,
                    'type'             => 'debit',
                    'amount'           => $actual,
                    'transaction_date' => $purchaseDate,
                    'reference'        => $reference,
                    'description'      => 'Purchase: ' . $item->name,
                ]);
            }
        } else {
            if ($transaction) {
                $transaction->delete();
                $transaction = null;
            }
        }

        $item->forceFill([
            'expense_id'             => $expense->id,
            'account_transaction_id' => $transaction?->id,
        ])->save();
    }

    protected static function deleteLinkedPurchaseRecords(BudgetItem $item): void
    {
        if ($item->account_transaction_id) {
            AccountTransaction::find($item->account_transaction_id)?->delete();
        }

        if ($item->expense_id) {
            Expense::find($item->expense_id)?->delete();
        }
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Budget summary')
                ->icon('heroicon-o-shopping-cart')
                ->schema([
                    Infolists\Components\TextEntry::make('name')->weight(FontWeight::Bold),
                    Infolists\Components\TextEntry::make('period')->badge()->color('info'),
                    Infolists\Components\TextEntry::make('status')->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'active' => 'success', 'draft' => 'warning', default => 'gray',
                        }),
                    Infolists\Components\TextEntry::make('start_date')->date(),
                    Infolists\Components\TextEntry::make('end_date')->date(),
                    Infolists\Components\TextEntry::make('total_budgeted')->label('Budgeted')->money('ZMW'),
                    Infolists\Components\TextEntry::make('total_actual')->label('Spent')->money('ZMW')
                        ->color(fn (Budget $record): string => (float) $record->total_actual > (float) $record->total_budgeted ? 'danger' : 'success'),
                    Infolists\Components\TextEntry::make('variance')->label('Remaining')->money('ZMW')
                        ->state(fn (Budget $record): float => $record->variance)
                        ->color(fn ($state): string => $state < 0 ? 'danger' : 'gray'),
                    Infolists\Components\TextEntry::make('utilization_percent')->label('Used')->badge()
                        ->state(fn (Budget $record): string => number_format($record->utilization_percent, 0) . '%')
                        ->color(fn (Budget $record): string => match (true) {
                            $record->utilization_percent > 100 => 'danger',
                            $record->utilization_percent >= 80 => 'warning',
                            default => 'success',
                        }),
                ])->columns(3),
        ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    public static function getRelations(): array
    {
        return [
            BudgetResource\RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBudgets::route('/'),
            'create' => Pages\CreateBudget::route('/create'),
            'view'   => Pages\ViewBudget::route('/{record}'),
            'edit'   => Pages\EditBudget::route('/{record}/edit'),
        ];
    }
}
