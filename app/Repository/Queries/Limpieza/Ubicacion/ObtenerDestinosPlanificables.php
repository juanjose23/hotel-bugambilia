<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Ubicacion;

use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;

final readonly class ObtenerDestinosPlanificables
{
    /** @return array<int, string> */
    public function ubicacionesPadre(?string $tipo = null): array
    {
        $ubicacionesIds = match ($tipo) {
            Habitacion::class => Habitacion::query()
                ->whereNotNull('ubicacion_id')
                ->distinct()
                ->pluck('ubicacion_id')
                ->filter(fn (mixed $id): bool => is_numeric($id))
                ->map(fn (mixed $id): int => (int) $id)
                ->values()
                ->all(),
            Espacio::class => Espacio::query()
                ->whereNotNull('ubicacion_id')
                ->distinct()
                ->pluck('ubicacion_id')
                ->filter(fn (mixed $id): bool => is_numeric($id))
                ->map(fn (mixed $id): int => (int) $id)
                ->values()
                ->all(),
            Ubicacion::class, null => null,
            default => [],
        };

        $opciones = app(ObtenerPathUbicacion::class)->ejecutar();

        if ($ubicacionesIds === null) {
            /** @var array<int, string> $opciones */
            return $opciones;
        }

        if ($ubicacionesIds === []) {
            return [];
        }

        $idsPermitidos = $this->resolverAncestrosIds($ubicacionesIds);

        $filtradas = collect($opciones)
            ->only($idsPermitidos)
            ->all();

        /** @var array<int, string> $filtradas */
        return $filtradas;
    }

    /** @return array<int, string> */
    public function destinos(string $tipo, mixed $ubicacionPadreId = null): array
    {
        $ubicacionesIds = $this->resolverUbicacionesIds($ubicacionPadreId);

        return match ($tipo) {
            Habitacion::class => $this->habitaciones($ubicacionesIds),
            Espacio::class => $this->espacios($ubicacionesIds),
            Ubicacion::class => $this->ubicaciones($ubicacionesIds),
            default => [],
        };
    }

    /** @return array<int>|null */
    private function resolverUbicacionesIds(mixed $ubicacionPadreId): ?array
    {
        if (! is_numeric($ubicacionPadreId)) {
            return null;
        }

        return Ubicacion::obtenerDescendientesIds((int) $ubicacionPadreId);
    }

    /**
     * @param  array<int>|null  $ubicacionesIds
     * @return array<int, string>
     */
    private function habitaciones(?array $ubicacionesIds): array
    {
        return Habitacion::query()
            ->with('ubicacion')
            ->when($ubicacionesIds !== null, fn ($query) => $query->whereIn('ubicacion_id', $ubicacionesIds))
            ->orderBy('numero')
            ->get()
            ->mapWithKeys(fn (Habitacion $habitacion): array => [
                $habitacion->id => trim(($habitacion->codigo ? $habitacion->codigo.' - ' : '').($habitacion->nombre ?? 'Habitación #'.$habitacion->id)),
            ])
            ->all();
    }

    /**
     * @param  array<int>|null  $ubicacionesIds
     * @return array<int, string>
     */
    private function espacios(?array $ubicacionesIds): array
    {
        return Espacio::query()
            ->with('padre.padre')
            ->when($ubicacionesIds !== null, fn ($query) => $query->whereIn('ubicacion_id', $ubicacionesIds))
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get()
            ->mapWithKeys(fn (Espacio $espacio): array => [
                $espacio->id => $espacio->getNombreCompleto(),
            ])
            ->all();
    }

    /**
     * @param  array<int>|null  $ubicacionesIds
     * @return array<int, string>
     */
    private function ubicaciones(?array $ubicacionesIds): array
    {
        $opciones = $this->ubicacionesPadre(Ubicacion::class);

        if ($ubicacionesIds === null) {
            return $opciones;
        }

        $filtradas = collect($opciones)
            ->only($ubicacionesIds)
            ->all();

        /** @var array<int, string> $filtradas */
        return $filtradas;
    }

    /**
     * @param  array<int>  $ubicacionesIds
     * @return array<int>
     */
    private function resolverAncestrosIds(array $ubicacionesIds): array
    {
        $ids = [];
        $ubicaciones = Ubicacion::query()
            ->select(['id', 'padre_id'])
            ->get()
            ->keyBy('id');

        foreach ($ubicacionesIds as $ubicacionId) {
            $actualId = $ubicacionId;

            while ($actualId !== null && isset($ubicaciones[$actualId])) {
                $ids[] = (int) $actualId;
                $padreId = $ubicaciones[$actualId]->padre_id;
                $actualId = is_numeric($padreId) ? (int) $padreId : null;
            }
        }

        return array_values(array_unique($ids));
    }
}
