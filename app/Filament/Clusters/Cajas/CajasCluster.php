<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Cajas;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class CajasCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static ?string $navigationLabel = 'Cajas y Turnos';

    protected static ?string $title = 'Cajas y Turnos';

    protected static ?string $clusterBreadcrumb = 'Cajas';

    protected static string|UnitEnum|null $navigationGroup = 'Caja & Facturación';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'cajas';

    protected string $view = 'filament.clusters.facturacion.inicio';

    public function getSubtitulo(): string
    {
        return 'Gestiona tus turnos activos, arqueos, cierres de caja y movimientos diarios.';
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
