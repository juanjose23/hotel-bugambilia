<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Icons\Heroicon;

enum PrecioEstado: int
{
    case Vigente = 1;
    case NoVigente = 2;

    public function label(): string
    {
        return match ($this) {
            self::Vigente => 'Vigente',
            self::NoVigente => 'No Vigente',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Vigente => 'success',
            self::NoVigente => 'danger',
        };
    }

    public function icon(): Heroicon
    {
        return match ($this) {
            self::Vigente => Heroicon::CheckCircle,
            self::NoVigente => Heroicon::XCircle,
        };
    }

    /** @return array<int, string> */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
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
