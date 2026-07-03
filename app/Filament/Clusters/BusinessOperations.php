<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class BusinessOperations extends Cluster
{
    protected static ?string $navigationLabel = 'Operations';

    protected static ?string $navigationGroup = 'Business Finance';

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?int $navigationSort = 2;
}
