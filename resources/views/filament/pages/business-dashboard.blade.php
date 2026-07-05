<x-filament-panels::page>
    @php($money = fn ($v) => 'ZMW ' . number_format((float) ($v ?? 0), 2))

    <x-filament::section>
        <form wire:submit="refreshStats">
            {{ $this->form }}
        </form>
    </x-filament::section>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <x-filament::section>
            <div class="text-sm text-gray-500">Customers</div>
            <div class="mt-1 text-2xl font-semibold">{{ $stats['customers'] ?? 0 }}</div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-sm text-gray-500">Suppliers</div>
            <div class="mt-1 text-2xl font-semibold">{{ $stats['suppliers'] ?? 0 }}</div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-sm text-gray-500">Products</div>
            <div class="mt-1 text-2xl font-semibold">{{ $stats['products'] ?? 0 }}</div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-sm text-gray-500">Services</div>
            <div class="mt-1 text-2xl font-semibold">{{ $stats['services'] ?? 0 }}</div>
        </x-filament::section>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <x-filament::section>
            <div class="text-sm text-gray-500">Inventory Value</div>
            <div class="mt-1 text-2xl font-semibold">{{ $money($stats['inventory_value'] ?? 0) }}</div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-sm text-gray-500">Sales (period)</div>
            <div class="mt-1 text-2xl font-semibold">{{ $money($stats['sales_in_period'] ?? 0) }}</div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-sm text-gray-500">Outstanding Receivables</div>
            <div class="mt-1 text-2xl font-semibold">{{ $money($stats['outstanding_receivables'] ?? 0) }}</div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-sm text-gray-500">Low Stock Items</div>
            <div class="mt-1 text-2xl font-semibold">{{ $stats['low_stock'] ?? 0 }}</div>
        </x-filament::section>
    </div>

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
        <x-filament::section heading="Recent Documents">
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
                    @forelse ($recentInvoices as $row)
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

        <x-filament::section heading="Low Stock Alerts">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                    <tr class="border-b text-left">
                        <th class="py-2">Product</th>
                        <th>SKU</th>
                        <th class="text-right">On Hand</th>
                        <th class="text-right">Reorder</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($lowStockItems as $item)
                        <tr class="border-b border-gray-100 dark:border-white/5">
                            <td class="py-2">{{ $item['name'] }}</td>
                            <td>{{ $item['sku'] ?: '-' }}</td>
                            <td class="text-right">{{ $item['qty'] }} {{ $item['unit'] }}</td>
                            <td class="text-right">{{ $item['reorder'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center text-gray-500">No low stock items.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
