<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\BusinessDocuments;
use App\Filament\Resources\ReceiptResource\Pages;
use App\Models\Invoice;
use App\Support\BusinessDocumentForm;
use App\Support\InvoicePdfBuilder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ReceiptResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static ?string $cluster = BusinessDocuments::class;
    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationGroup = 'Business Finance';
    protected static ?int $navigationSort = 42;

    public static function getNavigationLabel(): string
    {
        return 'Receipts';
    }

    public static function getModelLabel(): string
    {
        return 'receipt';
    }

    public static function getPluralModelLabel(): string
    {
        return 'receipts';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Receipt Header')->schema([
                Forms\Components\Select::make('business_id')
                    ->label('Business')
                    ->relationship('business', 'name')
                    ->required()->searchable()->preload(),
                Forms\Components\Select::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->required()->searchable()->preload(),
                Forms\Components\TextInput::make('invoice_number')
                    ->label('Receipt Number')
                    ->required()->default(fn () => 'RCP-' . strtoupper(Str::random(6))),
                Forms\Components\Hidden::make('type')->default('receipt'),
                Forms\Components\DatePicker::make('issue_date')->required()->default(now()),
                Forms\Components\DatePicker::make('due_date'),
                Forms\Components\Select::make('status')
                    ->options(['draft' => 'Draft', 'paid' => 'Paid', 'cancelled' => 'Cancelled'])
                    ->default('paid'),
            ])->columns(2),

            BusinessDocumentForm::lineItemsSection(),

            BusinessDocumentForm::totalsSection(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')->label('Receipt #')->searchable(),
                Tables\Columns\TextColumn::make('customer.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('issue_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('total_amount')->money('ZMW')->sortable(),
                Tables\Columns\TextColumn::make('amount_paid')->money('ZMW'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray'    => 'draft',
                        'success' => 'paid',
                        'danger'  => 'cancelled',
                    ]),
            ])
            ->defaultSort('issue_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status'),
            ])
            ->actions([
                Tables\Actions\Action::make('downloadPdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn (Invoice $record) => app(InvoicePdfBuilder::class)->download($record)),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('type', 'receipt')
            ->whereHas('business', fn ($q) => $q->where('user_id', auth()->id()));
    }

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) auth()->user()?->profile?->hasFeature('has_business');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListReceipts::route('/'),
            'create' => Pages\CreateReceipt::route('/create'),
            'edit'   => Pages\EditReceipt::route('/{record}/edit'),
        ];
    }
}
