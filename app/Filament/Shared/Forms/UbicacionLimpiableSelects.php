<?php

declare(strict_types=1);

namespace App\Filament\Shared\Forms;

use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Queries\Limpieza\Ubicacion\ObtenerOpcionesLimpiables;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;

final class UbicacionLimpiableSelects
{
    /**
     * Genera el selector del tipo de ubicación limpiable.
     *
     * @param  array<string, string>  $customOptions
     */
    public static function makeTipo(string $column = 'limpiable_type', array $customOptions = []): Select
    {
        $options = $customOptions ?: [
            Habitacion::class => 'Habitación',
            Espacio::class => 'Espacio Común',
            Ubicacion::class => 'Ubicación Física / Zona',
        ];

        return Select::make($column)
            ->label('Tipo')
            ->options($options)
            ->live()
            ->native(false)
            ->prefixIcon(Heroicon::RectangleStack);
    }

    /**
     * Genera el selector de la ubicación específica basado en el tipo.
     */
    public static function makeUbicacion(
        string $column = 'limpiable_id',
        string $tipoColumn = 'limpiable_type',
        bool $soloEspaciosPadre = false
    ): Select {
        return Select::make($column)
            ->label('Ubicación')
            ->placeholder('Seleccione')
            ->options(function (Get $get) use ($tipoColumn, $soloEspaciosPadre) {
                $tipo = $get($tipoColumn);

                if (! is_string($tipo) || ! class_exists($tipo)) {
                    // Mapear strings sencillos de filtros a clases reales de repositorio
                    if ($tipo === 'habitacion') {
                        $tipo = Habitacion::class;
                    } elseif ($tipo === 'espacio') {
                        $tipo = Espacio::class;
                    } elseif ($tipo === 'ubicacion') {
                        $tipo = Ubicacion::class;
                    } else {
                        return [];
                    }
                }

                if ($soloEspaciosPadre) {
                    return app(ObtenerOpcionesLimpiables::class)->padres($tipo);
                }

                return app(ObtenerOpcionesLimpiables::class)->execute($tipo);
            })
            ->searchable()
            ->preload()
            ->native(false)
            ->prefixIcon(Heroicon::Home)
            ->disabled(fn (Get $get) => ! $get($tipoColumn));
    }

    /**
     * Genera el selector de sub-ubicación/sub-espacio hijo basado en el padre seleccionado.
     */
    public static function makeSubUbicacion(
        string $column = 'sub_ubicacion_id',
        string $padreColumn = 'selectedUbicacionId',
        string $tipoColumn = 'tipo_ubicacion'
    ): Select {
        return Select::make($column)
            ->label('Sub-ubicación')
            ->placeholder('Todas las sub-ubicaciones')
            ->options(function (Get $get) use ($padreColumn, $tipoColumn): array {
                $padreId = $get($padreColumn);
                $tipo = $get($tipoColumn);

                if (! $padreId || ! is_numeric($padreId)) {
                    return [];
                }

                $padreIdInt = (int) $padreId;

                if (! is_string($tipo) || ! class_exists($tipo)) {
                    if ($tipo === 'espacio') {
                        $tipo = Espacio::class;
                    } elseif ($tipo === 'ubicacion') {
                        $tipo = Ubicacion::class;
                    } else {
                        return [];
                    }
                }

                return app(ObtenerOpcionesLimpiables::class)->hijos($tipo, $padreIdInt);
            })
            ->searchable()
            ->preload()
            ->native(false)
            ->prefixIcon(Heroicon::MapPin)
            ->disabled(function (Get $get) use ($padreColumn, $tipoColumn): bool {
                $padreId = $get($padreColumn);
                $tipo = $get($tipoColumn);

                if (! $padreId || ! is_numeric($padreId)) {
                    return true;
                }

                $padreIdInt = (int) $padreId;

                if (! is_string($tipo) || ! class_exists($tipo)) {
                    if ($tipo === 'espacio') {
                        $tipo = Espacio::class;
                    } elseif ($tipo === 'ubicacion') {
                        $tipo = Ubicacion::class;
                    } else {
                        return true;
                    }
                }

                return ! app(ObtenerOpcionesLimpiables::class)->tieneHijos($tipo, $padreIdInt);
            });
    }
}
