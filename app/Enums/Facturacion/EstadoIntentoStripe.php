<?php

declare(strict_types=1);

namespace App\Enums\Facturacion;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum EstadoIntentoStripe: string implements HasColor, HasIcon, HasLabel
{
    use TieneAyudantesEnum;

    case RequiereMetodoPago = 'requires_payment_method';
    case RequiereConfirmacion = 'requires_confirmation';
    case RequiereAccion = 'requires_action';
    case Procesando = 'processing';
    case RequiereCaptura = 'requires_capture';
    case Cancelado = 'canceled';
    case Exitoso = 'succeeded';

    public function getLabel(): string
    {
        return match ($this) {
            self::RequiereMetodoPago => 'Requiere método de pago',
            self::RequiereConfirmacion => 'Requiere confirmación',
            self::RequiereAccion => 'Requiere acción',
            self::Procesando => 'Procesando',
            self::RequiereCaptura => 'Requiere captura',
            self::Cancelado => 'Cancelado',
            self::Exitoso => 'Exitoso',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Exitoso => 'success',
            self::Procesando, self::RequiereAccion, self::RequiereCaptura => 'info',
            self::Cancelado => 'danger',
            default => 'warning',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Exitoso => 'heroicon-o-check-circle',
            self::Cancelado => 'heroicon-o-x-circle',
            default => 'heroicon-o-currency-dollar',
        };
    }
}
