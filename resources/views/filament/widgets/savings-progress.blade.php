<x-filament-widgets::widget class="fi-wi-savings-progress">
    <x-filament::section>
        <x-slot name="heading">Savings Goals</x-slot>
        <x-slot name="description">Track your progress towards financial targets</x-slot>

        @if($goals->isEmpty())
            <div class="py-4 text-center text-gray-500 dark:text-gray-400">
                No active savings goals. <a href="{{ \App\Filament\Resources\SavingsGoalResource::getUrl('create') }}" class="text-primary-600 hover:underline">Create one now.</a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($goals as $goal)
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-semibold text-gray-900 dark:text-white text-sm">{{ $goal['name'] }}</span>
                            <span class="text-xs text-gray-500 uppercase px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-800">
                                {{ str_replace('_', ' ', $goal['category']) }}
                            </span>
                        </div>
                        <div class="text-xl font-bold text-success-600 mb-2">
                            ZMW {{ number_format($goal['current'], 2) }}
                            <span class="text-sm font-normal text-gray-500">/ ZMW {{ number_format($goal['target'], 2) }}</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                            <div class="bg-success-500 h-3 rounded-full transition-all duration-300"
                                 style="width: {{ min(100, $goal['progress']) }}%"></div>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500 mt-1">
                            <span>{{ number_format($goal['progress'], 1) }}% saved</span>
                            @if($goal['estimated_date'])
                                <span>Target: {{ $goal['estimated_date'] }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
