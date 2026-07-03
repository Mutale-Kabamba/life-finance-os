<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SavingsGoalResource\Pages;
use App\Models\SavingsGoal;
use App\Models\SavingsTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SavingsGoalResource extends Resource
{
    protected static ?string $model = SavingsGoal::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Personal Finance';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Savings Goal')->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\Select::make('category')
                    ->required()
                    ->options([
                        'emergency_fund'   => 'Emergency Fund',
                        'school_fees'      => 'School Fees',
                        'wedding'          => 'Wedding',
                        'vehicle'          => 'Vehicle',
                        'house'            => 'House',
                        'land'             => 'Land',
                        'business_capital' => 'Business Capital',
                        'holiday'          => 'Holiday',
                        'retirement'       => 'Retirement',
                        'other'            => 'Other',
                    ]),
                Forms\Components\TextInput::make('target_amount')
                    ->required()->numeric()->prefix('ZMW'),
                Forms\Components\TextInput::make('current_amount')
                    ->numeric()->prefix('ZMW')->default(0),
                Forms\Components\TextInput::make('monthly_contribution')
                    ->numeric()->prefix('ZMW')->default(0),
                Forms\Components\DatePicker::make('target_date'),
                Forms\Components\Select::make('status')
                    ->options(['active' => 'Active', 'paused' => 'Paused', 'completed' => 'Completed', 'cancelled' => 'Cancelled'])
                    ->default('active'),
                Forms\Components\Textarea::make('notes')->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('category')->badge(),
                Tables\Columns\TextColumn::make('target_amount')->money('ZMW')->sortable(),
                Tables\Columns\TextColumn::make('current_amount')->money('ZMW'),
                Tables\Columns\TextColumn::make('remaining_amount')->label('Remaining')->money('ZMW'),
                Tables\Columns\TextColumn::make('progress_percent')->label('Progress')->suffix('%')->sortable(),
                Tables\Columns\TextColumn::make('target_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'    => 'success',
                        'paused'    => 'warning',
                        default     => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['active' => 'Active', 'paused' => 'Paused', 'completed' => 'Completed']),
            ])
            ->actions([
                Tables\Actions\Action::make('recordSaving')
                    ->label('Record saving')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('type')
                            ->options([
                                'deposit' => 'Deposit',
                                'withdrawal' => 'Withdrawal',
                            ])
                            ->default('deposit')
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->numeric()->prefix('ZMW')->required()->minValue(0.01),
                        Forms\Components\DatePicker::make('transaction_date')
                            ->default(now())
                            ->required(),
                        Forms\Components\Textarea::make('notes')->columnSpanFull(),
                    ])
                    ->action(function (SavingsGoal $record, array $data): void {
                        SavingsTransaction::create([
                            'savings_goal_id'   => $record->id,
                            'user_id'           => auth()->id(),
                            'type'              => $data['type'],
                            'amount'            => $data['amount'],
                            'transaction_date'  => $data['transaction_date'],
                            'notes'             => $data['notes'] ?? null,
                        ]);

                        $record->refresh();

                        Notification::make()
                            ->title('Savings transaction recorded')
                            ->body('Current amount is now ZMW ' . number_format((float) $record->current_amount, 2))
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
            SavingsGoalResource\RelationManagers\TransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSavingsGoals::route('/'),
            'create' => Pages\CreateSavingsGoal::route('/create'),
            'edit'   => Pages\EditSavingsGoal::route('/{record}/edit'),
        ];
    }
}
