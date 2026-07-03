<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\HtmlString;

class Dashboard extends BaseDashboard
{
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
}
