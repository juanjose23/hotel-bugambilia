<?php

declare(strict_types=1);

namespace App\UseCases\Espacios\Mutations;

use App\Models\Espacios\Espacio;
use App\Models\Espacios\ServicioEspacio;
use App\Models\Servicios\Servicio;
use Illuminate\Support\Facades\DB;

/**
 * Caso de Uso: Asociar un servicio a un espacio físico.
 */
class AsignarServicioAEspacio
{
    /**
     * Ejecuta la asociación de servicio a un espacio.
     *
     * @param  int  $estado  (0=Inactivo, 1=Activo)
     */
    public function execute(
        int $servicioId,
        int $espacioId,
        bool $incluido = false,
        int $estado = 1
    ): ServicioEspacio {
        Espacio::findOrFail($espacioId);
        Servicio::findOrFail($servicioId);

        return DB::transaction(function () use ($servicioId, $espacioId, $incluido, $estado) {
            $relacion = ServicioEspacio::withTrashed()
                ->where('servicio_id', $servicioId)
                ->where('espacio_id', $espacioId)
                ->first();

            if ($relacion) {
                if ($relacion->trashed()) {
                    $relacion->restore();
                }
                $relacion->update([
                    'incluido' => $incluido,
                    'estado' => $estado,
                ]);

                return $relacion;
            }

            return ServicioEspacio::create([
                'servicio_id' => $servicioId,
                'espacio_id' => $espacioId,
                'incluido' => $incluido,
                'estado' => $estado,
            ]);
        });
    }
}
