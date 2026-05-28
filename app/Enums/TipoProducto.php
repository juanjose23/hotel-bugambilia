<?php

declare(strict_types=1);

namespace App\Enums;

enum TipoProducto: int
{
    case Perecedero = 1;
    case NoPerecedero = 2;
    case ActivoFijo = 3;

    public function label(): string
    {
        return match ($this) {
            self::Perecedero => 'Perecedero',
            self::NoPerecedero => 'No Perecedero',
            self::ActivoFijo => 'Activo Fijo (Individualizable)'
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Perecedero => 'danger',
            self::NoPerecedero => 'success',
            self::ActivoFijo => 'info',
        };
    }

    /** @return array<int, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->all();
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
