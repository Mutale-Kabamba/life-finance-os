<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncomeSourceResource\Pages;
use App\Models\IncomeSource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class IncomeSourceResource extends Resource
{
    protected static ?string $model = IncomeSource::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-up';
    protected static ?string $navigationGroup = 'Personal Finance';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Income Details')->schema([
                Forms\Components\TextInput::make('name')
                    ->required()->maxLength(255),
                Forms\Components\Select::make('type')
                    ->required()
                    ->options([
                        'salary'      => 'Salary',
                        'business'    => 'Business Income',
                        'freelancing' => 'Freelancing',
                        'farming'     => 'Farming',
                        'rental'      => 'Rental Income',
                        'investment'  => 'Investment Returns',
                        'side_hustle' => 'Side Hustle',
                        'pension'     => 'Pension',
                        'other'       => 'Other',
                    ]),
                Forms\Components\TextInput::make('amount')
                    ->required()->numeric()->prefix('ZMW'),
                Forms\Components\Select::make('frequency')
                    ->required()
                    ->options([
                        'daily'     => 'Daily',
                        'weekly'    => 'Weekly',
                        'bi_weekly' => 'Bi-Weekly',
                        'monthly'   => 'Monthly',
                        'quarterly' => 'Quarterly',
                        'annually'  => 'Annually',
                    ]),
                Forms\Components\DatePicker::make('start_date'),
                Forms\Components\Toggle::make('is_active')->default(true),
                Forms\Components\Textarea::make('notes')->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'success' => 'salary',
                        'info'    => 'business',
                        'warning' => fn ($state) => in_array($state, ['freelancing', 'side_hustle']),
                    ]),
                Tables\Columns\TextColumn::make('amount')
                    ->money('ZMW')->sortable(),
                Tables\Columns\TextColumn::make('frequency')->badge(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'salary'      => 'Salary',
                        'business'    => 'Business Income',
                        'freelancing' => 'Freelancing',
                        'rental'      => 'Rental Income',
                        'investment'  => 'Investment Returns',
                        'other'       => 'Other',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
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
            'index'  => Pages\ListIncomeSources::route('/'),
            'create' => Pages\CreateIncomeSource::route('/create'),
            'edit'   => Pages\EditIncomeSource::route('/{record}/edit'),
        ];
    }
}
