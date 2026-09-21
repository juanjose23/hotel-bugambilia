<?php

declare(strict_types=1);

namespace App\Filament\Clusters\FacturacionConfig;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class FacturacionConfigCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static ?string $navigationLabel = 'Parámetros Fiscales & Pagos';

    protected static ?string $clusterBreadcrumb = 'Fiscal & Pagos';

    protected static string|UnitEnum|null $navigationGroup = 'Caja & Facturación';

    protected static ?int $navigationSort = 90;

    public static function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::End;
    }
}
