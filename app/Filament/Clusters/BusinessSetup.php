<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class BusinessSetup extends Cluster
{
    protected static ?string $navigationLabel = 'Setup';

    protected static ?string $navigationGroup = 'Business Finance';

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 1;
}
