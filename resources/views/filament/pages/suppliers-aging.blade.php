<x-filament-panels::page>
    <x-filament::section>
        <form wire:submit="generate">
            {{ $this->form }}
        </form>
    </x-filament::section>

    @php($money = fn ($v) => 'ZMW ' . number_format((float) ($v ?? 0), 2))

    @if ($report)
        <x-filament::section heading="Accounts Payable Aging">
            @if (empty($report['suppliers']))
                <p class="text-sm text-gray-500">No outstanding supplier balances.</p>
            @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left border-b">
                            <th class="py-1">Supplier</th>
                            <th class="text-right">Current</th>
                            <th class="text-right">1–30</th>
                            <th class="text-right">31–60</th>
                            <th class="text-right">61–90</th>
                            <th class="text-right">90+</th>
                            <th class="text-right">Total due</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($report['suppliers'] as $row)
                            <tr class="border-b border-gray-100 dark:border-white/5">
                                <td class="py-1">{{ $row['supplier'] }}</td>
                                <td class="text-right">{{ $money($row['current']) }}</td>
                                <td class="text-right">{{ $money($row['days_1_30']) }}</td>
                                <td class="text-right">{{ $money($row['days_31_60']) }}</td>
                                <td class="text-right">{{ $money($row['days_61_90']) }}</td>
                                <td class="text-right">{{ $money($row['days_90_plus']) }}</td>
                                <td class="text-right font-medium">{{ $money($row['total_due']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="font-semibold border-t">
                            <td class="py-1">Totals</td>
                            <td class="text-right">{{ $money($report['totals']['current']) }}</td>
                            <td class="text-right">{{ $money($report['totals']['days_1_30']) }}</td>
                            <td class="text-right">{{ $money($report['totals']['days_31_60']) }}</td>
                            <td class="text-right">{{ $money($report['totals']['days_61_90']) }}</td>
                            <td class="text-right">{{ $money($report['totals']['days_90_plus']) }}</td>
                            <td class="text-right">{{ $money($report['totals']['total_due']) }}</td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        </x-filament::section>
    @endif
</x-filament-panels::page>
