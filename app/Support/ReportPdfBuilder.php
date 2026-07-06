<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Business;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportPdfBuilder
{
    /**
     * @param  array<string, mixed>  $incomeStatement
     * @param  array<string, mixed>  $balanceSheet
     * @param  array<string, mixed>  $trialBalance
     */
    public function downloadFinancialReports(
        Business $business,
        string $start,
        string $end,
        array $incomeStatement,
        array $balanceSheet,
        array $trialBalance,
    ): StreamedResponse {
        $pdf = Pdf::loadView('pdf.financial-reports', [
            'business' => $business,
            'start' => $start,
            'end' => $end,
            'incomeStatement' => $incomeStatement,
            'balanceSheet' => $balanceSheet,
            'trialBalance' => $trialBalance,
        ])->setPaper('a4');

        $fileName = 'financial-report-' . now()->format('Ymd-His') . '.pdf';

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $fileName,
            ['Content-Type' => 'application/pdf'],
        );
    }

    /**
     * @param  array<string, mixed>  $report
     */
    public function downloadSuppliersAging(Business $business, string $asOf, array $report): StreamedResponse
    {
        $pdf = Pdf::loadView('pdf.suppliers-aging-report', [
            'business' => $business,
            'asOf' => $asOf,
            'report' => $report,
        ])->setPaper('a4');

        $fileName = 'suppliers-aging-' . now()->format('Ymd-His') . '.pdf';

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $fileName,
            ['Content-Type' => 'application/pdf'],
        );
    }
}
