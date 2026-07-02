@php
    /** @var \App\Models\Budget $record */
    $items = $record->items->sortBy('name')->values();
    $remaining = (float) $record->variance;
    $used = (float) $record->utilization_percent;
@endphp

<div class="space-y-6">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900">
            <p class="text-xs uppercase tracking-wide text-gray-500">Budgeted</p>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">ZMW {{ number_format((float) $record->total_budgeted, 2) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900">
            <p class="text-xs uppercase tracking-wide text-gray-500">Spent</p>
            <p class="mt-1 text-2xl font-bold {{ (float) $record->total_actual > (float) $record->total_budgeted ? 'text-danger-600' : 'text-success-600' }}">
                ZMW {{ number_format((float) $record->total_actual, 2) }}
            </p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900">
            <p class="text-xs uppercase tracking-wide text-gray-500">Remaining</p>
            <p class="mt-1 text-2xl font-bold {{ $remaining < 0 ? 'text-danger-600' : 'text-gray-900 dark:text-white' }}">
                {{ $remaining < 0 ? '-' : '' }}ZMW {{ number_format(abs($remaining), 2) }}
            </p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900">
            <p class="text-xs uppercase tracking-wide text-gray-500">Used</p>
            <p class="mt-1 text-2xl font-bold {{ $used > 100 ? 'text-danger-600' : ($used >= 80 ? 'text-warning-600' : 'text-primary-600') }}">
                {{ number_format($used, 0) }}%
            </p>
        </div>
    </div>

    @if ($items->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
            No shopping items yet. Open edit to add budget items.
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($items as $item)
                @php
                    $itemRemaining = (float) $item->budgeted_amount - (float) $item->actual_amount;
                    $isOver = $itemRemaining < 0;
                @endphp
                <div class="rounded-2xl border p-4 {{ $isOver ? 'border-danger-300 dark:border-danger-800' : ($item->is_purchased ? 'border-success-300 dark:border-success-800' : 'border-primary-300 dark:border-primary-800') }}">
                    <div class="flex items-start justify-between gap-2">
                        <h4 class="font-semibold text-gray-900 dark:text-white">{{ $item->name }}</h4>
                        <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $item->is_purchased ? 'bg-success-100 text-success-700 dark:bg-success-900/40 dark:text-success-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                            {{ $item->is_purchased ? 'Bought' : 'Planned' }}
                        </span>
                    </div>

                    <p class="mt-1 text-xs text-gray-500">{{ $item->category?->name ?? 'Uncategorized' }}</p>

                    <div class="mt-3 space-y-1 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Budgeted</span>
                            <span class="font-medium text-gray-900 dark:text-white">ZMW {{ number_format((float) $item->budgeted_amount, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Actual</span>
                            <span class="font-medium {{ $isOver ? 'text-danger-600' : 'text-success-600' }}">ZMW {{ number_format((float) $item->actual_amount, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">{{ $isOver ? 'Over by' : 'Left' }}</span>
                            <span class="font-medium {{ $isOver ? 'text-danger-600' : 'text-gray-900 dark:text-white' }}">ZMW {{ number_format(abs($itemRemaining), 2) }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
