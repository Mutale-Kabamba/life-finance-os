<?php

namespace App\Support;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class InvoicePdfBuilder
{
    public function download(Invoice $invoice): Response
    {
        $invoice->loadMissing(['business', 'customer', 'items']);

        $documentType = ucfirst((string) ($invoice->type ?: 'invoice'));
        $fileName = $this->buildFileName($invoice, $documentType);

        $pdf = Pdf::loadView('pdf.business-document', [
            'invoice' => $invoice,
            'documentType' => $documentType,
        ])->setPaper('a4');

        return $pdf->download($fileName);
    }

    private function buildFileName(Invoice $invoice, string $documentType): string
    {
        $number = $invoice->invoice_number ?: ($documentType . '-' . $invoice->id);

        return Str::slug($number) . '.pdf';
    }
}
