<?php

declare(strict_types=1);

namespace App\Support\Pdf;

enum TiposReporte: string
{
    case TABLA_SIMPLE = 'tabla_simple';
    case TABLA_DETALLE = 'tabla_detalle';
    case TABLA_MAESTRO_DETALLE = 'tabla_maestro_detalle';
    case ETIQUETA = 'etiqueta';
    case FICHA = 'ficha';

    public function configuracion(): ConfiguracionPagina
    {
        return match ($this) {
            self::TABLA_SIMPLE => new ConfiguracionPagina(
                filas: 40,
                altoFilaMm: 11,
                altoEncabezadoMm: 10,
            ),
            self::TABLA_DETALLE => new ConfiguracionPagina(
                filas: 40,
                altoFilaMm: 11,
                altoEncabezadoMm: 10,
            ),
            self::TABLA_MAESTRO_DETALLE => new ConfiguracionPagina(
                filas: 12,
                altoFilaMm: 16,
            ),
            self::ETIQUETA => new ConfiguracionPagina(
                filas: 5,
                columnas: 3,
                altoFilaMm: 44,
            ),
            self::FICHA => new ConfiguracionPagina(
                filas: 1,
                altoFilaMm: 0,
            ),
        };
    }
}
