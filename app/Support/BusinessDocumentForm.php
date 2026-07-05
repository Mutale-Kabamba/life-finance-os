<?php

namespace App\Support;

use App\Models\Inventory;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;

class BusinessDocumentForm
{
    /**
     * Recalculate a single line's total and the document-level totals.
     */
    public static function recalculate(Get $get, Set $set): void
    {
        $quantity = (float) ($get('quantity') ?? 0);
        $unitPrice = (float) ($get('unit_price') ?? 0);

        $set('total_price', round($quantity * $unitPrice, 2));

        self::recalculateTotalsFromItems($get, $set);
    }

    /**
     * Recompute subtotal and total_amount from all line items and tax/discount.
     */
    public static function recalculateTotalsFromItems(Get $get, Set $set): void
    {
        $items = $get('../../items') ?? [];

        $subtotal = collect($items)->sum(
            fn ($row): float => (float) ($row['quantity'] ?? 0) * (float) ($row['unit_price'] ?? 0)
        );

        $tax = (float) ($get('../../tax_amount') ?? 0);
        $discount = (float) ($get('../../discount_amount') ?? 0);

        $set('../../subtotal', round($subtotal, 2));
        $set('../../total_amount', round($subtotal + $tax - $discount, 2));
    }

    /**
     * Recompute totals from the document (root) scope.
     */
    public static function recalculateTotalsFromRoot(Get $get, Set $set): void
    {
        $items = $get('items') ?? [];

        $subtotal = collect($items)->sum(
            fn ($row): float => (float) ($row['quantity'] ?? 0) * (float) ($row['unit_price'] ?? 0)
        );

        $tax = (float) ($get('tax_amount') ?? 0);
        $discount = (float) ($get('discount_amount') ?? 0);

        $set('subtotal', round($subtotal, 2));
        $set('total_amount', round($subtotal + $tax - $discount, 2));
    }

    public static function lineItemsSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Line Items')
            ->schema([
                Forms\Components\Repeater::make('items')
                    ->relationship()
                    ->schema([
                        Forms\Components\Select::make('inventory_id')
                            ->label('Product / Service')
                            ->options(function (Get $get): array {
                                $businessId = $get('../../business_id');

                                if (! $businessId) {
                                    return [];
                                }

                                return Inventory::query()
                                    ->where('business_id', $businessId)
                                    ->where('is_active', true)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->all();
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, Get $get, Set $set): void {
                                $product = Inventory::find($state);

                                if ($product) {
                                    $set('description', $product->name);
                                    $set('unit_price', (float) $product->selling_price);
                                }

                                self::recalculate($get, $set);
                            })
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('description')
                            ->required()
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('quantity')
                            ->numeric()
                            ->required()
                            ->default(1)
                            ->minValue(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculate($get, $set)),
                        Forms\Components\TextInput::make('unit_price')
                            ->numeric()
                            ->required()
                            ->prefix('ZMW')
                            ->minValue(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculate($get, $set)),
                        Forms\Components\TextInput::make('total_price')
                            ->numeric()
                            ->prefix('ZMW')
                            ->disabled()
                            ->dehydrated(true)
                            ->default(0)
                            ->dehydrateStateUsing(fn (Get $get): float => round(((float) ($get('quantity') ?? 0)) * ((float) ($get('unit_price') ?? 0)), 2))
                            ->formatStateUsing(fn (Get $get): float => round(((float) ($get('quantity') ?? 0)) * ((float) ($get('unit_price') ?? 0)), 2)),
                    ])
                    ->columns(8)
                    ->defaultItems(1)
                    ->addActionLabel('Add line item')
                    ->live()
                    ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateTotalsFromRoot($get, $set))
                    ->itemLabel(fn (array $state): ?string => $state['description'] ?? null),
            ]);
    }

    public static function totalsSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Totals')
            ->schema([
                Forms\Components\TextInput::make('subtotal')
                    ->numeric()
                    ->prefix('ZMW')
                    ->default(0)
                    ->readOnly()
                    ->dehydrated(true),
                Forms\Components\TextInput::make('tax_amount')
                    ->label('Tax')
                    ->numeric()
                    ->prefix('ZMW')
                    ->default(0)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateTotalsFromRoot($get, $set)),
                Forms\Components\TextInput::make('discount_amount')
                    ->label('Discount')
                    ->numeric()
                    ->prefix('ZMW')
                    ->default(0)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateTotalsFromRoot($get, $set)),
                Forms\Components\TextInput::make('total_amount')
                    ->label('Total')
                    ->numeric()
                    ->prefix('ZMW')
                    ->default(0)
                    ->readOnly()
                    ->dehydrated(true),
                Forms\Components\TextInput::make('amount_paid')
                    ->numeric()
                    ->prefix('ZMW')
                    ->default(0),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}
