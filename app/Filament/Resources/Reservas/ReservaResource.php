<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas;

use App\Filament\Resources\Reservas\ReservaResource\Pages\CreateReserva;
use App\Filament\Resources\Reservas\ReservaResource\Pages\EditReserva;
use App\Filament\Resources\Reservas\ReservaResource\Pages\ListReservas;
use App\Filament\Resources\Reservas\ReservaResource\Pages\ViewReserva;
use App\Filament\Resources\Reservas\ReservaResource\RelationManagers\CuentasRelationManager;
use App\Filament\Resources\Reservas\ReservaResource\RelationManagers\DetallesRelationManager;
use App\Filament\Resources\Reservas\ReservaResource\RelationManagers\EstanciaRelationManager;
use App\Filament\Resources\Reservas\ReservaResource\RelationManagers\HistorialEstadosRelationManager;
use App\Filament\Resources\Reservas\ReservaResource\RelationManagers\HuespedesRelationManager;
use App\Filament\Resources\Reservas\ReservaResource\Schemas\ReservaForm;
use App\Filament\Resources\Reservas\ReservaResource\Tables\ReservaTable;
use App\Filament\Resources\Reservas\Schemas\Reserva\ResumenReserva;
use App\Repository\Models\Reservas\Reserva;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ReservaResource extends Resource
{
    protected static ?string $model = Reserva::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Reservaciones';

    protected static ?string $modelLabel = 'Reservación';

    protected static ?string $pluralModelLabel = 'Gestión de Reservaciones';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return (new ReservaForm)->configure($schema);
    }

    public static function table(Table $table): Table
    {
        return (new ReservaTable)->configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                ResumenReserva::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReservas::route('/'),
            'create' => CreateReserva::route('/create'),
            'view' => ViewReserva::route('/{record}'),
            'edit' => EditReserva::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            DetallesRelationManager::class,
            CuentasRelationManager::class,
            HuespedesRelationManager::class,
            EstanciaRelationManager::class,
            HistorialEstadosRelationManager::class,
        ];
    }
}
