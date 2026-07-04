<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Widgets\BusinessLowStockWidget;
use App\Filament\Widgets\BusinessOverviewWidget;
use App\Filament\Widgets\BusinessRecentDocumentsWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\HtmlString;

class BusinessDashboard extends BaseDashboard
{
    protected static string $routePath = '/business-dashboard';
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationGroup = 'Business Finance';
    protected static ?string $navigationLabel = 'Business Dashboard';
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
            BusinessOverviewWidget::class,
            BusinessRecentDocumentsWidget::class,
            BusinessLowStockWidget::class,
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) auth()->user()?->profile?->hasFeature('has_business');
    }
}
