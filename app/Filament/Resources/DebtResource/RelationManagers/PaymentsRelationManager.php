<?php

namespace App\Filament\Resources\DebtResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';
    protected static ?string $title = 'Payment history';
    protected static ?string $icon = 'heroicon-o-banknotes';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('amount')->numeric()->prefix('ZMW')->required()->minValue(0.01),
            Forms\Components\DatePicker::make('payment_date')->default(now())->required(),
            Forms\Components\TextInput::make('reference')->maxLength(255),
            Forms\Components\Toggle::make('is_late')->label('Paid late'),
            Forms\Components\Textarea::make('notes')->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reference')
            ->defaultSort('payment_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('payment_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('amount')->money('ZMW')->sortable(),
                Tables\Columns\TextColumn::make('reference')->searchable()->toggleable(),
                Tables\Columns\IconColumn::make('is_late')->boolean()->label('Late'),
                Tables\Columns\TextColumn::make('notes')->limit(40)->toggleable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Record payment')
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
