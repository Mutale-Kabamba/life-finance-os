<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Asset;
use App\Models\Investment;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

class WealthOverviewWidget extends Widget
{
    protected static string $view = 'filament.widgets.wealth-overview';
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    public string $activeHorizon = 'all';

    public function mount(): void
    {
        $savedHorizon = (string) session('dashboard_filters.wealth_horizon', 'all');
        $allowedHorizons = ['all', 'short', 'medium', 'long'];

        $this->activeHorizon = in_array($savedHorizon, $allowedHorizons, true) ? $savedHorizon : 'all';
    }

    public function setHorizon(string $horizon): void
    {
        $allowedHorizons = ['all', 'short', 'medium', 'long'];

        if (! in_array($horizon, $allowedHorizons, true)) {
            return;
        }

        $this->activeHorizon = $horizon;
        session(['dashboard_filters.wealth_horizon' => $horizon]);
        $this->dispatch('wealth-horizon-changed', horizon: $horizon);
    }

    public function getViewData(): array
    {
        $userId = (int) auth()->id();

        $horizon = $this->activeHorizon;
        $allowedHorizons = ['all', 'short', 'medium', 'long'];

        if (! in_array($horizon, $allowedHorizons, true)) {
            $horizon = 'all';
            $this->activeHorizon = 'all';
        }

        $horizonLabel = match ($horizon) {
            'all' => 'All horizons',
            'medium' => 'Medium-term (3-10 years)',
            'long' => 'Long-term (10+ years)',
            default => 'Short-term (1 day-3 years)',
        };

        $assetQuery = $this->applyAssetHorizonFilter(Asset::query()->where('user_id', $userId), $horizon);
        $investmentQuery = $this->applyInvestmentHorizonFilter(Investment::query()->where('user_id', $userId), $horizon);

        $assetCurrent = (float) (clone $assetQuery)->sum('current_value');
        $assetPurchase = (float) (clone $assetQuery)->sum('purchase_price');
        $insuredAssets = (int) (clone $assetQuery)->where('is_insured', true)->count();
        $totalAssets = (int) (clone $assetQuery)->count();

        $investmentCurrent = (float) (clone $investmentQuery)->sum('current_value');
        $investmentInitial = (float) (clone $investmentQuery)->sum('initial_amount');
        $activeInvestments = (int) (clone $investmentQuery)->where('status', 'active')->count();
        $totalInvestments = (int) (clone $investmentQuery)->count();

        $portfolioValue = $assetCurrent + $investmentCurrent;
        $portfolioCost = $assetPurchase + $investmentInitial;
        $portfolioGain = $portfolioValue - $portfolioCost;

        return [
            'description' => $this->getHorizonDescription($horizon),
            'activeHorizon' => $horizon,
            'horizonLabel' => $horizonLabel,
            'horizonFilters' => [
                ['value' => 'all', 'label' => 'All'],
                ['value' => 'short', 'label' => 'Short-term'],
                ['value' => 'medium', 'label' => 'Medium-term'],
                ['value' => 'long', 'label' => 'Long-term'],
            ],
            'cards' => [
                [
                    'title' => 'Total Portfolio Value',
                    'value' => 'ZMW ' . number_format($portfolioValue, 2),
                    'note'  => 'Assets + investments for selected horizon',
                    'icon'  => 'heroicon-m-scale',
                    'theme' => 'mint',
                ],
                [
                    'title' => 'Total Asset Value',
                    'value' => 'ZMW ' . number_format($assetCurrent, 2),
                    'note'  => number_format($totalAssets) . ' assets in horizon',
                    'icon'  => 'heroicon-m-home-modern',
                    'theme' => 'blue',
                ],
                [
                    'title' => 'Total Investment Value',
                    'value' => 'ZMW ' . number_format($investmentCurrent, 2),
                    'note'  => number_format($totalInvestments) . ' investment positions in horizon',
                    'icon'  => 'heroicon-m-chart-pie',
                    'theme' => 'indigo',
                ],
                [
                    'title' => 'Portfolio Gain / Loss',
                    'value' => 'ZMW ' . number_format($portfolioGain, 2),
                    'note'  => $portfolioGain >= 0 ? 'Overall gain vs invested cost' : 'Overall drawdown vs invested cost',
                    'icon'  => $portfolioGain >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down',
                    'theme' => $portfolioGain >= 0 ? 'cyan' : 'rose',
                ],
                [
                    'title' => 'Active Investments',
                    'value' => number_format($activeInvestments),
                    'note'  => 'Active holdings in selected horizon',
                    'icon'  => 'heroicon-m-sparkles',
                    'theme' => 'amber',
                ],
                [
                    'title' => 'Insured Assets',
                    'value' => number_format($insuredAssets),
                    'note'  => 'Assets with active insurance in horizon',
                    'icon'  => 'heroicon-m-shield-check',
                    'theme' => 'mint',
                ],
            ],
        ];
    }

    protected function applyAssetHorizonFilter(Builder $query, string $horizon): Builder
    {
        return match ($horizon) {
            'all' => $query,
            'medium' => $query
                ->whereNotNull('purchase_date')
                ->whereRaw('TIMESTAMPDIFF(YEAR, purchase_date, CURDATE()) > 3')
                ->whereRaw('TIMESTAMPDIFF(YEAR, purchase_date, CURDATE()) <= 10'),
            'long' => $query
                ->whereNotNull('purchase_date')
                ->whereRaw('TIMESTAMPDIFF(YEAR, purchase_date, CURDATE()) > 10'),
            default => $query
                ->whereNotNull('purchase_date')
                ->whereRaw('TIMESTAMPDIFF(YEAR, purchase_date, CURDATE()) <= 3'),
        };
    }

    protected function applyInvestmentHorizonFilter(Builder $query, string $horizon): Builder
    {
        $termYearsSql = "TIMESTAMPDIFF(YEAR, start_date, COALESCE(maturity_date, CURDATE()))";

        return match ($horizon) {
            'all' => $query,
            'medium' => $query
                ->whereNotNull('start_date')
                ->whereRaw($termYearsSql . ' > 3')
                ->whereRaw($termYearsSql . ' <= 10'),
            'long' => $query
                ->whereNotNull('start_date')
                ->whereRaw($termYearsSql . ' > 10'),
            default => $query
                ->whereNotNull('start_date')
                ->whereRaw($termYearsSql . ' <= 3'),
        };
    }

    protected function getHorizonDescription(string $horizon): string
    {
        return match ($horizon) {
            'all' => 'Showing all wealth positions | All horizons',
            'medium' => 'Showing medium-term positions | 3 to 10 years',
            'long' => 'Showing long-term positions | 10+ years',
            default => 'Showing short-term positions | 1 day to 3 years',
        };
    }
}
