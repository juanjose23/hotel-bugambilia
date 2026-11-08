<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Tesoreria;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class TesoreriaCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static ?string $navigationLabel = 'Tesorería y Bancos';

    protected static ?string $title = 'Tesorería y Bancos';

    protected static ?string $clusterBreadcrumb = 'Tesorería';

    protected static string|UnitEnum|null $navigationGroup = 'Caja & Facturación';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'tesoreria';

    protected string $view = 'filament.clusters.facturacion.inicio';

    public function getSubtitulo(): string
    {
        return 'Control de fondos bancarios, movimientos de tesorería, caja chica y conciliaciones.';
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
