<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Auditoria;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class AuditoriaCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Auditoría & Trazabilidad';

    protected static ?string $clusterBreadcrumb = 'Auditoría';

    protected static string|UnitEnum|null $navigationGroup = 'Seguridad';

    protected static ?int $navigationSort = 50;

    public static function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::End;
    }
}
