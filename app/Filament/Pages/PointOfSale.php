<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Clusters\BusinessOperations;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Inventory;
use App\Services\PointOfSaleService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Throwable;

class PointOfSale extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $cluster = BusinessOperations::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Business Finance';
    protected static ?string $navigationLabel = 'Point of Sale';
    protected static ?int $navigationSort = 6;
    protected static string $view = 'filament.pages.point-of-sale';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'business_id'    => Business::query()->where('user_id', auth()->id())->value('id'),
            'date'           => now()->toDateString(),
            'create_invoice' => true,
            'items'          => [],
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Sale')
                    ->schema([
                        Select::make('business_id')
                            ->label('Business')
                            ->options(fn () => Business::query()->where('user_id', auth()->id())->pluck('name', 'id'))
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('items', [])),
                        Select::make('customer_id')
                            ->label('Customer (optional)')
                            ->options(fn (Get $get) => Customer::query()
                                ->where('business_id', $get('business_id'))
                                ->pluck('name', 'id'))
                            ->searchable(),
                        DatePicker::make('date')->required(),
                        Toggle::make('create_invoice')
                            ->label('Generate invoice record')
                            ->default(true),
                    ])
                    ->columns(2),

                Section::make('Cart')
                    ->schema([
                        Repeater::make('items')
                            ->hiddenLabel()
                            ->schema([
                                Select::make('inventory_id')
                                    ->label('Product')
                                    ->options(fn (Get $get) => Inventory::query()
                                        ->where('business_id', $get('../../business_id'))
                                        ->where('is_active', true)
                                        ->orderBy('name')
                                        ->get()
                                        ->mapWithKeys(fn (Inventory $p) => [
                                            $p->id => "{$p->name} ({$p->quantity_on_hand} {$p->unit})",
                                        ]))
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        $product = Inventory::find($state);
                                        if ($product) {
                                            $set('unit_price', (float) $product->selling_price);
                                        }
                                    })
                                    ->columnSpan(2),
                                TextInput::make('quantity')
                                    ->numeric()->integer()->minValue(1)->default(1)->required()->live(),
                                TextInput::make('unit_price')
                                    ->label('Unit price')
                                    ->numeric()->prefix('ZMW')->required()->live(),
                                Placeholder::make('line_total')
                                    ->label('Subtotal')
                                    ->content(fn (Get $get) => 'ZMW ' . number_format(
                                        (float) ($get('unit_price') ?? 0) * (int) ($get('quantity') ?? 0),
                                        2
                                    )),
                            ])
                            ->columns(5)
                            ->addActionLabel('Add product')
                            ->reorderable(false)
                            ->defaultItems(1),

                        Placeholder::make('grand_total')
                            ->label('Total due')
                            ->content(fn (Get $get) => 'ZMW ' . number_format($this->cartTotal($get('items')), 2)),
                    ]),
            ])
            ->statePath('data');
    }

    /**
     * @param array<int, array<string, mixed>>|null $items
     */
    public function cartTotal(?array $items): float
    {
        return round(collect($items ?? [])
            ->sum(fn ($i) => (float) ($i['unit_price'] ?? 0) * (int) ($i['quantity'] ?? 0)), 2);
    }

    public function checkout(): void
    {
        $state = $this->form->getState();

        try {
            $customerId = filled($state['customer_id'] ?? null)
                ? (int) $state['customer_id']
                : null;

            $result = app(PointOfSaleService::class)->checkout([
                'business_id'    => (int) $state['business_id'],
                'user_id'        => (int) auth()->id(),
                'customer_id'    => $customerId,
                'date'           => $state['date'] ?? now()->toDateString(),
                'items'          => $state['items'] ?? [],
                'create_invoice' => (bool) ($state['create_invoice'] ?? false),
                'notes'          => null,
            ]);
        } catch (Throwable $e) {
            Notification::make()
                ->title('Sale could not be completed')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title('Sale completed')
            ->body("{$result['reference']} — ZMW " . number_format($result['total'], 2) . " across {$result['items']} item(s).")
            ->success()
            ->send();

        $this->form->fill([
            'business_id'    => $state['business_id'],
            'customer_id'    => null,
            'date'           => now()->toDateString(),
            'create_invoice' => (bool) ($state['create_invoice'] ?? true),
            'items'          => [],
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('checkout')
                ->label('Complete sale')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->action('checkout'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) auth()->user()?->profile?->hasFeature('has_business');
    }
}
