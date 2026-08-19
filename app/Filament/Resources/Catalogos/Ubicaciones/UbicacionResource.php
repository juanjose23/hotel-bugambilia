<?php

declare(strict_types=1);

namespace App\Filament\Resources\Catalogos\Ubicaciones;

use App\Filament\Resources\Catalogos\Ubicaciones\Pages\ArbolUbicacion;
use App\Filament\Resources\Catalogos\Ubicaciones\Pages\ListUbicaciones;
use App\Filament\Resources\Catalogos\Ubicaciones\Pages\ViewUbicacion;
use App\Filament\Resources\Catalogos\Ubicaciones\Schemas\UbicacionForm;
use App\Filament\Resources\Catalogos\Ubicaciones\Schemas\UbicacionInfolist;
use App\Filament\Resources\Catalogos\Ubicaciones\Tables\UbicacionesTable;
use App\Filament\Shared\RelationManagers\InventarioFijoRelationManager;
use App\Filament\Shared\RelationManagers\StocksRelationManager;
use App\Repository\Models\Catalogos\Ubicacion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class UbicacionResource extends Resource
{
    protected static ?string $model = Ubicacion::class;

    protected static ?string $slug = 'catalogos/ubicaciones';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::MapPin;

    protected static ?string $pluralModelLabel = 'Ubicaciones';

    protected static string|UnitEnum|null $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return UbicacionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UbicacionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UbicacionesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            StocksRelationManager::class,
            InventarioFijoRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUbicaciones::route('/'),
            'tree' => ArbolUbicacion::route('/visualizador'),
            'view' => ViewUbicacion::route('/{record}'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
