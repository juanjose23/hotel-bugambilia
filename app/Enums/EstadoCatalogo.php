<?php

namespace App\Enums;

enum EstadoCatalogo: int
{
    case Inactivo = 0;
    case Activo = 1;

    public function label(): string
    {
        return match ($this) {
            self::Activo => 'Activo',
            self::Inactivo => 'Inactivo',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Activo => 'success',
            self::Inactivo => 'danger',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function options(): array
    {
        return [
            self::Activo->value => self::Activo->label(),
            self::Inactivo->value => self::Inactivo->label(),
        ];
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
}