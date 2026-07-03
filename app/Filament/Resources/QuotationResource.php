<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\BusinessDocuments;
use App\Filament\Resources\QuotationResource\Pages;
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

class QuotationResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static ?string $cluster = BusinessDocuments::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static ?string $navigationGroup = 'Business Finance';
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
                    ->relationship('business', 'name')
                    ->required()->searchable()->preload(),
                Forms\Components\Select::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
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
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray'   => 'draft',
                        'info'   => 'sent',
                        'danger' => 'cancelled',
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
