<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BusinessResource\Pages;
use App\Models\Business;
use App\Support\ZambiaReferenceData;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BusinessResource extends Resource
{
    protected static ?string $model = Business::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Business Finance';
    protected static ?string $navigationParentItem = 'Business Setup';
    protected static ?string $navigationLabel = 'Businesses';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Business Information')->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\TextInput::make('trading_name')->maxLength(255),
                Forms\Components\Select::make('type')
                    ->required()
                    ->options([
                        'sole_trader'     => 'Sole Trader',
                        'partnership'     => 'Partnership',
                        'private_limited' => 'Private Limited (Ltd)',
                        'public_limited'  => 'Public Limited (PLC)',
                        'cooperative'     => 'Cooperative',
                        'ngo'             => 'NGO / Non-Profit',
                        'other'           => 'Other',
                    ])
                    ->native(false),
                Forms\Components\Select::make('industry')
                    ->label('Industry')
                    ->options(function (Get $get): array {
                        $options = ZambiaReferenceData::businessIndustryOptions();
                        $current = trim((string) $get('industry'));
                        if ($current !== '' && ! array_key_exists($current, $options)) {
                            $options[$current] = $current;
                        }

                        return $options;
                    })
                    ->searchable()
                    ->native(false)
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label('Industry name')
                            ->required()
                            ->maxLength(100),
                    ])
                    ->createOptionUsing(fn (array $data): string => trim((string) ($data['name'] ?? ''))),
                Forms\Components\TextInput::make('registration_number')->maxLength(50),
                Forms\Components\TextInput::make('tax_number')->maxLength(50),
                Forms\Components\DatePicker::make('established_date'),
                Forms\Components\TextInput::make('currency')->maxLength(5)->default('ZMW'),
            ])->columns(2),

            Forms\Components\Section::make('Contact Details')->schema([
                Forms\Components\TextInput::make('phone')->tel()->maxLength(20),
                Forms\Components\TextInput::make('email')->email()->maxLength(255),
                Forms\Components\Textarea::make('address'),
                Forms\Components\Textarea::make('description')->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('industry'),
                Tables\Columns\TextColumn::make('phone'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('established_date')->date()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(['sole_trader' => 'Sole Trader', 'partnership' => 'Partnership', 'private_limited' => 'Ltd']),
                Tables\Filters\TernaryFilter::make('is_active'),
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
            'index'  => Pages\ListBusinesses::route('/'),
            'create' => Pages\CreateBusiness::route('/create'),
            'edit'   => Pages\EditBusiness::route('/{record}/edit'),
        ];
    }
}
