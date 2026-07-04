<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Pages\Page;

class BusinessReportsTab extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationGroup = 'Business Finance';
    protected static ?string $navigationLabel = 'Business Reports';
    protected static ?int $navigationSort = 4;
    protected static string $view = 'filament.pages.navigation-tab';

    public function mount(): void
    {
        $this->redirect(FinancialReports::getUrl());
    }
}
