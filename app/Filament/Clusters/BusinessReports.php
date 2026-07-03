<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class BusinessReports extends Cluster
{
    protected static ?string $navigationLabel = 'Reports';

    protected static ?string $navigationGroup = 'Business Finance';

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?int $navigationSort = 4;
}
