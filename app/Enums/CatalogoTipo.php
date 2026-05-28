<?php

declare(strict_types=1);

namespace App\Enums;

enum CatalogoTipo: string
{
    // Colaboradores
    case CARGO = 'CARGO';
    case DEPARTAMENTO = 'DEPARTAMENTO';

    // Clientes
    case TIPO_CLIENTE = 'TIPO_CLIENTE';
    case SECTOR_COMERCIAL = 'SECTOR_COMERCIAL';

    // Productos y Variantes
    case CATEGORIA_PRODUCTO = 'CATEGORIA_PRODUCTO';
    case MARCA = 'MARCA';
    case UNIDAD_MEDIDA = 'UNIDAD_MEDIDA';
    // Inventario
    case TIPO_MOVIMIENTO_INV = 'TIPO_MOVIMIENTO_INV';

    // Proveedores
    case CONDICION_PAGO = 'CONDICION_PAGO';
    case TIPO_PROVEEDOR = 'TIPO_PROVEEDOR';

    // Servicios
    case CATEGORIA_SERVICIO = 'CATEGORIA_SERVICIO';
    case TIPO_SERVICIO = 'TIPO_SERVICIO';

    // Promociones
    case TIPO_PROMOCION = 'TIPO_PROMOCION';

    // Habitaciones
    case CATEGORIA_HABITACION = 'CATEGORIA_HABITACION';
    case TIPO_VISTA = 'TIPO_VISTA';

    // Espacios
    case TIPO_ESPACIO = 'TIPO_ESPACIO';

    /** @return array<string, string> */
    public static function options(): array
    {
        return array_column(self::cases(), 'value', 'value');
    }
}
