<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\EspacioResource;

use App\Filament\Resources\Habitaciones\EspacioResource\Pages\CreateEspacio;
use App\Filament\Resources\Habitaciones\EspacioResource\Pages\EditEspacio;
use App\Filament\Resources\Habitaciones\EspacioResource\Pages\ListEspacios;
use App\Filament\Resources\Habitaciones\EspacioResource\Pages\ViewEspacio;
use App\Filament\Resources\Habitaciones\EspacioResource\RelationManagers\SubEspaciosRelationManager;
use App\Filament\Resources\Habitaciones\EspacioResource\Schemas\EspacioForm;
use App\Filament\Resources\Habitaciones\EspacioResource\Schemas\EspacioInfolist;
use App\Filament\Resources\Habitaciones\EspacioResource\Tables\EspacioTable;
use App\Filament\Shared\RelationManagers\InventarioFijoRelationManager;
use App\Filament\Shared\RelationManagers\PoliticasRelationManager;
use App\Filament\Shared\RelationManagers\PreciosRelationManager;
use App\Filament\Shared\RelationManagers\ServiciosRelationManager;
use App\Filament\Shared\RelationManagers\StocksRelationManager;
use App\Repository\Models\Espacios\Espacio;
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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Home;

    protected static string|UnitEnum|null $navigationGroup = 'Habitaciones & Espacios';

    protected static ?string $modelLabel = 'Ambiente / Espacio';

    protected static ?string $pluralModelLabel = 'Ambientes y Espacios';

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
            PreciosRelationManager::class,
            StocksRelationManager::class,
            InventarioFijoRelationManager::class,
            ServiciosRelationManager::class,
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
