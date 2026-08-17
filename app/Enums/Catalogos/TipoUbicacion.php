<?php

declare(strict_types=1);

namespace App\Enums\Catalogos;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum TipoUbicacion: string implements HasColor, HasIcon, HasLabel
{
    use TieneAyudantesEnum;

    case EDIFICIO = 'edificio';
    case PISO = 'piso';
    case SECTOR = 'sector';
    case ZONA = 'zona';
    case ALMACEN = 'almacen';
    case BODEGA = 'bodega';
    case ESTANTE = 'estante';
    case NIVEL = 'nivel';
    case POSICION = 'posicion';
    case HABITACION = 'habitacion';
    case ESPACIO = 'espacio';
    case LAVANDERIA = 'lavanderia';
    case BLANCOS_SUCIOS = 'blancos_sucios';
    case BLANCOS_LIMPIOS = 'blancos_limpios';
    case MERMA = 'merma';
    case CARRITO = 'carrito';

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
            self::CARRITO => 'Carrito de Limpieza',
        };
    }

    public function getIcon(): Heroicon
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
            self::LAVANDERIA => Heroicon::CheckBadge,
            self::BLANCOS_SUCIOS => Heroicon::Trash,
            self::BLANCOS_LIMPIOS => Heroicon::CheckBadge,
            self::MERMA => Heroicon::ArchiveBoxXMark,
            self::CARRITO => Heroicon::ShoppingCart,
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
            self::CARRITO => 'warning',
        };
    }
}
