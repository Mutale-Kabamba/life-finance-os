<x-filament-panels::page>
    <x-filament::section>
        <form wire:submit="generate">
            {{ $this->form }}
        </form>
    </x-filament::section>

    @php($money = fn ($v) => 'ZMW ' . number_format((float) ($v ?? 0), 2))

    @if ($incomeStatement)
        <x-filament::section heading="Income Statement">
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                <div class="flex justify-between"><span>Total income</span><span class="font-medium">{{ $money($incomeStatement['total_income']) }}</span></div>
                <div class="flex justify-between"><span>Direct costs</span><span class="font-medium">{{ $money($incomeStatement['direct_costs']) }}</span></div>
                <div class="flex justify-between"><span>Gross profit</span><span class="font-medium">{{ $money($incomeStatement['gross_profit']) }}</span></div>
                <div class="flex justify-between"><span>General expenses</span><span class="font-medium">{{ $money($incomeStatement['general_expenses']) }}</span></div>
                <div class="flex justify-between border-t pt-2 sm:col-span-2">
                    <span class="font-semibold">Net profit</span>
                    <span class="font-bold {{ $incomeStatement['net_profit'] < 0 ? 'text-danger-600' : 'text-success-600' }}">{{ $money($incomeStatement['net_profit']) }}</span>
                </div>
            </div>
        </x-filament::section>
    @endif

    @if ($balanceSheet)
        <x-filament::section heading="Balance Sheet">
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                <div class="flex justify-between"><span>Total assets</span><span class="font-medium">{{ $money($balanceSheet['total_assets']) }}</span></div>
                <div class="flex justify-between"><span>Total liabilities</span><span class="font-medium">{{ $money($balanceSheet['total_liabilities']) }}</span></div>
                <div class="flex justify-between"><span>Equity</span><span class="font-medium">{{ $money($balanceSheet['equity']) }}</span></div>
                <div class="flex justify-between"><span>Equation gap (should be 0)</span><span class="font-medium">{{ $money($balanceSheet['equation_gap']) }}</span></div>
            </div>
        </x-filament::section>
    @endif

    @if ($trialBalance)
        <x-filament::section heading="Trial Balance">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b">
                        <th class="py-1">Code</th>
                        <th>Account</th>
                        <th class="text-right">Debit</th>
                        <th class="text-right">Credit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($trialBalance['accounts'] as $row)
                        <tr class="border-b border-gray-100 dark:border-white/5">
                            <td class="py-1">{{ $row['code'] }}</td>
                            <td>{{ $row['name'] }}</td>
                            <td class="text-right">{{ $money($row['debit_total']) }}</td>
                            <td class="text-right">{{ $money($row['credit_total']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="font-semibold border-t">
                        <td colspan="2" class="py-1">Totals</td>
                        <td class="text-right">{{ $money($trialBalance['total_debit']) }}</td>
                        <td class="text-right">{{ $money($trialBalance['total_credit']) }}</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="py-1">Difference (should be 0)</td>
                        <td colspan="2" class="text-right">{{ $money($trialBalance['difference']) }}</td>
                    </tr>
                </tfoot>
            </table>
        </x-filament::section>
    @endif
</x-filament-panels::page>
