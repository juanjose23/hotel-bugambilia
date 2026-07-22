<?php

declare(strict_types=1);

namespace App\Interactors\Shared;

use App\Repository\Models\Servicios\Servicio;
use App\Repository\Models\Shared\ServicioAsignacion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AsignarServicio
{
    public function __construct(
        private readonly Servicio $servicioModel,
        private readonly ServicioAsignacion $asignacionModel,
    ) {}

    public function execute(
        int $servicioId,
        string $serviceableType,
        int $serviceableId,
        bool $incluido = false,
        int $estado = 1,
    ): ServicioAsignacion {
        $servicio = $this->loadServicio($servicioId);
        $serviceable = $this->loadServiceable($serviceableType, $serviceableId);

        return DB::transaction(function () use ($servicio, $serviceable, $incluido, $estado) {
            $relacionExistente = $this->buscarRelacionPrevia($servicio, $serviceable);

            return $relacionExistente !== null
                ? $this->reactivarORenovarRelacion($relacionExistente, $incluido, $estado)
                : $this->crearNuevaRelacion($servicio, $serviceable, $incluido, $estado);
        });
    }

    private function loadServicio(int $id): Servicio
    {
        return $this->servicioModel->query()->findOrFail($id);
    }

    private function loadServiceable(string $type, int $id): Model
    {
        /** @var Model $model */
        $model = new $type;

        return $model->query()->findOrFail($id);
    }

    private function buscarRelacionPrevia(Servicio $servicio, Model $serviceable): ?ServicioAsignacion
    {
        return $this->asignacionModel->query()
            ->withTrashed()
            ->where('servicio_id', $servicio->id)
            ->where('serviceable_id', $serviceable->getKey())
            ->where('serviceable_type', $serviceable::class)
            ->first();
    }

    private function reactivarORenovarRelacion(
        ServicioAsignacion $relacion,
        bool $incluido,
        int $estado,
    ): ServicioAsignacion {
        if ($relacion->trashed()) {
            $relacion->restore();
        }

        $relacion->update([
            'incluido' => $incluido,
            'estado' => $estado,
        ]);

        return $relacion;
    }

    private function crearNuevaRelacion(
        Servicio $servicio,
        Model $serviceable,
        bool $incluido,
        int $estado,
    ): ServicioAsignacion {
        return $this->asignacionModel->query()->create([
            'servicio_id' => $servicio->id,
            'serviceable_id' => $serviceable->getKey(),
            'serviceable_type' => $serviceable::class,
            'incluido' => $incluido,
            'estado' => $estado,
        ]);
    }
}
