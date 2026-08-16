<?php

declare(strict_types=1);

namespace App\Enums\Promociones;

enum EstadoUsoBeneficioCliente: string
{
    case Reservado = 'reservado';
    case Aplicado = 'aplicado';
    case Anulado = 'anulado';

    public function getLabel(): string
    {
        return match ($this) {
            self::Reservado => 'Reservado',
            self::Aplicado => 'Aplicado',
            self::Anulado => 'Anulado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Reservado => 'warning',
            self::Aplicado => 'success',
            self::Anulado => 'danger',
        };
    }
}
