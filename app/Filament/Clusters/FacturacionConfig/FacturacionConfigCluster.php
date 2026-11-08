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
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Configuración Fiscal';

    protected static ?string $title = 'Configuración Fiscal';

    protected static ?string $clusterBreadcrumb = 'Fiscal';

    protected static string|UnitEnum|null $navigationGroup = 'Caja & Facturación';

    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'configuracion-fiscal';

    protected string $view = 'filament.clusters.facturacion.inicio';

    public function getSubtitulo(): string
    {
        return 'Configuración de series fiscales, autorizaciones DGI, folios y pasarelas de pago.';
    }

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
