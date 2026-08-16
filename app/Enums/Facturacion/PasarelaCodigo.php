<?php

declare(strict_types=1);

namespace App\Enums\Facturacion;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PasarelaCodigo: string implements HasColor, HasLabel
{
    use TieneAyudantesEnum;

    case Stripe = 'stripe';

    public function getLabel(): string
    {
        return match ($this) {
            self::Stripe => 'Stripe',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Stripe => 'info',
        };
    }

    /**
     * Claves requeridas en config/services.php para habilitar la pasarela.
     *
     * @return list<string>
     */
    public function clavesRequeridas(): array
    {
        return match ($this) {
            self::Stripe => ['key', 'secret', 'webhook_secret'],
        };
    }
}
