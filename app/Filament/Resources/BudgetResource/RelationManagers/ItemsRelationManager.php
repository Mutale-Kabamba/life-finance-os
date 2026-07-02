<?php

namespace App\Filament\Resources\BudgetResource\RelationManagers;

use App\Models\AccountTransaction;
use App\Models\BudgetItem;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'Budget items';
    protected static ?string $icon = 'heroicon-o-shopping-cart';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('expense_category_id')
                ->label('Category')
                ->relationship('category', 'name')
                ->searchable()->preload()->required(),
            Forms\Components\TextInput::make('name')
                ->label('What to buy')
                ->required()->maxLength(255),
            Forms\Components\TextInput::make('budgeted_amount')
                ->label('Budgeted amount')
                ->numeric()->prefix('ZMW')->required()->minValue(0),
            Forms\Components\Textarea::make('notes')->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->heading('Shopping list')
            ->description('Tick each item as you buy it and enter how much it actually cost. Purchases update your expenses and account balances automatically.')
            ->contentGrid(['md' => 2, 'xl' => 3])
            ->paginated([12, 24, 48, 'all'])
            ->recordClasses(fn (BudgetItem $record): string => match (true) {
                (float) $record->actual_amount > (float) $record->budgeted_amount => 'border-s-4 border-s-danger-500',
                $record->is_purchased => 'border-s-4 border-s-success-500',
                default => 'border-s-4 border-s-primary-400',
            })
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('name')
                        ->weight(FontWeight::Bold)
                        ->size(Tables\Columns\TextColumn\TextColumnSize::Large)
                        ->searchable(),

                    Tables\Columns\TextColumn::make('category.name')
                        ->badge()
                        ->color('info')
                        ->icon('heroicon-m-tag'),

                    Tables\Columns\TextColumn::make('is_purchased')
                        ->badge()
                        ->formatStateUsing(fn (bool $state): string => $state ? 'Bought' : 'Planned')
                        ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                        ->icon(fn (bool $state): string => $state ? 'heroicon-m-check-circle' : 'heroicon-m-clock'),

                    Tables\Columns\TextColumn::make('budgeted_amount')
                        ->label('Budgeted')
                        ->money('ZMW')
                        ->color('gray')
                        ->icon('heroicon-m-banknotes'),

                    Tables\Columns\TextColumn::make('actual_amount')
                        ->label('Spent')
                        ->money('ZMW')
                        ->weight(FontWeight::SemiBold)
                        ->color(fn (BudgetItem $record): string => (float) $record->actual_amount > (float) $record->budgeted_amount ? 'danger' : 'success')
                        ->icon('heroicon-m-shopping-bag'),

                    Tables\Columns\TextColumn::make('variance')
                        ->label('Remaining')
                        ->state(fn (BudgetItem $record): float => (float) $record->budgeted_amount - (float) $record->actual_amount)
                        ->badge()
                        ->color(fn ($state): string => $state < 0 ? 'danger' : ($state == 0 ? 'gray' : 'warning'))
                        ->formatStateUsing(fn ($state): string => ($state < 0 ? 'Over by ZMW ' : 'Left ZMW ') . number_format(abs((float) $state), 2)),
                ])->space(2),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add item')
                    ->icon('heroicon-m-plus'),
            ])
            ->actions([
                Tables\Actions\Action::make('markBought')
                    ->label('Tick as bought')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->button()
                    ->visible(fn (BudgetItem $record): bool => ! $record->is_purchased)
                    ->modalHeading('Mark item as bought')
                    ->modalSubmitActionLabel('Confirm purchase')
                    ->fillForm(fn (BudgetItem $record): array => [
                        'amount'       => (float) $record->budgeted_amount,
                        'purchased_at' => now(),
                    ])
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->label('Bought at (actual cost)')
                            ->numeric()->prefix('ZMW')->required()->minValue(0.01),
                        Forms\Components\DatePicker::make('purchased_at')
                            ->label('Purchase date')
                            ->required()->default(now()),
                        Forms\Components\Select::make('account_id')
                            ->label('Paid from account')
                            ->helperText('Optional — deducts the amount from this account balance.')
                            ->options(fn (): array => Auth::user()->accounts()
                                ->where('is_active', true)
                                ->pluck('name', 'id')->all())
                            ->searchable(),
                    ])
                    ->action(function (BudgetItem $record, array $data): void {
                        $this->recordPurchase($record, $data);

                        Notification::make()
                            ->title('Purchase recorded')
                            ->body($record->name . ' marked as bought for ZMW ' . number_format((float) $data['amount'], 2)
                                . '. Expense and money movements are in sync.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('undoPurchase')
                    ->label('Undo purchase')
                    ->icon('heroicon-m-arrow-uturn-left')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Undo this purchase?')
                    ->modalDescription('This removes the linked expense and reverses any account deduction.')
                    ->visible(fn (BudgetItem $record): bool => $record->is_purchased)
                    ->action(function (BudgetItem $record): void {
                        $this->reversePurchase($record);

                        Notification::make()
                            ->title('Purchase reversed')
                            ->body($record->name . ' is back on the shopping list. Expense and account balance restored.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(),
            ]);
    }

    /**
     * Record a purchase: create the expense, optionally move money out of an
     * account, and sync the budget item + budget totals.
     */
    protected function recordPurchase(BudgetItem $record, array $data): void
    {
        $user      = Auth::user();
        $amount    = (float) $data['amount'];
        $date      = $data['purchased_at'] ?? now();
        $budget    = $record->budget;
        $reference = 'Budget: ' . ($budget?->name ?? 'item');

        $expense = Expense::create([
            'user_id'             => $user->id,
            'expense_category_id' => $record->expense_category_id,
            'name'                => $record->name,
            'amount'              => $amount,
            'expense_date'        => $date,
            'frequency'           => 'one_time',
            'is_recurring'        => false,
            'reference'           => $reference,
            'notes'               => 'Auto-recorded from budget purchase.',
        ]);

        $accountTransaction = null;
        if (! empty($data['account_id'])) {
            $accountTransaction = AccountTransaction::create([
                'account_id'       => $data['account_id'],
                'user_id'          => $user->id,
                'type'             => 'debit',
                'amount'           => $amount,
                'transaction_date' => $date,
                'reference'        => $reference,
                'description'      => 'Purchase: ' . $record->name,
            ]);
        }

        $record->forceFill([
            'actual_amount'          => $amount,
            'is_purchased'           => true,
            'purchased_at'           => $date,
            'account_id'             => $data['account_id'] ?? null,
            'expense_id'             => $expense->id,
            'account_transaction_id' => $accountTransaction?->id,
        ])->save();
    }

    /**
     * Reverse a recorded purchase and restore linked money records.
     */
    protected function reversePurchase(BudgetItem $record): void
    {
        if ($record->account_transaction_id) {
            AccountTransaction::find($record->account_transaction_id)?->delete();
        }

        if ($record->expense_id) {
            Expense::find($record->expense_id)?->delete();
        }

        $record->forceFill([
            'actual_amount'          => 0,
            'is_purchased'           => false,
            'purchased_at'           => null,
            'account_id'             => null,
            'expense_id'             => null,
            'account_transaction_id' => null,
        ])->save();
    }
}
