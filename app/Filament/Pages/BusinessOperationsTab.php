<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Resources\StockMovementResource;
use Filament\Pages\Page;

class BusinessOperationsTab extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationGroup = 'Business Finance';
    protected static ?string $navigationLabel = 'Business Operations';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.navigation-tab';

    public function mount(): void
    {
        $this->redirect(StockMovementResource::getUrl('index'));
    }
}
