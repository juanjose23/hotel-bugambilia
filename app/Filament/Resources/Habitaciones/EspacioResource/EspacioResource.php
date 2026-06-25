<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\EspacioResource;

use App\Filament\Resources\Habitaciones\EspacioResource\Pages\CreateEspacio;
use App\Filament\Resources\Habitaciones\EspacioResource\Pages\EditEspacio;
use App\Filament\Resources\Habitaciones\EspacioResource\Pages\ListEspacios;
use App\Filament\Resources\Habitaciones\EspacioResource\Pages\ViewEspacio;
use App\Filament\Resources\Habitaciones\EspacioResource\RelationManagers\PoliticasRelationManager;
use App\Filament\Resources\Habitaciones\EspacioResource\RelationManagers\PreciosRelationManager;
use App\Filament\Resources\Habitaciones\EspacioResource\RelationManagers\ServiciosRelationManager;
use App\Filament\Resources\Habitaciones\EspacioResource\RelationManagers\SubEspaciosRelationManager;
use App\Filament\Resources\Habitaciones\EspacioResource\Schemas\EspacioForm;
use App\Filament\Resources\Habitaciones\EspacioResource\Schemas\EspacioInfolist;
use App\Filament\Resources\Habitaciones\EspacioResource\Tables\EspacioTable;
use App\Filament\Resources\Shared\InventarioFijoRelationManager;
use App\Filament\Resources\Shared\StocksRelationManager;
use App\Models\Espacios\Espacio;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class EspacioResource extends Resource
{
    protected static ?string $model = Espacio::class;

    protected static ?string $slug = 'habitaciones/espacios';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::BuildingStorefront;

    protected static string|UnitEnum|null $navigationGroup = 'Habitaciones y Ambientes';

    protected static ?string $modelLabel = 'Espacio';

    protected static ?string $pluralModelLabel = 'Espacios';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return app(EspacioForm::class)->configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return app(EspacioInfolist::class)->configure($schema);
    }

    public static function table(Table $table): Table
    {
        return app(EspacioTable::class)->configure($table);
    }

    public static function getRelations(): array
    {
        return [
            SubEspaciosRelationManager::class,
            PoliticasRelationManager::class,
            ServiciosRelationManager::class,
            StocksRelationManager::class,
            PreciosRelationManager::class,
            InventarioFijoRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEspacios::route('/'),
            'create' => CreateEspacio::route('/create'),
            'view' => ViewEspacio::route('/{record}'),
            'edit' => EditEspacio::route('/{record}/edit'),
        ];
    }
}
