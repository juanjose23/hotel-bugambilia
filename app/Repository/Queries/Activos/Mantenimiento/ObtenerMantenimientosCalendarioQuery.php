<?php

declare(strict_types=1);

namespace App\Repository\Queries\Activos\Mantenimiento;

use App\Filament\Shared\Concerns\InyectaDesdeContenedor;
use App\Repository\Models\Activos\ActivoMantenimiento;
use Illuminate\Database\Eloquent\Collection;

final class ObtenerMantenimientosCalendarioQuery
{
    use InyectaDesdeContenedor;

    /** @return Collection<int, ActivoMantenimiento> */
    public function ejecutar(int $mes, int $anio): Collection
    {
        return ActivoMantenimiento::query()
            ->with(['activo'])
            ->whereMonth('fecha_programada', $mes)
            ->whereYear('fecha_programada', $anio)
            ->get();
    }
}
