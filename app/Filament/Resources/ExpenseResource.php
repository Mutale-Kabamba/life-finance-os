<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Support\CsvActions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-down';
    protected static ?string $navigationGroup = 'Personal Finance';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Expense Details')->schema([
                Forms\Components\TextInput::make('name')
                    ->required()->maxLength(255),
                Forms\Components\Select::make('expense_category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->required()->numeric()->prefix('ZMW'),
                Forms\Components\DatePicker::make('expense_date')
                    ->required()->default(now()),
                Forms\Components\Select::make('frequency')
                    ->options([
                        'one_time'  => 'One Time',
                        'daily'     => 'Daily',
                        'weekly'    => 'Weekly',
                        'bi_weekly' => 'Bi-Weekly',
                        'monthly'   => 'Monthly',
                        'quarterly' => 'Quarterly',
                        'annually'  => 'Annually',
                    ])->default('one_time'),
                Forms\Components\Toggle::make('is_recurring')->default(false),
                Forms\Components\TextInput::make('reference')->maxLength(100),
                Forms\Components\Textarea::make('notes')->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('category.name')->badge()->sortable(),
                Tables\Columns\TextColumn::make('amount')->money('ZMW')->sortable(),
                Tables\Columns\TextColumn::make('expense_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('frequency')->badge(),
                Tables\Columns\IconColumn::make('is_recurring')->boolean(),
            ])
            ->defaultSort('expense_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('expense_category_id')
                    ->label('Category')
                    ->relationship('category', 'name'),
                Tables\Filters\Filter::make('this_month')
                    ->query(fn (Builder $query) => $query->whereMonth('expense_date', now()->month))
                    ->label('This Month'),
            ])
            ->headerActions([
                CsvActions::export([
                    'expense_date'   => 'Date',
                    'name'           => 'Name',
                    'category.name'  => 'Category',
                    'amount'         => 'Amount',
                    'frequency'      => 'Frequency',
                    'reference'      => 'Reference',
                ], 'expenses'),
                CsvActions::import(
                    Expense::class,
                    [
                        'expense_date' => 'Date',
                        'name'         => 'Name',
                        'amount'       => 'Amount',
                        'frequency'    => 'Frequency',
                        'reference'    => 'Reference',
                        'notes'        => 'Notes',
                    ],
                    fn () => [
                        'user_id'             => auth()->id(),
                        'expense_category_id' => ExpenseCategory::query()->value('id'),
                    ],
                    ['amount'],
                ),
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
            'index'  => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit'   => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
