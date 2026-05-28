<?php

declare(strict_types=1);

namespace App\Enums;

enum TipoIdentificacion: string
{
    case Cedula = 'cedula';
    case Dni = 'dni';
    case Pasaporte = 'pasaporte';
    case Residencia = 'residencia';
    case Nit = 'nit';
    case Ruc = 'ruc';
    case Otro = 'otro';

    public function label(): string
    {
        return match ($this) {
            self::Cedula => 'Cédula',
            self::Dni => 'DNI',
            self::Pasaporte => 'Pasaporte',
            self::Residencia => 'Residencia',
            self::Nit => 'NIT',
            self::Ruc => 'RUC',
            self::Otro => 'Otro',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Cedula => 'success',
            self::Dni => 'danger',
            self::Pasaporte => 'warning',
            self::Residencia, self::Otro => 'info',
            self::Nit => 'primary',
            self::Ruc => 'secondary',
        };
    }

    /**
     * Opciones para Select (Filament / Forms)
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [
                $case->value => $case->label(),
            ])
            ->toArray();
    }

    public static function fromValue(string|self|null $value): ?self
    {
        if ($value instanceof self) {
            return $value;
        }

        if (! $value) {
            return null;
        }

        return self::tryFrom($value);
    }

    public static function labelFor(string|self|null $value): string
    {
        return self::fromValue($value)?->label() ?? 'No definido';
    }

    public static function colorFor(string|self|null $value): string
    {
        return self::fromValue($value)?->color() ?? 'gray';
    }
}
