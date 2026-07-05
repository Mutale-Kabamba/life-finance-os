<x-filament-widgets::widget class="fi-wi-business-low-stock">
    <x-filament::section>
        <x-slot name="heading">Low Stock Alerts</x-slot>
        <x-slot name="description">Products that have reached reorder levels</x-slot>

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
                @forelse ($items as $item)
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
</x-filament-widgets::widget>
