<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Servicios;

use App\Repository\Models\Servicios\Servicio;
use App\Repository\Models\Shared\Imagen;
use App\Repository\Models\Shared\ServicioAsignacion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

final class ServicioRepositorio implements ServicioRepositorioInterface
{
    /** @param array<string, mixed> $datos */
    public function crear(array $datos): Servicio
    {
        return Servicio::create($datos);
    }

    public function buscarActivoConPrecios(int $id): ?Servicio
    {
        return Servicio::with(['precios.moneda'])->activos()->find($id);
    }

    public function buscarPorId(int $id): ?Servicio
    {
        return Servicio::query()->find($id);
    }

    /** @param array<int, string> $imageUrls */
    public function sincronizarImagenes(Servicio $servicio, array $imageUrls): void
    {
        $existingUrls = $servicio->imagenes()
            ->pluck('url')
            ->map(fn ($u) => is_scalar($u) ? (string) $u : '')
            ->values()
            ->all();

        $toDelete = array_diff($existingUrls, $imageUrls);

        if (! empty($toDelete)) {
            Imagen::query()
                ->where('imagenable_id', $servicio->getKey())
                ->where('imagenable_type', $servicio::class)
                ->whereIn('url', $toDelete)
                ->delete();
        }

        foreach ($imageUrls as $index => $url) {
            $servicio->imagenes()->updateOrCreate(
                ['url' => $url],
                ['orden' => $index + 1],
            );
        }
    }

    public function asignarAServiceable(
        int $servicioId,
        string $serviceableType,
        int $serviceableId,
        bool $incluido = false,
        int $estado = 1
    ): ServicioAsignacion {
        /** @var Servicio $servicio */
        $servicio = Servicio::query()->findOrFail($servicioId);

        /** @var Model $serviceableModel */
        $serviceableModel = new $serviceableType;
        /** @var Model $serviceable */
        $serviceable = $serviceableModel->query()->findOrFail($serviceableId);

        return DB::transaction(function () use ($servicio, $serviceable, $incluido, $estado): ServicioAsignacion {
            /** @var ServicioAsignacion|null $relacionExistente */
            $relacionExistente = ServicioAsignacion::query()
                ->withTrashed()
                ->where('servicio_id', $servicio->id)
                ->where('serviceable_id', $serviceable->getKey())
                ->where('serviceable_type', $serviceable::class)
                ->first();

            if ($relacionExistente !== null) {
                if ($relacionExistente->trashed()) {
                    $relacionExistente->restore();
                }

                $relacionExistente->update([
                    'incluido' => $incluido,
                    'estado' => $estado,
                ]);

                return $relacionExistente;
            }

            return ServicioAsignacion::query()->create([
                'servicio_id' => $servicio->id,
                'serviceable_id' => $serviceable->getKey(),
                'serviceable_type' => $serviceable::class,
                'incluido' => $incluido,
                'estado' => $estado,
            ]);
        });
    }
}
