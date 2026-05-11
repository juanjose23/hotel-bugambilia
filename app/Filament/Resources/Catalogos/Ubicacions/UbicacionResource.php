<?php

namespace App\Filament\Resources\Catalogos\Ubicacions;

use App\Filament\Resources\Catalogos\Ubicacions\Pages\ArbolUbicacion;
use App\Filament\Resources\Catalogos\Ubicacions\Pages\ListUbicacions;
use App\Filament\Resources\Catalogos\Ubicacions\Pages\ViewUbicacion;
use App\Filament\Resources\Catalogos\Ubicacions\Schemas\UbicacionForm;
use App\Filament\Resources\Catalogos\Ubicacions\Schemas\UbicacionInfolist;
use App\Filament\Resources\Catalogos\Ubicacions\Tables\UbicacionsTable;
use App\Models\Catalogos\Ubicacion;
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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::MapPin;

    protected static ?string $pluralModelLabel = 'Ubicaciones';
    protected static string|UnitEnum|null $navigationGroup = 'Gestión de Catálogos';

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
        return UbicacionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUbicacions::route('/'),
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
