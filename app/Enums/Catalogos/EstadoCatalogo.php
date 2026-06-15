<?php

declare(strict_types=1);

namespace App\Enums\Catalogos;

use App\Enums\Concerns\HasEnumHelpers;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum EstadoCatalogo: int implements HasColor, HasIcon, HasLabel
{
    use HasEnumHelpers;

    case Inactivo = 0;
    case Activo = 1;
    case Borrador = 2;
    case Pendiente = 3;
    case Enviada = 4;
    case Recibida = 5;
    case Cancelada = 6;
    case Aprobada = 7;
    case Rechazada = 8;

    public function getLabel(): string
    {
        return match ($this) {
            self::Inactivo => 'Inactivo',
            self::Activo => 'Activo',
            self::Borrador => 'Borrador',
            self::Pendiente => 'Pendiente',
            self::Enviada => 'Enviada',
            self::Recibida => 'Recibida',
            self::Cancelada => 'Cancelada',
            self::Aprobada => 'Aprobada',
            self::Rechazada => 'Rechazada',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Inactivo => 'danger',
            self::Activo => 'success',
            self::Borrador => 'gray',
            self::Pendiente => 'warning',
            self::Enviada => 'info',
            self::Recibida => 'success',
            self::Cancelada => 'danger',
            self::Aprobada => 'success',
            self::Rechazada => 'danger',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Inactivo => Heroicon::XCircle,
            self::Activo => Heroicon::CheckCircle,
            self::Borrador => Heroicon::DocumentText,
            self::Pendiente => Heroicon::Clock,
            self::Enviada => Heroicon::PaperAirplane,
            self::Recibida => Heroicon::ArchiveBoxArrowDown,
            self::Cancelada => Heroicon::XCircle,
            self::Aprobada => Heroicon::CheckBadge,
            self::Rechazada => Heroicon::NoSymbol,
        };
    }

    /**
     * @return array<int, self>
     */
    public static function defaultEstados(): array
    {
        return [self::Activo, self::Inactivo];
    }

    /**
     * @return array<int, self>
     */
    public static function forSolicitud(): array
    {
        return [
            self::Borrador,
            self::Pendiente,
            self::Aprobada,
            self::Rechazada,
            self::Cancelada,
        ];
    }

    /**
     * @return array<int, self>
     */
    public static function forOrden(): array
    {
        return [
            self::Borrador,
            self::Enviada,
            self::Recibida,
            self::Cancelada,
        ];
    }

    /**
     * @param  array<int, self>|callable|null  $estados
     * @return array<int, string>
     */
    public static function options(array|callable|null $estados = null): array
    {
        if (is_callable($estados)) {
            $estados = $estados();
        }

        $estados ??= self::defaultEstados();

        $options = [];
        foreach ($estados as $estado) {
            $options[$estado->value] = $estado->getLabel();
        }

        return $options;
    }
}
