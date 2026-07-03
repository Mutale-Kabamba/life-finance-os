<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Business Finance';
    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Invoice Header')->schema([
                Forms\Components\Select::make('business_id')
                    ->label('Business')
                    ->relationship('business', 'name')
                    ->required()->searchable()->preload(),
                Forms\Components\Select::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->required()->searchable()->preload(),
                Forms\Components\TextInput::make('invoice_number')
                    ->required()->default(fn () => 'INV-' . strtoupper(Str::random(6))),
                Forms\Components\Select::make('type')
                    ->options(['invoice' => 'Invoice', 'quotation' => 'Quotation', 'receipt' => 'Receipt'])
                    ->default('invoice'),
                Forms\Components\DatePicker::make('issue_date')->required()->default(now()),
                Forms\Components\DatePicker::make('due_date'),
                Forms\Components\Select::make('status')
                    ->options(['draft' => 'Draft', 'sent' => 'Sent', 'partial' => 'Partial', 'paid' => 'Paid', 'overdue' => 'Overdue', 'cancelled' => 'Cancelled'])
                    ->default('draft'),
            ])->columns(2),

            Forms\Components\Section::make('Line Items')->schema([
                Forms\Components\Repeater::make('items')
                    ->relationship()
                    ->schema([
                        Forms\Components\TextInput::make('description')->required()->columnSpan(3),
                        Forms\Components\TextInput::make('quantity')->numeric()->required()->default(1),
                        Forms\Components\TextInput::make('unit_price')->numeric()->required()->prefix('ZMW'),
                        Forms\Components\TextInput::make('total_price')->numeric()->prefix('ZMW')->disabled(),
                    ])->columns(6),
            ]),

            Forms\Components\Section::make('Totals')->schema([
                Forms\Components\TextInput::make('subtotal')->numeric()->prefix('ZMW')->default(0),
                Forms\Components\TextInput::make('tax_amount')->numeric()->prefix('ZMW')->default(0),
                Forms\Components\TextInput::make('discount_amount')->numeric()->prefix('ZMW')->default(0),
                Forms\Components\TextInput::make('total_amount')->numeric()->prefix('ZMW')->default(0),
                Forms\Components\TextInput::make('amount_paid')->numeric()->prefix('ZMW')->default(0),
                Forms\Components\Textarea::make('notes')->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')->searchable(),
                Tables\Columns\TextColumn::make('customer.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('issue_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('due_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('total_amount')->money('ZMW')->sortable(),
                Tables\Columns\TextColumn::make('amount_paid')->money('ZMW'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray'    => 'draft',
                        'info'    => 'sent',
                        'warning' => 'partial',
                        'success' => 'paid',
                        'danger'  => 'overdue',
                    ]),
            ])
            ->defaultSort('issue_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status'),
                Tables\Filters\SelectFilter::make('type'),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('business', fn ($q) => $q->where('user_id', auth()->id()));
    }

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) auth()->user()?->profile?->hasFeature('has_business');
    }


    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit'   => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
