<?php

declare(strict_types=1);

namespace App\Filament\Shared\RelationManagers;

final class ActivosDelServicioRelationManager extends InventarioFijoRelationManager
{
    protected static string $relationship = 'asignacionesActivos';

    protected static ?string $title = 'Flota del Servicio – Activos y Accesorios';

    protected static ?string $label = 'Activo Fijo';

    protected static ?string $pluralLabel = 'Flota del Servicio';
}
