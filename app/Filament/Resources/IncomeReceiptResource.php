<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncomeReceiptResource\Pages;
use App\Models\IncomeReceipt;
use App\Support\CsvActions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class IncomeReceiptResource extends Resource
{
    protected static ?string $model = IncomeReceipt::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Personal Finance';
    protected static ?string $navigationLabel = 'Income Received';
    protected static ?string $modelLabel = 'income received';
    protected static ?string $slug = 'income-received';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Income Received')->schema([
                Forms\Components\Select::make('income_source_id')
                    ->label('Income source')
                    ->relationship('source', 'name', fn (Builder $query) => $query->where('user_id', auth()->id()))
                    ->searchable()->preload()->nullable()
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set): void {
                        if (! $state) {
                            return;
                        }
                        $source = \App\Models\IncomeSource::find($state);
                        if ($source) {
                            $set('name', $source->name);
                            $set('amount', (float) $source->amount);
                        }
                    })
                    ->helperText('Pick a listed source to auto-fill, or leave blank for one-off income.'),
                Forms\Components\TextInput::make('name')
                    ->label('Description')->required()->maxLength(255),
                Forms\Components\TextInput::make('amount')
                    ->required()->numeric()->prefix('ZMW')->minValue(0.01),
                Forms\Components\DatePicker::make('received_date')->default(now())->required(),
                Forms\Components\Select::make('account_id')
                    ->label('Deposit to account')
                    ->relationship('account', 'name', fn (Builder $query) => $query->where('user_id', auth()->id())->where('is_active', true))
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->helperText('Optional. If blank, the system credits your default cash account.'),
                Forms\Components\TextInput::make('method')->label('Method')
                    ->placeholder('Cash, bank, mobile money...')->maxLength(50),
                Forms\Components\TextInput::make('reference')->maxLength(255),
                Forms\Components\Textarea::make('notes')->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('received_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('received_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Description')->searchable(),
                Tables\Columns\TextColumn::make('source.name')->label('Source')->placeholder('One-off')->toggleable(),
                Tables\Columns\TextColumn::make('amount')->money('ZMW')->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('ZMW')),
                Tables\Columns\TextColumn::make('account.name')->label('Account')->placeholder('Auto')->toggleable(),
                Tables\Columns\TextColumn::make('method')->toggleable(),
                Tables\Columns\TextColumn::make('reference')->searchable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('income_source_id')
                    ->label('Source')
                    ->relationship('source', 'name', fn (Builder $query) => $query->where('user_id', auth()->id())),
                Tables\Filters\Filter::make('this_month')
                    ->label('This month')
                    ->query(fn (Builder $query) => $query->whereMonth('received_date', now()->month)->whereYear('received_date', now()->year)),
            ])
            ->headerActions([
                CsvActions::export([
                    'received_date' => 'Date',
                    'name'          => 'Description',
                    'amount'        => 'Amount',
                    'method'        => 'Method',
                    'reference'     => 'Reference',
                ], 'income-received'),
                CsvActions::import(
                    IncomeReceipt::class,
                    [
                        'received_date' => 'Date',
                        'name'          => 'Description',
                        'amount'        => 'Amount',
                        'method'        => 'Method',
                        'reference'     => 'Reference',
                        'notes'         => 'Notes',
                    ],
                    fn () => ['user_id' => auth()->id()],
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

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListIncomeReceipts::route('/'),
            'create' => Pages\CreateIncomeReceipt::route('/create'),
            'edit'   => Pages\EditIncomeReceipt::route('/{record}/edit'),
        ];
    }
}
