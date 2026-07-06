<x-filament-widgets::widget class="fi-wi-wealth-overview">
    <x-filament::section>
        <x-slot name="heading">This is your Investment &amp; Assets Overview</x-slot>
        <x-slot name="description">{{ $description }}</x-slot>

        <style>
            .kpi-toolbar {
                display: flex;
                justify-content: flex-end;
                gap: 0.5rem;
                margin-bottom: 0.75rem;
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

            .kpi-theme-indigo {
                background: linear-gradient(145deg, #e5e0fa, #d3cbf2);
                border-color: #b8ace8;
            }

            .kpi-theme-rose {
                background: linear-gradient(145deg, #f9dde3, #f1c2cd);
                border-color: #e8a3b5;
            }

            .kpi-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 1.5rem;
                height: 1.5rem;
                border-radius: 0.45rem;
                background: rgba(255, 255, 255, 0.45);
                color: rgb(30 41 59);
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

            @media (max-width: 1280px) {
                .kpi-grid {
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                }
            }

            @media (max-width: 1024px) {
                .kpi-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            @media (max-width: 640px) {
                .kpi-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>

        <div class="kpi-toolbar">
            @foreach ($horizonFilters as $filter)
                <button type="button" wire:click="setHorizon('{{ $filter['value'] }}')" class="kpi-filter-chip {{ $activeHorizon === $filter['value'] ? 'is-active' : '' }}">
                    {{ $filter['label'] }}
                </button>
            @endforeach
        </div>

        @if (! empty($investmentTypeHighlights))
            <div style="margin-bottom: .75rem; font-size: .78rem; color: rgb(75 85 99);">
                <span style="font-weight: 700;">Investment mix:</span>
                @foreach ($investmentTypeHighlights as $index => $mix)
                    <span>
                        {{ $mix['label'] }} ({{ $mix['count'] }}, ZMW {{ number_format($mix['value'], 2) }})@if($index < count($investmentTypeHighlights) - 1) · @endif
                    </span>
                @endforeach
                @if (($upcomingMaturities ?? 0) > 0)
                    <span> · {{ $upcomingMaturities }} maturity event(s) due within 120 days</span>
                @endif
            </div>
        @endif

        <div class="kpi-grid">
            @foreach ($cards as $card)
                <article class="kpi-card kpi-theme-{{ $card['theme'] }}">
                    <div class="kpi-title">{{ $card['title'] }}</div>
                    <div class="kpi-value">{{ $card['value'] }}</div>
                    <div class="kpi-note">
                        <span>{{ $card['note'] }}</span>
                        <span class="kpi-icon">
                            <x-filament::icon :icon="$card['icon']" class="h-5 w-5" />
                        </span>
                    </div>
                </article>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
