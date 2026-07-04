<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource;
use App\Filament\Resources\QuotationResource\Pages;
use App\Models\Invoice;
use App\Support\BusinessDocumentConverter;
use App\Support\BusinessDocumentForm;
use App\Support\InvoicePdfBuilder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class QuotationResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static ?string $navigationGroup = 'Business Finance';
    protected static ?string $navigationParentItem = 'Business Documents';
    protected static ?int $navigationSort = 41;

    public static function getNavigationLabel(): string
    {
        return 'Quotations';
    }

    public static function getModelLabel(): string
    {
        return 'quotation';
    }

    public static function getPluralModelLabel(): string
    {
        return 'quotations';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Quotation Header')->schema([
                Forms\Components\Select::make('business_id')
                    ->label('Business')
                    ->relationship('business', 'name', fn (Builder $query) => $query->where('user_id', auth()->id()))
                    ->default(fn () => \App\Models\Business::query()->where('user_id', auth()->id())->value('id'))
                    ->required()->searchable()->preload(),
                Forms\Components\Select::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name', fn (Builder $query) => $query->whereHas('business', fn (Builder $b) => $b->where('user_id', auth()->id())))
                    ->required()->searchable()->preload(),
                Forms\Components\TextInput::make('invoice_number')
                    ->label('Quotation Number')
                    ->required()->default(fn () => 'QTN-' . strtoupper(Str::random(6))),
                Forms\Components\Hidden::make('type')->default('quotation'),
                Forms\Components\DatePicker::make('issue_date')->required()->default(now()),
                Forms\Components\DatePicker::make('due_date'),
                Forms\Components\Select::make('status')
                    ->options(['draft' => 'Draft', 'sent' => 'Sent', 'cancelled' => 'Cancelled'])
                    ->default('draft'),
            ])->columns(2),

            BusinessDocumentForm::lineItemsSection(),

            BusinessDocumentForm::totalsSection(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')->label('Quotation #')->searchable(),
                Tables\Columns\TextColumn::make('customer.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('issue_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('due_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('total_amount')->money('ZMW')->sortable(),
                Tables\Columns\SelectColumn::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'sent' => 'Sent',
                        'cancelled' => 'Cancelled',
                    ])
                    ->rules(['required']),
            ])
            ->defaultSort('issue_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status'),
            ])
            ->actions([
                Tables\Actions\Action::make('convertToInvoice')
                    ->label('Convert to Invoice')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('success')
                    ->modalHeading('Convert quotation to invoice')
                    ->modalDescription('Adjust any fields below before creating the invoice.')
                    ->modalSubmitActionLabel('Convert')
                    ->form([
                        Forms\Components\DatePicker::make('issue_date')
                            ->required()
                            ->default(now()),
                        Forms\Components\DatePicker::make('due_date')
                            ->default(fn (Invoice $record) => $record->due_date),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'sent' => 'Sent',
                                'partial' => 'Partial',
                                'paid' => 'Paid',
                                'overdue' => 'Overdue',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('draft')
                            ->required(),
                        Forms\Components\TextInput::make('tax_amount')
                            ->numeric()
                            ->default(fn (Invoice $record) => (float) $record->tax_amount)
                            ->minValue(0)
                            ->prefix('ZMW'),
                        Forms\Components\TextInput::make('discount_amount')
                            ->numeric()
                            ->default(fn (Invoice $record) => (float) $record->discount_amount)
                            ->minValue(0)
                            ->prefix('ZMW'),
                        Forms\Components\Textarea::make('notes')
                            ->default(fn (Invoice $record) => $record->notes)
                            ->columnSpanFull(),
                    ])
                    ->action(function (Invoice $record, array $data) {
                        $invoice = BusinessDocumentConverter::convert($record, 'invoice', [
                            'issue_date' => $data['issue_date'] ?? now(),
                            'due_date' => $data['due_date'] ?? null,
                            'status' => $data['status'] ?? 'draft',
                            'tax_amount' => $data['tax_amount'] ?? 0,
                            'discount_amount' => $data['discount_amount'] ?? 0,
                            'notes' => $data['notes'] ?? null,
                        ]);

                        Notification::make()
                            ->title('Invoice created from quotation')
                            ->body("Invoice {$invoice->invoice_number} was created successfully.")
                            ->success()
                            ->send();

                        return redirect(InvoiceResource::getUrl('index'));
                    }),
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
            ->where('type', 'quotation')
            ->whereHas('business', fn ($q) => $q->where('user_id', auth()->id()));
    }

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) auth()->user()?->profile?->hasFeature('has_business');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListQuotations::route('/'),
            'create' => Pages\CreateQuotation::route('/create'),
            'edit'   => Pages\EditQuotation::route('/{record}/edit'),
        ];
    }
}
