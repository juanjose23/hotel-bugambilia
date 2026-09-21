<?php

declare(strict_types=1);

namespace App\Interactors\Espacios;

use App\Enums\Activos\EstadoAsignacion;
use App\Interactors\Activos\Gestion\AsignarActivo;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Persistencia\Activos\ActivoAsignacionRepositorioInterface;
use Illuminate\Support\Facades\DB;

final class SincronizarActivosEspacio
{
    public function __construct(
        private readonly AsignarActivo $asignarActivo,
        private readonly ActivoAsignacionRepositorioInterface $asignacionRepositorio,
    ) {}

    /**
     * @param  array<int|string>  $activosIds
     */
    public function ejecutar(Espacio $espacio, array $activosIds, int $userId, ?string $motivo = null): void
    {
        $espacioKey = $espacio->getKey();
        $espacioId = is_numeric($espacioKey) ? (int) $espacioKey : 0;
        if ($espacioId <= 0) {
            return;
        }

        $nuevosIds = array_values(array_unique(array_filter(
            array_map(fn ($id) => is_numeric($id) ? (int) $id : 0, $activosIds),
            fn (int $id): bool => $id > 0
        )));

        $espacio->loadMissing('inventarioFijo');
        $actualesIds = $espacio->inventarioFijo
            ->pluck('activo_id')
            ->map(fn ($id) => is_numeric($id) ? (int) $id : 0)
            ->filter(fn (int $id): bool => $id > 0)
            ->values()
            ->all();

        $agregar = array_values(array_diff($nuevosIds, $actualesIds));
        $retirar = array_values(array_diff($actualesIds, $nuevosIds));

        DB::transaction(function () use ($espacio, $espacioId, $userId, $motivo, $agregar, $retirar) {
            foreach ($agregar as $activoId) {
                $this->asignarActivo->ejecutar(
                    activoId: $activoId,
                    asignableType: Espacio::class,
                    asignableId: $espacioId,
                    userId: $userId,
                    motivo: $motivo ?? "Asignación directa a sub-espacio {$espacio->nombre}"
                );
            }

            foreach ($retirar as $activoId) {
                $this->asignacionRepositorio->cerrarAsignacionesVigentes(
                    activoId: $activoId,
                    fechaFin: now()->toDateString(),
                    estado: EstadoAsignacion::Cerrada->value
                );
            }
        });
    }
}
