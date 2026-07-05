<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Pages\Page;

class BusinessDocumentsTab extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Business Finance';
    protected static ?string $navigationLabel = 'Business Documents';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.navigation-tab';

    public function mount(): void
    {
        $this->redirect(InvoiceResource::getUrl('index'));
    }
}
