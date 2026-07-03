<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class BusinessDocuments extends Cluster
{
    protected static ?string $navigationLabel = 'Documents';

    protected static ?string $navigationGroup = 'Business Finance';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 3;
}
