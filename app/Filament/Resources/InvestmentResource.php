<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvestmentResource\Pages;
use App\Models\Investment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvestmentResource extends Resource
{
    protected static ?string $model = Investment::class;
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Wealth Building';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Investment Details')->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\Select::make('type')
                    ->required()
                    ->options([
                        'stocks'        => 'Listed Shares (LuSE / Global)',
                        'bonds'         => 'Government / Corporate Bonds',
                        'treasury_bills' => 'Treasury Bills (BoZ)',
                        'fixed_deposit' => 'Fixed Deposit',
                        'unit_trust'    => 'Unit Trust / CIS',
                        'mutual_fund'   => 'Mutual Fund',
                        'real_estate'   => 'Real Estate',
                        'cryptocurrency' => 'Cryptocurrency',
                        'business'      => 'Business Equity',
                        'farming'       => 'Farming / Livestock',
                        'other'         => 'Other',
                    ])
                    ->live()
                    ->native(false),
                Forms\Components\Select::make('details.subtype')
                    ->label('Subtype')
                    ->options(fn (Get $get): array => self::investmentSubtypeOptions((string) $get('type')))
                    ->searchable()
                    ->native(false),
                Forms\Components\TextInput::make('institution')->maxLength(255),
                Forms\Components\TextInput::make('initial_amount')
                    ->required()->numeric()->prefix('ZMW')
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::syncInvestmentValues($set, $get)),
                Forms\Components\TextInput::make('details.rate_percent')
                    ->label('Annual return rate')
                    ->numeric()->suffix('%')->default(0)
                    ->visible(fn (Get $get): bool => in_array($get('type'), ['treasury_bills', 'bonds', 'fixed_deposit'], true))
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::syncInvestmentValues($set, $get)),
                Forms\Components\TextInput::make('details.tenor_months')
                    ->label('Tenor (months)')
                    ->numeric()->integer()->minValue(1)
                    ->visible(fn (Get $get): bool => in_array($get('type'), ['treasury_bills', 'bonds', 'fixed_deposit'], true))
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::syncInvestmentValues($set, $get)),
                Forms\Components\TextInput::make('details.units')
                    ->label('Units / shares held')
                    ->numeric()->minValue(0)
                    ->visible(fn (Get $get): bool => in_array($get('type'), ['stocks', 'unit_trust', 'mutual_fund', 'cryptocurrency'], true))
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::syncInvestmentValues($set, $get)),
                Forms\Components\TextInput::make('details.current_unit_price')
                    ->label('Current unit/share price')
                    ->numeric()->prefix('ZMW')->minValue(0)
                    ->visible(fn (Get $get): bool => in_array($get('type'), ['stocks', 'unit_trust', 'mutual_fund', 'cryptocurrency'], true))
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::syncInvestmentValues($set, $get)),
                Forms\Components\TextInput::make('details.annual_net_income')
                    ->label('Estimated annual net income')
                    ->numeric()->prefix('ZMW')->minValue(0)
                    ->visible(fn (Get $get): bool => in_array($get('type'), ['real_estate', 'business', 'farming'], true))
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::syncInvestmentValues($set, $get)),
                Forms\Components\TextInput::make('current_value')
                    ->required()->numeric()->prefix('ZMW')
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::syncInvestmentValues($set, $get)),
                Forms\Components\TextInput::make('expected_return_rate')
                    ->numeric()->suffix('%')->default(0),
                Forms\Components\DatePicker::make('start_date')->required(),
                Forms\Components\DatePicker::make('maturity_date'),
                Forms\Components\Select::make('status')
                    ->options(['active' => 'Active', 'matured' => 'Matured', 'sold' => 'Sold', 'cancelled' => 'Cancelled'])
                    ->default('active'),
                Forms\Components\Textarea::make('notes')->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('institution'),
                Tables\Columns\TextColumn::make('initial_amount')->money('ZMW')->sortable(),
                Tables\Columns\TextColumn::make('current_value')->money('ZMW')->sortable(),
                Tables\Columns\TextColumn::make('return_percent')
                    ->label('Return %')
                    ->suffix('%')
                    ->color(fn ($record) => $record->return_amount >= 0 ? 'success' : 'danger'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['success' => 'active', 'info' => 'matured', 'gray' => fn ($state) => in_array($state, ['sold', 'cancelled'], true)]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type'),
                Tables\Filters\SelectFilter::make('status')
                    ->options(['active' => 'Active', 'matured' => 'Matured', 'sold' => 'Sold']),
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
        return (bool) auth()->user()?->profile?->hasFeature('has_investments');
    }


    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListInvestments::route('/'),
            'create' => Pages\CreateInvestment::route('/create'),
            'edit'   => Pages\EditInvestment::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function investmentSubtypeOptions(string $type): array
    {
        return match ($type) {
            'treasury_bills' => [
                '91_day' => '91-day Treasury Bill',
                '182_day' => '182-day Treasury Bill',
                '273_day' => '273-day Treasury Bill',
                '364_day' => '364-day Treasury Bill',
            ],
            'bonds' => [
                'government_bond' => 'Government Bond',
                'corporate_bond' => 'Corporate Bond',
                'infrastructure_bond' => 'Infrastructure Bond',
            ],
            'fixed_deposit' => [
                '30_day' => '30-day Fixed Deposit',
                '90_day' => '90-day Fixed Deposit',
                '180_day' => '180-day Fixed Deposit',
                '365_day' => '365-day Fixed Deposit',
            ],
            'stocks' => [
                'luse_equity' => 'LuSE Listed Equity',
                'regional_equity' => 'Regional Equity',
                'global_equity' => 'Global Equity',
            ],
            'unit_trust', 'mutual_fund' => [
                'money_market' => 'Money Market Fund',
                'balanced_fund' => 'Balanced Fund',
                'equity_fund' => 'Equity Fund',
                'income_fund' => 'Income Fund',
            ],
            'real_estate' => [
                'rental_property' => 'Rental Property',
                'land_bank' => 'Land Bank',
                'commercial_property' => 'Commercial Property',
            ],
            'farming' => [
                'crop_farming' => 'Crop Farming',
                'livestock' => 'Livestock',
                'mixed_farming' => 'Mixed Farming',
            ],
            default => [],
        };
    }

    private static function syncInvestmentValues(Set $set, Get $get): void
    {
        $type = (string) ($get('type') ?? 'other');
        $initialAmount = self::floatValue($get('initial_amount'));
        $currentValue = self::floatValue($get('current_value'));

        if ($currentValue <= 0) {
            if (in_array($type, ['treasury_bills', 'bonds', 'fixed_deposit'], true)) {
                $rate = self::floatValue($get('details.rate_percent'));
                $tenorMonths = max((int) self::floatValue($get('details.tenor_months')), 0);
                if ($initialAmount > 0 && $rate > 0 && $tenorMonths > 0) {
                    $currentValue = round($initialAmount * (1 + (($rate / 100) * ($tenorMonths / 12))), 2);
                    $set('current_value', $currentValue);
                }
            } elseif (in_array($type, ['stocks', 'unit_trust', 'mutual_fund', 'cryptocurrency'], true)) {
                $units = self::floatValue($get('details.units'));
                $currentUnitPrice = self::floatValue($get('details.current_unit_price'));
                if ($units > 0 && $currentUnitPrice > 0) {
                    $currentValue = round($units * $currentUnitPrice, 2);
                    $set('current_value', $currentValue);
                }
            }
        }

        if ($initialAmount > 0) {
            if (in_array($type, ['real_estate', 'business', 'farming'], true) && self::floatValue($get('details.annual_net_income')) > 0) {
                $set('expected_return_rate', round((self::floatValue($get('details.annual_net_income')) / $initialAmount) * 100, 2));
            } elseif ($currentValue > 0) {
                $set('expected_return_rate', round((($currentValue - $initialAmount) / $initialAmount) * 100, 2));
            }
        }
    }

    private static function floatValue(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        return (float) str_replace(',', '', (string) $value);
    }
}
