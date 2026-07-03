<x-filament-widgets::widget class="fi-wi-business-overview">
    <x-filament::section>
        <x-slot name="heading">Business Overview</x-slot>
        <x-slot name="description">Core business KPIs for the current month</x-slot>

        @if (! $hasBusiness)
            <div class="py-4 text-center text-gray-500 dark:text-gray-400">
                No business profile found. Create a business first to view dashboard analytics.
            </div>
        @else
            <style>
                .kpi-grid {
                    display: grid;
                    grid-template-columns: repeat(4, minmax(0, 1fr));
                    gap: 1rem;
                }

                .kpi-card {
                    border-radius: 1rem;
                    padding: 1.1rem 1.2rem;
                    border: 1px solid transparent;
                    box-shadow: 0 14px 35px -24px rgba(15, 23, 42, 0.5);
                    transition: transform 180ms ease;
                }

                .kpi-card:hover {
                    transform: translateY(-2px);
                }

                .kpi-title {
                    font-size: 0.9rem;
                    font-weight: 600;
                    color: rgb(75 85 99);
                }

                .kpi-value {
                    margin-top: 0.45rem;
                    font-size: 2rem;
                    font-weight: 800;
                    letter-spacing: -0.02em;
                    line-height: 1.05;
                    color: rgb(15 23 42);
                }

                .kpi-note {
                    margin-top: 0.6rem;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 0.5rem;
                    font-size: 0.95rem;
                    font-weight: 500;
                    color: rgb(30 41 59);
                }

                .kpi-note svg {
                    width: 1.1rem;
                    height: 1.1rem;
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
