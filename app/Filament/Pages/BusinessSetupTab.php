<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Resources\BusinessResource;
use Filament\Pages\Page;

class BusinessSetupTab extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Business Finance';
    protected static ?string $navigationLabel = 'Business Setup';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.navigation-tab';

    public function mount(): void
    {
        $this->redirect(BusinessResource::getUrl('index'));
    }
}
