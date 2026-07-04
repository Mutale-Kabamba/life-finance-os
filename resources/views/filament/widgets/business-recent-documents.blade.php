<x-filament-widgets::widget class="fi-wi-business-recent-documents">
    @php($money = fn ($v) => 'ZMW ' . number_format((float) ($v ?? 0), 2))

    <x-filament::section>
        <x-slot name="heading">Recent Documents</x-slot>
        <x-slot name="description">{{ $description }}</x-slot>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                <tr class="border-b text-left">
                    <th class="py-2">Number</th>
                    <th>Customer</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th class="text-right">Total</th>
                    <th class="text-right">Balance</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($rows as $row)
                    <tr class="border-b border-gray-100 dark:border-white/5">
                        <td class="py-2">{{ $row['number'] }}</td>
                        <td>{{ $row['customer'] }}</td>
                        <td class="capitalize">{{ str_replace('_', ' ', $row['type']) }}</td>
                        <td class="capitalize">{{ str_replace('_', ' ', $row['status']) }}</td>
                        <td>{{ $row['issue_date'] }}</td>
                        <td class="text-right">{{ $money($row['total_amount']) }}</td>
                        <td class="text-right">{{ $money($row['balance_due']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-4 text-center text-gray-500">No business documents found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
