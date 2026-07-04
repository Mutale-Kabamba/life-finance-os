<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Widgets\WealthAssetsTableWidget;
use App\Filament\Widgets\WealthInvestmentsTableWidget;
use App\Filament\Widgets\WealthOverviewWidget;
use App\Models\Asset;
use App\Models\Investment;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\HtmlString;

class WealthDashboard extends BaseDashboard
{
    protected static string $routePath = '/wealth-dashboard';
    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
    protected static ?string $navigationGroup = 'Wealth Building';
    protected static ?string $navigationLabel = 'Wealth Dashboard';
    protected static ?int $navigationSort = 0;

    public function getHeading(): HtmlString
    {
        $name = trim((string) auth()->user()?->name);
        $firstName = $name !== '' ? explode(' ', $name)[0] : 'there';

        $hour = now()->hour;
        $greeting = match (true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default => 'Good evening',
        };

        $nameColor = match (true) {
            $hour < 12 => '#f59e0b',
            $hour < 17 => '#0ea5e9',
            default => '#a78bfa',
        };

        $safeName = e($firstName);

        return new HtmlString($greeting . ', <span style="color: ' . $nameColor . ';">' . $safeName . '</span>!');
    }

    public function getWidgets(): array
    {
        return [
            WealthOverviewWidget::class,
            WealthAssetsTableWidget::class,
            WealthInvestmentsTableWidget::class,
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return (bool) (
            $user->profile?->hasFeature('has_investments') ||
            Asset::query()->where('user_id', $user->id)->exists() ||
            Investment::query()->where('user_id', $user->id)->exists()
        );
    }
}
