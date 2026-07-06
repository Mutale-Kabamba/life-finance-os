<?php

namespace App\Filament\Resources\DebtResource\Pages;

use App\Filament\Resources\DebtResource;
use App\Filament\Resources\Pages\CreateRecord;

class CreateDebt extends CreateRecord
{
    protected static string $resource = DebtResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        $principal = (float) ($data['original_amount'] ?? 0);
        $type = (string) ($data['type'] ?? 'personal_loan');

        if ($type === 'hire_purchase') {
            $details = (array) ($data['details'] ?? []);
            $deposit = (float) ($details['deposit_amount'] ?? 0);
            $termMonths = max((int) ($details['term_months'] ?? 0), 0);
            $installment = (float) ($data['monthly_installment'] ?? 0);
            $totalRepayment = (float) ($data['total_repayment_amount'] ?? 0);

            if ($totalRepayment <= 0 && $installment > 0 && $termMonths > 0) {
                $totalRepayment = round(($installment * $termMonths) + $deposit, 2);
            }

            if ($totalRepayment <= 0) {
                $totalRepayment = $principal;
            }

            $financedRepayment = max($totalRepayment - $deposit, 0);
            $data['total_repayment_amount'] = $totalRepayment;
            $data['outstanding_balance'] = $financedRepayment;

            $financedCashPrice = max($principal - $deposit, 0);
            if ($financedCashPrice > 0) {
                $data['interest_rate'] = round(max((($financedRepayment - $financedCashPrice) / $financedCashPrice) * 100, 0), 2);
            }

            if ($termMonths > 0 && $financedRepayment > 0) {
                $details['suggested_installment'] = round($financedRepayment / $termMonths, 2);
                $details['remaining_term_months'] = $termMonths;
            }

            $details['financed_amount'] = round($financedRepayment, 2);
            $data['details'] = $details;
        } else {
            $payableBase = (float) ($data['total_repayment_amount'] ?? 0);
            if ($payableBase <= 0) {
                $payableBase = $principal;
            }

            $data['outstanding_balance'] = max(0, $payableBase);

            if ($type !== 'personal_loan' && isset($data['details'])) {
                unset(
                    $data['details']['installments'],
                    $data['details']['suggested_installment'],
                    $data['details']['remaining_term_months'],
                );
            }
        }

        return $data;
    }
}
