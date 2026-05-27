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
    case ALMACEN = 'almacen';
    case BODEGA = 'bodega';
    case ESTANTE = 'estante';
    case NIVEL = 'nivel';
    case POSICION = 'posicion';

    // Tipos operativos para Blancos y Lavandería
    case HABITACION = 'habitacion';
    case ESPACIO = 'espacio';
    case LAVANDERIA = 'lavanderia';
    case BLANCOS_SUCIOS = 'blancos_sucios';
    case BLANCOS_LIMPIOS = 'blancos_limpios';
    case MERMA = 'merma';

    public function getLabel(): string
    {
        return match ($this) {
            self::EDIFICIO => 'Edificio',
            self::PISO => 'Piso',
            self::SECTOR => 'Sector',
            self::ZONA => 'Zona',
            self::ALMACEN => 'Almacén',
            self::BODEGA => 'Bodega',
            self::ESTANTE => 'Estante',
            self::NIVEL => 'Nivel',
            self::POSICION => 'Posición',
            self::HABITACION => 'Habitación',
            self::ESPACIO => 'Espacio',
            self::LAVANDERIA => 'Lavandería',
            self::BLANCOS_SUCIOS => 'Blancos Sucios',
            self::BLANCOS_LIMPIOS => 'Blancos Limpios',
            self::MERMA => 'Merma Operativa',
        };
    }

    public function getIcon(): BackedEnum
    {
        return match ($this) {
            self::EDIFICIO => Heroicon::BuildingOffice2,
            self::PISO => Heroicon::RectangleStack,
            self::SECTOR => Heroicon::Squares2x2,
            self::ZONA => Heroicon::MapPin,
            self::ALMACEN => Heroicon::BuildingStorefront,
            self::BODEGA => Heroicon::ArchiveBox,
            self::ESTANTE => Heroicon::QueueList,
            self::NIVEL => Heroicon::ListBullet,
            self::POSICION => Heroicon::MapPin,
            self::HABITACION => Heroicon::Home,
            self::ESPACIO => Heroicon::Cube,
            self::LAVANDERIA => Heroicon::Sparkles,
            self::BLANCOS_SUCIOS => Heroicon::Trash,
            self::BLANCOS_LIMPIOS => Heroicon::Sparkles,
            self::MERMA => Heroicon::ArchiveBoxXMark,
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::EDIFICIO => 'primary',
            self::PISO => 'info',
            self::SECTOR => 'warning',
            self::ZONA => 'success',
            self::ALMACEN => 'gray',
            self::BODEGA => 'indigo',
            self::ESTANTE => 'danger',
            self::NIVEL => 'orange',
            self::POSICION => 'info',
            self::HABITACION => 'success',
            self::ESPACIO => 'primary',
            self::LAVANDERIA => 'info',
            self::BLANCOS_SUCIOS => 'warning',
            self::BLANCOS_LIMPIOS => 'success',
            self::MERMA => 'danger',
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
