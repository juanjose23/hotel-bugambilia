<?php

declare(strict_types=1);

namespace App\Enums\Restaurante;

enum EstadoPedido: string
{
    case Abierto = 'abierto';
    case Preparacion = 'preparacion';
    case Listo = 'listo';
    case Servido = 'servido';
    case Pagado = 'pagado';
    case Cancelado = 'cancelado';

    public function label(): string
    {
        return match ($this) {
            self::Abierto => 'Abierto',
            self::Preparacion => 'En Preparación',
            self::Listo => 'Listo para Servir',
            self::Servido => 'Servido',
            self::Pagado => 'Pagado',
            self::Cancelado => 'Cancelado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Abierto => 'warning',
            self::Preparacion => 'info',
            self::Listo => 'success',
            self::Servido => 'primary',
            self::Pagado => 'gray',
            self::Cancelado => 'danger',
        };
    }
}
