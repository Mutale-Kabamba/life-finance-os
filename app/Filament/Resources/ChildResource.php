<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChildResource\Pages;
use App\Models\Child;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ChildResource extends Resource
{
    protected static ?string $model = Child::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Family';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Children';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Child Details')->schema([
                Forms\Components\TextInput::make('first_name')->required()->maxLength(100),
                Forms\Components\TextInput::make('last_name')->required()->maxLength(100),
                Forms\Components\DatePicker::make('date_of_birth')->required(),
                Forms\Components\TextInput::make('school_name')->maxLength(255),
                Forms\Components\TextInput::make('grade')->maxLength(20),
            ])->columns(2),

            Forms\Components\Section::make('Monthly Costs')->schema([
                Forms\Components\TextInput::make('annual_school_fees')
                    ->numeric()->prefix('ZMW')->default(0),
                Forms\Components\TextInput::make('monthly_transport')
                    ->numeric()->prefix('ZMW')->default(0),
                Forms\Components\TextInput::make('monthly_medical')
                    ->numeric()->prefix('ZMW')->default(0),
                Forms\Components\TextInput::make('monthly_other')
                    ->numeric()->prefix('ZMW')->default(0),
                Forms\Components\Textarea::make('notes')->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')->searchable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('date_of_birth')->date()->sortable(),
                Tables\Columns\TextColumn::make('age')->suffix(' yrs'),
                Tables\Columns\TextColumn::make('school_name')->searchable(),
                Tables\Columns\TextColumn::make('grade'),
                Tables\Columns\TextColumn::make('annual_school_fees')->money('ZMW'),
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

    /**
     * Only show this resource in the sidebar when the user enabled the
     * "has_children" feature during onboarding (stored in the
     * profiles.feature_registry JSON column).
     */
    public static function shouldRegisterNavigation(): bool
    {
        return (bool) auth()->user()?->profile?->hasFeature('has_children');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListChildren::route('/'),
            'create' => Pages\CreateChild::route('/create'),
            'edit'   => Pages\EditChild::route('/{record}/edit'),
        ];
    }
}
