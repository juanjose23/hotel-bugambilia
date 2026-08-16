<?php

declare(strict_types=1);

namespace App\Enums\Promociones;

enum TipoReglaBeneficioCliente: string
{
    case MontoMinimo = 'monto_minimo';
    case NochesMinimas = 'noches_minimas';
    case FechaNacimiento = 'fecha_nacimiento';
    case CategoriaHabitacion = 'categoria_habitacion';
    case PrimerReserva = 'primera_reserva';
    case UnaVezPorCliente = 'una_vez_por_cliente';

    public function getLabel(): string
    {
        return match ($this) {
            self::MontoMinimo => 'Monto mínimo',
            self::NochesMinimas => 'Noches mínimas',
            self::FechaNacimiento => 'Cumpleaños',
            self::CategoriaHabitacion => 'Categoría de habitación',
            self::PrimerReserva => 'Primera reserva',
            self::UnaVezPorCliente => 'Una vez por cliente',
        };
    }
}
