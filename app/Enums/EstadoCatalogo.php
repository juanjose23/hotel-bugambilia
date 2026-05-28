<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Icons\Heroicon;

enum EstadoCatalogo: int
{
    case Inactivo = 0;
    case Activo = 1;
    case Borrador = 2;
    case Pendiente = 3;
    case Enviada = 4;
    case Recibida = 5;
    case Cancelada = 6;
    case Aprobada = 7;
    case Rechazada = 8;

    public function label(): string
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

    public function color(): string
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

    public function icon(): Heroicon
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

    /** @return array<self> */
    public static function defaultEstados(): array
    {
        return [self::Activo, self::Inactivo];
    }

    /** @return array<self> */
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

    /** @return array<self> */
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
     * @param  array<self>|callable|null  $estados
     * @return array<int,string>
     */
    public static function options(array|callable|null $estados = null): array
    {
        if (is_callable($estados)) {
            $estados = $estados();
        }

        $estados ??= self::defaultEstados();

        $options = [];
        foreach ($estados as $estado) {
            $options[$estado->value] = $estado->label();
        }

        return $options;
    }

    public static function fromValue(self|int|string|null $value): ?self
    {
        if ($value instanceof self) {
            return $value;
        }
        if ($value === null || $value === '') {
            return null;
        }

        return self::tryFrom((int) $value);
    }

    public static function labelFor(self|int|string|null $value): string
    {
        return self::fromValue($value)?->label() ?? 'No definido';
    }

    public static function colorFor(self|int|string|null $value): string
    {
        return self::fromValue($value)?->color() ?? 'gray';
    }

    public static function iconFor(self|int|string|null $value): ?Heroicon
    {
        return self::fromValue($value)?->icon();
    }
}
