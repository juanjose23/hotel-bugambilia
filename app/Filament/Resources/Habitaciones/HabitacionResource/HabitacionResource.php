<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\HabitacionResource;

use App\Filament\Resources\Habitaciones\HabitacionResource\Pages\CreateHabitacion;
use App\Filament\Resources\Habitaciones\HabitacionResource\Pages\EditHabitacion;
use App\Filament\Resources\Habitaciones\HabitacionResource\Pages\ListHabitaciones;
use App\Filament\Resources\Habitaciones\HabitacionResource\Pages\ViewHabitacion;
use App\Filament\Resources\Habitaciones\HabitacionResource\RelationManagers\ImagenesRelationManager;
use App\Filament\Resources\Habitaciones\HabitacionResource\RelationManagers\PoliticasRelationManager;
use App\Filament\Resources\Habitaciones\HabitacionResource\RelationManagers\PreciosRelationManager;
use App\Filament\Resources\Habitaciones\HabitacionResource\RelationManagers\ServiciosRelationManager;
use App\Filament\Resources\Habitaciones\HabitacionResource\Schemas\HabitacionForm;
use App\Filament\Resources\Habitaciones\HabitacionResource\Schemas\HabitacionInfolist;
use App\Filament\Resources\Habitaciones\HabitacionResource\Tables\HabitacionTable;
use App\Models\Habitaciones\Habitacion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class HabitacionResource extends Resource
{
    protected static ?string $model = Habitacion::class;

    protected static ?string $slug = 'habitaciones/habitaciones';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::HomeModern;

    protected static string|UnitEnum|null $navigationGroup = 'Habitaciones';

    protected static ?string $modelLabel = 'Habitación';

    protected static ?string $pluralModelLabel = 'Habitaciones';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return HabitacionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return HabitacionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HabitacionTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PoliticasRelationManager::class,
            ServiciosRelationManager::class,
            PreciosRelationManager::class,
            ImagenesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHabitaciones::route('/'),
            'create' => CreateHabitacion::route('/create'),
            'view' => ViewHabitacion::route('/{record}'),
            'edit' => EditHabitacion::route('/{record}/edit'),
        ];
    }
}
