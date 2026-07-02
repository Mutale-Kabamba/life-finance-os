<?php

namespace App\Filament\Resources\AccountResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';
    protected static ?string $title = 'Transactions';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('type')
                ->options([
                    'credit' => 'Credit (+)',
                    'debit' => 'Debit (-)',
                    'transfer_in' => 'Transfer In (+)',
                    'transfer_out' => 'Transfer Out (-)',
                    'adjustment_in' => 'Adjustment In (+)',
                    'adjustment_out' => 'Adjustment Out (-)',
                ])
                ->default('credit')
                ->required(),
            Forms\Components\TextInput::make('amount')
                ->numeric()->prefix('ZMW')->required()->minValue(0.01),
            Forms\Components\DatePicker::make('transaction_date')
                ->default(now())
                ->required(),
            Forms\Components\TextInput::make('reference')->maxLength(255),
            Forms\Components\Textarea::make('description')->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('transaction_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('transaction_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => in_array($state, ['credit', 'transfer_in', 'adjustment_in'], true) ? 'success' : 'warning')
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state))),
                Tables\Columns\TextColumn::make('amount')->money('ZMW')->sortable(),
                Tables\Columns\TextColumn::make('reference')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('description')->limit(40)->toggleable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
