<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Support\BusinessDocumentConverter;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('createFromQuotation')
                ->label('Create from Quotation')
                ->icon('heroicon-o-document-duplicate')
                ->color('gray')
                ->modalHeading('Create invoice from quotation')
                ->modalDescription('Select a quotation and adjust fields before creating the invoice.')
                ->modalSubmitActionLabel('Convert')
                ->form([
                    Forms\Components\Select::make('quotation_id')
                        ->label('Available Quotation')
                        ->options(fn (): array => Invoice::query()
                            ->where('type', 'quotation')
                            ->whereHas('business', fn (Builder $q) => $q->where('user_id', auth()->id()))
                            ->whereDoesntHave('conversions', fn (Builder $q) => $q->where('type', 'invoice'))
                            ->orderByDesc('issue_date')
                            ->get()
                            ->mapWithKeys(fn (Invoice $quotation): array => [
                                $quotation->getKey() => "{$quotation->invoice_number} — {$quotation->customer?->name} (ZMW " . number_format((float) $quotation->total_amount, 2) . ')',
                            ])
                            ->all())
                        ->searchable()
                        ->required()
                        ->helperText('Only quotations not yet converted to an invoice are shown.'),
                    Forms\Components\DatePicker::make('issue_date')
                        ->default(now())
                        ->required(),
                    Forms\Components\DatePicker::make('due_date'),
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
                        ->default(0)
                        ->minValue(0)
                        ->prefix('ZMW'),
                    Forms\Components\TextInput::make('discount_amount')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->prefix('ZMW'),
                    Forms\Components\Textarea::make('notes')
                        ->columnSpanFull(),
                ])
                ->action(function (array $data) {
                    $quotation = Invoice::findOrFail($data['quotation_id']);
                    $invoice = BusinessDocumentConverter::convert($quotation, 'invoice', [
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
        ];
    }
}
