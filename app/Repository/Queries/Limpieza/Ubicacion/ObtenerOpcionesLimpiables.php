<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Ubicacion;

use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;

final class ObtenerOpcionesLimpiables
{
    /**
     * @return array<int, string>
     */
    public function execute(?string $tipo): array
    {
        $tipo = $this->normalizarTipo($tipo);

        if ($tipo === null) {
            return [];
        }

        if ($tipo === Espacio::class) {
            $opciones = Espacio::query()
                ->with('padre.padre')
                ->orderBy('nombre')
                ->get()
                ->mapWithKeys(fn (Espacio $espacio): array => [
                    (int) $espacio->id => $espacio->getNombreCompleto(),
                ])
                ->toArray();

            /** @var array<int, string> $opciones */
            return $opciones;
        }

        if ($tipo === Ubicacion::class) {
            $opciones = app(ObtenerPathUbicacion::class)->ejecutar();

            /** @var array<int, string> $opciones */
            return $opciones;
        }

        $opciones = Habitacion::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre'])
            ->mapWithKeys(fn (Habitacion $habitacion): array => [(int) $habitacion->id => (string) $habitacion->nombre])
            ->toArray();

        /** @var array<int, string> $opciones */
        return $opciones;
    }

    /**
     * @return array<int, string>
     */
    public function padres(?string $tipo): array
    {
        $tipo = $this->normalizarTipo($tipo);

        if ($tipo === Espacio::class) {
            $opciones = Espacio::query()
                ->whereNull('padre_id')
                ->get(['id', 'nombre'])
                ->mapWithKeys(fn (Espacio $espacio): array => [(int) $espacio->id => (string) $espacio->nombre])
                ->toArray();

            /** @var array<int, string> $opciones */
            return $opciones;
        }

        if ($tipo === Ubicacion::class) {
            $opciones = Ubicacion::query()
                ->whereNull('padre_id')
                ->get(['id', 'nombre'])
                ->mapWithKeys(fn (Ubicacion $ubicacion): array => [(int) $ubicacion->id => (string) $ubicacion->nombre])
                ->toArray();

            /** @var array<int, string> $opciones */
            return $opciones;
        }

        return [];
    }

    /**
     * @return array<int, string>
     */
    public function hijos(?string $tipo, mixed $padreId): array
    {
        $tipo = $this->normalizarTipo($tipo);

        if ($tipo === null || ! is_numeric($padreId)) {
            return [];
        }

        $padreId = (int) $padreId;

        if ($tipo === Espacio::class) {
            $opciones = Espacio::query()
                ->where('padre_id', $padreId)
                ->get(['id', 'nombre'])
                ->mapWithKeys(fn (Espacio $espacio): array => [(int) $espacio->id => (string) $espacio->nombre])
                ->toArray();

            /** @var array<int, string> $opciones */
            return $opciones;
        }

        if ($tipo === Ubicacion::class) {
            $opciones = Ubicacion::query()
                ->where('padre_id', $padreId)
                ->get(['id', 'nombre'])
                ->mapWithKeys(fn (Ubicacion $ubicacion): array => [(int) $ubicacion->id => (string) $ubicacion->nombre])
                ->toArray();

            /** @var array<int, string> $opciones */
            return $opciones;
        }

        return [];
    }

    public function tieneHijos(?string $tipo, mixed $padreId): bool
    {
        $tipo = $this->normalizarTipo($tipo);

        if ($tipo === null || ! is_numeric($padreId)) {
            return false;
        }

        $padreId = (int) $padreId;

        return match ($tipo) {
            Espacio::class => Espacio::query()->where('padre_id', $padreId)->exists(),
            Ubicacion::class => Ubicacion::query()->where('padre_id', $padreId)->exists(),
            default => false,
        };
    }

    private function normalizarTipo(?string $tipo): ?string
    {
        return match ($tipo) {
            'habitacion', Habitacion::class => Habitacion::class,
            'espacio', Espacio::class => Espacio::class,
            'ubicacion', Ubicacion::class => Ubicacion::class,
            default => null,
        };
    }
}
