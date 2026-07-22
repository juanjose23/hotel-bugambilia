<?php

declare(strict_types=1);

namespace App\Enums\Catalogos;

namespace App\Enums\Catalogos;

enum CatalogoTipo: string
{
    case CARGO = 'CARGO';
    case DEPARTAMENTO = 'DEPARTAMENTO';
    case TIPO_CLIENTE = 'TIPO_CLIENTE';
    case SECTOR_COMERCIAL = 'SECTOR_COMERCIAL';
    case CATEGORIA_PRODUCTO = 'CATEGORIA_PRODUCTO';
    case MARCA = 'MARCA';
    case UNIDAD_MEDIDA = 'UNIDAD_MEDIDA';
    case TIPO_MOVIMIENTO_INV = 'TIPO_MOVIMIENTO_INV';
    case CONDICION_PAGO = 'CONDICION_PAGO';
    case TIPO_PROVEEDOR = 'TIPO_PROVEEDOR';
    case CATEGORIA_SERVICIO = 'CATEGORIA_SERVICIO';
    case TIPO_SERVICIO = 'TIPO_SERVICIO';
    case TIPO_PROMOCION = 'TIPO_PROMOCION';
    case CATEGORIA_HABITACION = 'CATEGORIA_HABITACION';
    case TIPO_VISTA = 'TIPO_VISTA';
    case TIPO_ESPACIO = 'TIPO_ESPACIO';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_column(self::cases(), 'value', 'value');
    }
}
