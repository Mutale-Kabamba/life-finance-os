<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LedgerAccountResource\Pages;
use App\Models\Business;
use App\Models\LedgerAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LedgerAccountResource extends Resource
{
    protected static ?string $model = LedgerAccount::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Business Finance';
    protected static ?string $navigationLabel = 'Chart of Accounts';
    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Account')->schema([
                Forms\Components\Select::make('business_id')
                    ->label('Business')
                    ->options(fn () => Business::query()->where('user_id', auth()->id())->pluck('name', 'id'))
                    ->default(fn () => Business::query()->where('user_id', auth()->id())->value('id'))
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(20)
                    ->helperText('Codes 1100 (cash/settlement) and 1199 (contra) are reserved.'),
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\Select::make('group_name')
                    ->label('Group')
                    ->options([
                        'valuables'     => 'Valuables (Assets)',
                        'debts'         => 'Debts (Liabilities)',
                        'money_in'      => 'Money In (Income)',
                        'direct_costs'  => 'Direct Costs (COGS)',
                        'general_costs' => 'General Costs (Expenses)',
                    ])
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('type', LedgerAccount::GROUP_TYPE_MAP[$state] ?? null)),
                Forms\Components\Select::make('type')
                    ->options([
                        'asset'     => 'Asset',
                        'liability' => 'Liability',
                        'income'    => 'Income',
                        'cogs'      => 'COGS',
                        'expense'   => 'Expense',
                    ])
                    ->required()
                    ->disabled()
                    ->dehydrated()
                    ->helperText('Derived from the group.'),
                Forms\Components\Toggle::make('is_active')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('code')
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('business.name')->label('Business')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('group_name')->badge()->label('Group'),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('business_id')
                    ->label('Business')
                    ->options(fn () => Business::query()->where('user_id', auth()->id())->pluck('name', 'id')),
                Tables\Filters\SelectFilter::make('type')->options([
                    'asset' => 'Asset', 'liability' => 'Liability', 'income' => 'Income',
                    'cogs' => 'COGS', 'expense' => 'Expense',
                ]),
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn (LedgerAccount $record): bool => in_array($record->code, [LedgerAccount::SETTLEMENT_CODE, LedgerAccount::CONTRA_CODE], true)
                        || $record->transactions()->exists()
                        || $record->journalLines()->exists()),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
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
            'index'  => Pages\ListLedgerAccounts::route('/'),
            'create' => Pages\CreateLedgerAccount::route('/create'),
            'edit'   => Pages\EditLedgerAccount::route('/{record}/edit'),
        ];
    }
}
