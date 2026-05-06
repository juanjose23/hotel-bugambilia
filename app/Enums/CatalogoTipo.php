<?php

namespace App\Enums;

enum CatalogoTipo: string
{
    // Habitaciones
    case CATEGORIA_HAB = 'CATEGORIA_HAB';
    case CAPACIDAD_HAB = 'CAPACIDAD_HAB';
    case TIPO_TARIFA = 'TIPO_TARIFA';
    case AMENIDAD_HAB = 'AMENIDAD_HAB';

    // Colaboradores
    case CARGO = 'CARGO';
    case DEPARTAMENTO = 'DEPARTAMENTO';

    // Clientes
    case TIPO_CLIENTE = 'TIPO_CLIENTE';
    case SECTOR_COMERCIAL = 'SECTOR_COMERCIAL';

    // Inventario
    case TIPO_MOVIMIENTO_INV = 'TIPO_MOVIMIENTO_INV';

    // Servicios
    case CATEGORIA_SERVICIO = 'CATEGORIA_SERVICIO';
    case TIPO_SERVICIO = 'TIPO_SERVICIO';

    // Promociones
    case TIPO_PROMOCION = 'TIPO_PROMOCION';

    /** @return array<string, string> */
    public static function options(): array
    {
        return array_column(self::cases(), 'value', 'value');
    }

    public static function byCode(string $code): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->value === $code) {
                return $case;
            }
        }

        return null;
    }
}
