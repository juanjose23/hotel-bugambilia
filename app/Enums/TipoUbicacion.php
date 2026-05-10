<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum TipoUbicacion: string implements HasColor, HasIcon, HasLabel
{
    case EDIFICIO = 'edificio';
    case PISO = 'piso';
    case SECTOR = 'sector';
    case ZONA = 'zona';

    public function getLabel(): string
    {
        return match ($this) {
            self::EDIFICIO => 'Edificio',
            self::PISO => 'Piso',
            self::SECTOR => 'Sector',
            self::ZONA => 'Zona',
        };
    }

    public function getIcon(): BackedEnum
    {
        return match ($this) {
            self::EDIFICIO => Heroicon::BuildingOffice2,
            self::PISO => Heroicon::RectangleStack,
            self::SECTOR => Heroicon::Squares2x2,
            self::ZONA => Heroicon::MapPin,
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::EDIFICIO => 'primary',
            self::PISO => 'info',
            self::SECTOR => 'warning',
            self::ZONA => 'success',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case): array => [$case->value => $case->getLabel()])
            ->toArray();
    }

    public static function fromValue(self|string|null $value): ?self
    {
        if ($value instanceof self) {
            return $value;
        }

        if ($value === null || $value === '') {
            return null;
        }

        return self::tryFrom($value);
    }

    public static function labelFor(self|string|null $value): string
    {
        return self::fromValue($value)?->getLabel() ?? 'No definido';
    }

    public static function colorFor(self|string|null $value): string
    {
        return self::fromValue($value)?->getColor() ?? 'gray';
    }

    public static function iconFor(self|string|null $value): ?BackedEnum
    {
        return self::fromValue($value)?->getIcon();
    }
}
