<x-filament-widgets::widget class="fi-wi-debt-overview">
    <x-filament::section>
        <x-slot name="heading">Debt Intelligence</x-slot>
        <x-slot name="description">
            Ranked by urgency and affordability · Available cash: ZMW {{ number_format($availableCash, 2) }} · Expected income: ZMW {{ number_format($monthlyExpectedIncome, 2) }}/{{ $periodSuffix }}
        </x-slot>

        @if($debts->isEmpty())
            <div class="py-4 text-center text-gray-500 dark:text-gray-400">
                No active debts recorded.
            </div>
        @else
            <div class="mb-3 text-sm text-gray-600 dark:text-gray-300">
                Mandatory recurring commitments: ZMW {{ number_format($mandatoryMonthlyExpenses, 2) }}/{{ $periodSuffix }}
            </div>

            <div class="space-y-3">
                @foreach($debts as $debt)
                    @php
                        $progress = $debt['total_repayment_amount'] > 0
                            ? max(0, min(100, round((1 - ($debt['outstanding_balance'] / $debt['total_repayment_amount'])) * 100)))
                            : 0;

                        $priorityColor = match ($debt['priority_status']) {
                            'Critical' => 'text-danger-600',
                            'High Priority' => 'text-warning-600',
                            'Manageable' => 'text-info-600',
                            default => 'text-gray-600',
                        };

                        $affordabilityColor = match ($debt['affordability_status']) {
                            'Affordable' => 'text-success-600',
                            'Borderline' => 'text-warning-600',
                            'High Risk' => 'text-danger-500',
                            default => 'text-danger-700',
                        };
                    @endphp
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ $debt['creditor_name'] }}</span>
                                <span class="ml-2 text-xs text-gray-500 uppercase">{{ str_replace('_', ' ', $debt['type']) }}</span>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-danger-600">ZMW {{ number_format($debt['outstanding_balance'], 2) }}</div>
                                <div class="text-xs text-gray-500">ZMW {{ number_format($debt['monthly_obligation'], 2) }}/{{ $periodSuffix }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs mb-3">
                            <div><span class="text-gray-500">Priority:</span> <span class="font-semibold {{ $priorityColor }}">{{ $debt['priority_status'] }}</span></div>
                            <div><span class="text-gray-500">Affordability:</span> <span class="font-semibold {{ $affordabilityColor }}">{{ $debt['affordability_status'] }}</span></div>
                            <div><span class="text-gray-500">Interest payable:</span> ZMW {{ number_format($debt['interest_payable'], 2) }}</div>
                            <div><span class="text-gray-500">Income required:</span> {{ number_format($debt['income_percentage'], 1) }}%</div>
                            <div><span class="text-gray-500">Cash required:</span> {{ number_format($debt['cash_percentage'], 1) }}%</div>
                            <div><span class="text-gray-500">Disposable after payment:</span> ZMW {{ number_format($debt['disposable_after_payment'], 2) }}</div>
                        </div>

                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-primary-500 h-2 rounded-full transition-all duration-300"
                                 style="width: {{ $progress }}%"></div>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500 mt-1">
                            <span>{{ $progress }}% repaid</span>
                            @if($debt['due_date'])
                                <span>Due: {{ \Illuminate\Support\Carbon::parse($debt['due_date'])->format('d M Y') }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            @if(! empty($allocation) && $latestIncomeAmount)
                <div class="mt-4 rounded-lg border border-success-200 dark:border-success-700 bg-success-50 dark:bg-success-950/30 p-4">
                    <h4 class="font-semibold text-success-800 dark:text-success-300 mb-2">
                        Allocation recommendation for latest income (ZMW {{ number_format($latestIncomeAmount, 2) }})
                    </h4>
                    <ol class="list-decimal list-inside space-y-1 text-sm text-success-900 dark:text-success-200">
                        @foreach($allocation as $item)
                            <li>{{ $item['name'] }} - ZMW {{ number_format($item['amount'], 2) }} <span class="text-success-700 dark:text-success-300">({{ $item['reason'] }})</span></li>
                        @endforeach
                    </ol>
                </div>
            @endif
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
