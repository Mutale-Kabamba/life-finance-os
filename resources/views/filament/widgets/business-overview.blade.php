<x-filament-widgets::widget class="fi-wi-business-overview">
    <x-filament::section>
        <x-slot name="heading">This is your Business Overview</x-slot>
        <x-slot name="description">{{ $description }}</x-slot>

        @if (! $hasBusiness)
            <div class="py-4 text-center text-gray-500 dark:text-gray-400">
                No business profile found. Create a business first to view dashboard analytics.
            </div>
        @else
            <style>
                .kpi-toolbar {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    gap: 0.5rem;
                    margin-bottom: 0.75rem;
                    flex-wrap: wrap;
                }

                .kpi-toolbar-left,
                .kpi-toolbar-right {
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                    flex-wrap: wrap;
                }

                .kpi-filter-chip {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    padding: 0.35rem 0.7rem;
                    border-radius: 9999px;
                    border: 1px solid rgb(203 213 225);
                    background: rgb(248 250 252);
                    color: rgb(51 65 85);
                    font-size: 0.72rem;
                    font-weight: 700;
                    letter-spacing: 0.02em;
                    text-decoration: none;
                    transition: all 160ms ease;
                }

                .kpi-filter-chip:hover {
                    border-color: rgb(148 163 184);
                    color: rgb(15 23 42);
                }

                .kpi-filter-chip.is-active {
                    border-color: rgb(14 116 144);
                    background: rgb(224 242 254);
                    color: rgb(8 47 73);
                }

                .kpi-date-input {
                    height: 2rem;
                    border-radius: 0.5rem;
                    border: 1px solid rgb(203 213 225);
                    background: rgb(255 255 255);
                    color: rgb(30 41 59);
                    padding: 0.2rem 0.55rem;
                    font-size: 0.75rem;
                    font-weight: 600;
                }

                .kpi-date-btn {
                    height: 2rem;
                    padding: 0 0.6rem;
                    border-radius: 0.5rem;
                    border: 1px solid rgb(148 163 184);
                    background: rgb(241 245 249);
                    color: rgb(15 23 42);
                    font-size: 0.72rem;
                    font-weight: 700;
                }

                .kpi-date-btn.is-primary {
                    border-color: rgb(14 116 144);
                    background: rgb(8 145 178);
                    color: rgb(255 255 255);
                }

                .kpi-date-btn.is-active {
                    border-color: rgb(14 116 144);
                    background: rgb(224 242 254);
                    color: rgb(8 47 73);
                }

                .kpi-grid {
                    display: grid;
                    grid-template-columns: repeat(4, minmax(0, 1fr));
                    gap: 0.72rem;
                }

                .kpi-card {
                    border-radius: 0.8rem;
                    padding: 0.78rem 0.86rem;
                    border: 1px solid transparent;
                    box-shadow: 0 9px 24px -18px rgba(15, 23, 42, 0.45);
                    transition: transform 180ms ease;
                }

                .kpi-card:hover {
                    transform: translateY(-2px);
                }

                .kpi-title {
                    font-size: 0.74rem;
                    font-weight: 700;
                    letter-spacing: 0.02em;
                    color: rgb(75 85 99);
                }

                .kpi-value {
                    margin-top: 0.28rem;
                    font-size: 1.34rem;
                    font-weight: 900;
                    letter-spacing: -0.02em;
                    line-height: 1.05;
                    color: rgb(15 23 42);
                }

                .kpi-note {
                    margin-top: 0.42rem;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 0.35rem;
                    font-size: 0.73rem;
                    font-weight: 600;
                    color: rgb(30 41 59);
                }

                .kpi-note svg {
                    width: 0.95rem;
                    height: 0.95rem;
                }

                .kpi-theme-mint {
                    background: linear-gradient(145deg, #d8f3e6, #b7e7d1);
                    border-color: #90d9bc;
                }

                .kpi-theme-blue {
                    background: linear-gradient(145deg, #dbe7f9, #c3d4ee);
                    border-color: #a4bde1;
                }

                .kpi-theme-amber {
                    background: linear-gradient(145deg, #f8ead6, #efdcbc);
                    border-color: #e6cfa3;
                }

                .kpi-theme-cyan {
                    background: linear-gradient(145deg, #d7f1f5, #b8e3ea);
                    border-color: #97d1da;
                }

                .kpi-theme-rose {
                    background: linear-gradient(145deg, #f7dfe3, #edc5cc);
                    border-color: #e2aab4;
                }

                .kpi-theme-indigo {
                    background: linear-gradient(145deg, #dfe5f8, #c8d2ef);
                    border-color: #adbee8;
                }

                .dark .kpi-card {
                    border-color: rgba(148, 163, 184, 0.32);
                    box-shadow: 0 12px 30px -22px rgba(2, 6, 23, 0.8);
                }

                .dark .kpi-filter-chip {
                    border-color: rgb(71 85 105);
                    background: rgb(30 41 59);
                    color: rgb(203 213 225);
                }

                .dark .kpi-filter-chip:hover {
                    border-color: rgb(148 163 184);
                    color: rgb(241 245 249);
                }

                .dark .kpi-filter-chip.is-active {
                    border-color: rgb(14 116 144);
                    background: rgb(22 78 99);
                    color: rgb(224 242 254);
                }

                .dark .kpi-date-input {
                    border-color: rgb(71 85 105);
                    background: rgb(30 41 59);
                    color: rgb(226 232 240);
                }

                .dark .kpi-date-btn {
                    border-color: rgb(71 85 105);
                    background: rgb(51 65 85);
                    color: rgb(226 232 240);
                }

                .dark .kpi-date-btn.is-primary {
                    border-color: rgb(14 116 144);
                    background: rgb(14 116 144);
                    color: rgb(255 255 255);
                }

                .dark .kpi-date-btn.is-active {
                    border-color: rgb(14 116 144);
                    background: rgb(22 78 99);
                    color: rgb(224 242 254);
                }

                .dark .kpi-theme-mint {
                    background: linear-gradient(145deg, rgba(16, 185, 129, 0.22), rgba(6, 95, 70, 0.35));
                }

                .dark .kpi-theme-blue {
                    background: linear-gradient(145deg, rgba(59, 130, 246, 0.2), rgba(30, 58, 138, 0.36));
                }

                .dark .kpi-theme-amber {
                    background: linear-gradient(145deg, rgba(245, 158, 11, 0.24), rgba(146, 64, 14, 0.38));
                }

                .dark .kpi-theme-cyan {
                    background: linear-gradient(145deg, rgba(6, 182, 212, 0.23), rgba(14, 116, 144, 0.38));
                }

                .dark .kpi-theme-rose {
                    background: linear-gradient(145deg, rgba(244, 63, 94, 0.24), rgba(136, 19, 55, 0.38));
                }

                .dark .kpi-theme-indigo {
                    background: linear-gradient(145deg, rgba(99, 102, 241, 0.23), rgba(49, 46, 129, 0.4));
                }

                .dark .kpi-title {
                    color: rgb(203 213 225);
                }

                .dark .kpi-value,
                .dark .kpi-note {
                    color: rgb(248 250 252);
                }

                @media (max-width: 1280px) {
                    .kpi-grid {
                        grid-template-columns: repeat(3, minmax(0, 1fr));
                    }
                }

                @media (max-width: 980px) {
                    .kpi-grid {
                        grid-template-columns: repeat(2, minmax(0, 1fr));
                    }
                }

                @media (max-width: 640px) {
                    .kpi-grid {
                        grid-template-columns: 1fr;
                    }

                    .kpi-value {
                        font-size: 1.6rem;
                    }

                    .kpi-card {
                        padding: 0.9rem 1rem;
                    }

                    .kpi-title {
                        font-size: 0.84rem;
                    }

                    .kpi-note {
                        font-size: 0.83rem;
                    }
                }
            </style>

            <div class="kpi-toolbar">
                <div class="kpi-toolbar-left">
                    @foreach ($periodFilters as $filter)
                        <button type="button" wire:click="setPeriod('{{ $filter['value'] }}')" class="kpi-filter-chip {{ ! $useCustomRange && $activePeriod === $filter['value'] ? 'is-active' : '' }}">
                            {{ $filter['label'] }}
                        </button>
                    @endforeach
                </div>

                <div class="kpi-toolbar-right">
                    <input type="date" wire:model.live="rangeStart" class="kpi-date-input" />
                    <span class="text-xs text-gray-400">to</span>
                    <input type="date" wire:model.live="rangeEnd" class="kpi-date-input" />
                    <button type="button" wire:click="applyDateRange" class="kpi-date-btn is-primary">Apply Range</button>
                    <button type="button" wire:click="clearDateRange" class="kpi-date-btn {{ $useCustomRange ? 'is-active' : '' }}">Use Period</button>
                </div>
            </div>

            <div class="kpi-grid">
                @foreach ($cards as $card)
                    <article class="kpi-card kpi-theme-{{ $card['theme'] }}">
                        <p class="kpi-title">{{ $card['title'] }}</p>
                        <p class="kpi-value">{{ $card['value'] }}</p>
                        <div class="kpi-note">
                            <span>{{ $card['note'] }}</span>
                            <x-filament::icon :icon="$card['icon']" />
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
