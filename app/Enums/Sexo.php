<?php

declare(strict_types=1);

namespace App\Enums;

enum Sexo: string
{
    case MASCULINO = 'M';
    case FEMENINO = 'F';
    case OTRO = 'O';

    public function label(): string
    {
        return match ($this) {
            self::MASCULINO => 'Masculino',
            self::FEMENINO => 'Femenino',
            self::OTRO => 'Otro'
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::MASCULINO => 'success',
            self::FEMENINO => 'primary',
            self::OTRO => 'danger',
        };
    }

    /** @return array<string, string> */
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

        return self::tryFrom((string) $value);
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
