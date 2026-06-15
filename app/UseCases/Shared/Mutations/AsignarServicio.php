<?php

declare(strict_types=1);

namespace App\UseCases\Shared\Mutations;

use App\Models\Servicios\Servicio;
use App\Models\Shared\ServicioAsignacion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AsignarServicio
{
    public function execute(
        int $servicioId,
        string $serviceableType,
        int $serviceableId,
        bool $incluido = false,
        int $estado = 1,
    ): ServicioAsignacion {
        /** @var Model $serviceable */
        $serviceable = $serviceableType::findOrFail($serviceableId);
        Servicio::findOrFail($servicioId);

        return DB::transaction(function () use ($servicioId, $serviceable, $incluido, $estado) {
            $relacion = ServicioAsignacion::withTrashed()
                ->where('servicio_id', $servicioId)
                ->where('serviceable_id', $serviceable->getKey())
                ->where('serviceable_type', $serviceable::class)
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

            return ServicioAsignacion::create([
                'servicio_id' => $servicioId,
                'serviceable_id' => $serviceable->getKey(),
                'serviceable_type' => $serviceable::class,
                'incluido' => $incluido,
                'estado' => $estado,
            ]);
        });
    }
}
