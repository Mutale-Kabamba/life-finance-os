<?php

namespace App\Filament\Resources\DebtResource\Pages;

use App\Filament\Resources\DebtResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDebt extends EditRecord
{
    protected static string $resource = DebtResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $type = (string) ($data['type'] ?? $this->record->type ?? 'personal_loan');

        if ($type === 'hire_purchase') {
            $details = (array) ($data['details'] ?? []);
            $deposit = (float) ($details['deposit_amount'] ?? 0);
            $termMonths = max((int) ($details['term_months'] ?? 0), 0);
            $principal = (float) ($data['original_amount'] ?? $this->record->original_amount ?? 0);
            $totalRepayment = (float) ($data['total_repayment_amount'] ?? $this->record->total_repayment_amount ?? 0);

            if ($totalRepayment <= 0) {
                $totalRepayment = $principal;
            }

            $financedRepayment = max($totalRepayment - $deposit, 0);
            $paid = (float) $this->record->payments()->sum('amount');
            $data['outstanding_balance'] = max($financedRepayment - $paid, 0);

            $financedCashPrice = max($principal - $deposit, 0);
            if ($financedCashPrice > 0) {
                $data['interest_rate'] = round(max((($financedRepayment - $financedCashPrice) / $financedCashPrice) * 100, 0), 2);
            }

            if ($termMonths > 0 && $financedRepayment > 0) {
                $suggested = round($financedRepayment / $termMonths, 2);
                $details['suggested_installment'] = $suggested;
                $details['remaining_term_months'] = max((int) ceil($data['outstanding_balance'] / max($suggested, 0.01)), 0);
            }

            $details['financed_amount'] = round($financedRepayment, 2);
            $data['details'] = $details;
        } else {
            $principal = (float) ($data['original_amount'] ?? $this->record->original_amount ?? 0);
            $payableBase = (float) ($data['total_repayment_amount'] ?? $this->record->total_repayment_amount ?? 0);
            if ($payableBase <= 0) {
                $payableBase = $principal;
            }

            $paid = (float) $this->record->payments()->sum('amount');
            $data['outstanding_balance'] = max($payableBase - $paid, 0);

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
