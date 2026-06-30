<x-filament-widgets::widget class="fi-wi-debt-overview">
    <x-filament::section>
        <x-slot name="heading">Debt Management</x-slot>
        <x-slot name="description">Active debts · Total: ZMW {{ number_format($totalDebt, 2) }} · Monthly: ZMW {{ number_format($totalMonthly, 2) }}</x-slot>

        @if($debts->isEmpty())
            <div class="py-4 text-center text-gray-500 dark:text-gray-400">
                No active debts recorded.
            </div>
        @else
            <div class="space-y-3">
                @foreach($debts as $debt)
                    @php
                        $progress = $debt->original_amount > 0
                            ? round((($debt->original_amount - $debt->outstanding_balance) / $debt->original_amount) * 100)
                            : 0;
                    @endphp
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ $debt->creditor_name }}</span>
                                <span class="ml-2 text-xs text-gray-500 uppercase">{{ str_replace('_', ' ', $debt->type) }}</span>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-danger-600">ZMW {{ number_format($debt->outstanding_balance, 2) }}</div>
                                <div class="text-xs text-gray-500">ZMW {{ number_format($debt->monthly_installment, 2) }}/mo</div>
                            </div>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-primary-500 h-2 rounded-full transition-all duration-300"
                                 style="width: {{ $progress }}%"></div>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500 mt-1">
                            <span>{{ $progress }}% paid</span>
                            @if($debt->debt_free_projection)
                                <span>Debt-free: {{ $debt->debt_free_projection }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
