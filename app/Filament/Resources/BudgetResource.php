<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BudgetResource\Pages;
use App\Models\Budget;
use App\Support\CsvActions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
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
                Forms\Components\TextInput::make('total_income')
                    ->numeric()->prefix('ZMW')->default(0),
                Forms\Components\Select::make('status')
                    ->options(['draft' => 'Draft', 'active' => 'Active', 'completed' => 'Completed'])
                    ->default('active'),
                Forms\Components\Textarea::make('notes')->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Budget Items')->schema([
                Forms\Components\Repeater::make('items')
                    ->relationship()
                    ->schema([
                        Forms\Components\Select::make('expense_category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->required(),
                        Forms\Components\TextInput::make('name')->required(),
                        Forms\Components\TextInput::make('budgeted_amount')
                            ->numeric()->prefix('ZMW')->required(),
                        Forms\Components\TextInput::make('actual_amount')
                            ->numeric()->prefix('ZMW')->default(0),
                    ])->columns(4),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('period')->badge(),
                Tables\Columns\TextColumn::make('start_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('end_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('total_income')->money('ZMW'),
                Tables\Columns\TextColumn::make('total_budgeted')->money('ZMW'),
                Tables\Columns\TextColumn::make('total_actual')->money('ZMW'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['warning' => 'draft', 'success' => 'active', 'gray' => 'completed']),
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
                    'total_income'   => 'Total Income',
                    'total_budgeted' => 'Total Budgeted',
                    'total_actual'   => 'Total Actual',
                    'status'         => 'Status',
                ], 'budgets'),
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
        return [
            BudgetResource\RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBudgets::route('/'),
            'create' => Pages\CreateBudget::route('/create'),
            'edit'   => Pages\EditBudget::route('/{record}/edit'),
        ];
    }
}
