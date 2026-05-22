<?php

namespace App\Filament\Resources\Servicios\Servicios;

use App\Filament\Resources\Servicios\Servicios\Pages\CreateServicio;
use App\Filament\Resources\Servicios\Servicios\Pages\EditServicio;
use App\Filament\Resources\Servicios\Servicios\Pages\ListServicios;
use App\Filament\Resources\Servicios\Servicios\Pages\ViewServicio;
use App\Filament\Resources\Servicios\Servicios\RelationManagers\PreciosRelationManager;
use App\Filament\Resources\Servicios\Servicios\Schemas\ServicioForm;
use App\Filament\Resources\Servicios\Servicios\Schemas\ServicioInfolist;
use App\Filament\Resources\Servicios\Servicios\Tables\ServiciosTable;
use App\Models\Servicios\Servicio;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ServicioResource extends Resource
{
    protected static ?string $model = Servicio::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Briefcase;

    protected static ?string $slug = 'servicios/servicios';

    protected static string|UnitEnum|null $navigationGroup = 'Servicios';

    protected static ?string $navigationLabel = 'Servicios';

    protected static ?string $modelLabel = 'Servicio';

    protected static ?string $pluralModelLabel = 'Servicios';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return ServicioForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ServicioInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiciosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PreciosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServicios::route('/'),
            'create' => CreateServicio::route('/create'),
            'view' => ViewServicio::route('/{record}'),
            'edit' => EditServicio::route('/{record}/edit'),
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
