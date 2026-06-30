<?php

namespace App\Filament\Resources\BudgetResource\RelationManagers;

use App\Models\BudgetItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'Budget items';
    protected static ?string $icon = 'heroicon-o-list-bullet';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('expense_category_id')
                ->label('Category')
                ->relationship('category', 'name')
                ->searchable()->preload()->required(),
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\TextInput::make('budgeted_amount')
                ->numeric()->prefix('ZMW')->required()->minValue(0),
            Forms\Components\TextInput::make('actual_amount')
                ->numeric()->prefix('ZMW')->default(0),
            Forms\Components\Textarea::make('notes')->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('category.name')->label('Category')->toggleable(),
                Tables\Columns\TextColumn::make('budgeted_amount')->money('ZMW')->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('ZMW')),
                Tables\Columns\TextColumn::make('actual_amount')->money('ZMW')->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('ZMW')),
                Tables\Columns\TextColumn::make('variance')
                    ->label('Remaining')
                    ->state(fn (BudgetItem $record): float => (float) $record->budgeted_amount - (float) $record->actual_amount)
                    ->money('ZMW')
                    ->color(fn ($state) => $state < 0 ? 'danger' : 'success'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Add item'),
            ])
            ->actions([
                Tables\Actions\Action::make('recordSpending')
                    ->label('Record spending')
                    ->icon('heroicon-o-minus-circle')
                    ->color('warning')
                    ->modalHeading('Record spending against this item')
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->label('Amount spent')
                            ->numeric()->prefix('ZMW')->required()->minValue(0.01),
                        Forms\Components\Placeholder::make('current')
                            ->label('Current actual')
                            ->content(fn (BudgetItem $record): string => 'ZMW ' . number_format((float) $record->actual_amount, 2)),
                    ])
                    ->action(function (BudgetItem $record, array $data): void {
                        $record->actual_amount = (float) $record->actual_amount + (float) $data['amount'];
                        $record->save();
                        $record->refresh();

                        Notification::make()
                            ->title('Spending recorded')
                            ->body('Actual is now ZMW ' . number_format((float) $record->actual_amount, 2)
                                . ' of ZMW ' . number_format((float) $record->budgeted_amount, 2))
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
