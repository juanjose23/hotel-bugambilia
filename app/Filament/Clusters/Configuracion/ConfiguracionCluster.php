<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Configuracion;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class ConfiguracionCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Configuración General';

    protected static ?string $clusterBreadcrumb = 'Configuración';

    protected static string|UnitEnum|null $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 1;

    public static function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::End;
    }
}
