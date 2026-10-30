<?php

declare(strict_types=1);

namespace App\Filament\Clusters\FacturacionConfig;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class FacturacionConfigCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static ?string $navigationLabel = 'Facturación y caja';

    protected static ?string $title = 'Facturación y caja';

    protected static ?string $clusterBreadcrumb = 'Facturación y caja';

    protected static string|UnitEnum|null $navigationGroup = 'Caja & Facturación';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'facturacion';

    protected string $view = 'filament.clusters.facturacion.inicio';

    public function mount(): void {}

    public static function canAccess(): bool
    {
        return self::canAccessClusteredComponents();
    }

    public static function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Start;
    }

    /** @return array<NavigationGroup> */
    public function getCachedSubNavigation(): array
    {
        return [];
    }

    /** @return array<NavigationGroup> */
    public function gruposDeAcceso(): array
    {
        return parent::getCachedSubNavigation();
    }
}
