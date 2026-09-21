<?php

declare(strict_types=1);

namespace App\Filament\Shared\Concerns;

use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Servicios\Servicio;
use App\Support\CachedOptions;
use Illuminate\Support\Collection;

/**
 * Fuente única de verdad del mapa de destinos de asignación de activos fijos.
 *
 * Centraliza el mapa que antes estaba duplicado en los formularios, tablas,
 * filtros, actions, el modelo ActivoAsignacion y los casos de uso de activos.
 */
final class TipoDestinoAsignacionActivo
{
    /**
     * @return array<class-string, string> Tabla de tipos de destino válidos
     */
    public static function destinos(): array
    {
        return [
            Habitacion::class => 'Habitación',
            Ubicacion::class => 'Ubicación / Bodega',
            Espacio::class => 'Espacio / Área Común',
            Servicio::class => 'Servicio / Actividad',
        ];
    }

    /**
     * Opciones de destino específico según el tipo seleccionado.
     *
     * @return Collection<int, string>
     */
    public static function opcionesDestino(?string $tipo): Collection
    {
        return match ($tipo) {
            Habitacion::class => CachedOptions::habitaciones(),
            Ubicacion::class => CachedOptions::ubicacionesAlmacen(),
            Espacio::class => CachedOptions::espacios(),
            Servicio::class => CachedOptions::serviciosActivos(),
            default => collect(),
        };
    }

    public static function tipoDestinoLabel(?string $tipo): string
    {
        return match ($tipo) {
            Habitacion::class => 'Habitación',
            Ubicacion::class => 'Ubicación / Bodega',
            Espacio::class => 'Espacio / Área Común',
            Servicio::class => 'Servicio / Actividad',
            default => class_basename((string) $tipo) ?: 'Sin tipo',
        };
    }

    public static function tipoDestinoColor(?string $tipo): string
    {
        return match ($tipo) {
            Habitacion::class => 'success',
            Ubicacion::class => 'info',
            Espacio::class => 'warning',
            Servicio::class => 'primary',
            default => 'gray',
        };
    }
}
