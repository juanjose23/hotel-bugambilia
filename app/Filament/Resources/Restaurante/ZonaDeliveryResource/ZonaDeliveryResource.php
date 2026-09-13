<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\ZonaDeliveryResource;

use App\BusinessLogic\Restaurante\Mesas\VerificarRestauranteActivo;
use App\Filament\Resources\Restaurante\ZonaDeliveryResource\Pages\CreateZonaDelivery;
use App\Filament\Resources\Restaurante\ZonaDeliveryResource\Pages\EditZonaDelivery;
use App\Filament\Resources\Restaurante\ZonaDeliveryResource\Pages\ListZonasDelivery;
use App\Filament\Resources\Restaurante\ZonaDeliveryResource\Schemas\ZonaDeliveryForm;
use App\Filament\Resources\Restaurante\ZonaDeliveryResource\Tables\ZonaDeliveryTable;
use App\Repository\Models\Restaurante\ZonaDelivery;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

final class ZonaDeliveryResource extends Resource
{
    protected static ?string $model = ZonaDelivery::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Truck;

    protected static string|UnitEnum|null $navigationGroup = 'Restaurante & Cocina';

    protected static ?string $navigationLabel = 'Zonas de Delivery';

    protected static ?string $modelLabel = 'Zona de Delivery';

    protected static ?string $pluralModelLabel = 'Zonas de Delivery';

    protected static ?int $navigationSort = 6;

    protected static ?string $slug = 'restaurante/zonas-delivery';

    public static function form(Schema $schema): Schema
    {
        return ZonaDeliveryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ZonaDeliveryTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListZonasDelivery::route('/'),
            'create' => CreateZonaDelivery::route('/create'),
            'edit' => EditZonaDelivery::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return self::restauranteActivo() && parent::shouldRegisterNavigation();
    }

    public static function canViewAny(): bool
    {
        return self::restauranteActivo() && parent::canViewAny();
    }

    private static function restauranteActivo(): bool
    {
        return self::$restauranteActivo ??= app(VerificarRestauranteActivo::class)->estaActivo();
    }

    private static ?bool $restauranteActivo = null;
}
