<?php

namespace App\Support;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Str;

class InvoicePdfBuilder
{
    public function download(Invoice $invoice): StreamedResponse
    {
        $invoice->loadMissing(['business', 'customer', 'items']);

        $documentType = ucfirst((string) ($invoice->type ?: 'invoice'));
        $fileName = $this->buildFileName($invoice, $documentType);

        $pdf = Pdf::loadView('pdf.business-document', [
            'invoice' => $invoice,
            'documentType' => $documentType,
        ])->setPaper('a4');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $fileName,
            ['Content-Type' => 'application/pdf'],
        );
    }

    private function buildFileName(Invoice $invoice, string $documentType): string
    {
        $number = $invoice->invoice_number ?: ($documentType . '-' . $invoice->id);

        return Str::slug($number) . '.pdf';
    }
}
