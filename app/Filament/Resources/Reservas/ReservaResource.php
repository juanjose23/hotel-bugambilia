<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas;

use App\Filament\Resources\Reservas\ReservaResource\Pages\CreateReserva;
use App\Filament\Resources\Reservas\ReservaResource\Pages\EditReserva;
use App\Filament\Resources\Reservas\ReservaResource\Pages\ListReservas;
use App\Filament\Resources\Reservas\ReservaResource\Schemas\ReservaForm;
use App\Filament\Resources\Reservas\ReservaResource\Tables\ReservaTable;
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

    protected static string|UnitEnum|null $navigationGroup = 'Habitaciones';

    protected static ?string $modelLabel = 'Reserva';

    protected static ?string $pluralModelLabel = 'Reservas';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return app(ReservaForm::class)->configure($schema);
    }

    public static function table(Table $table): Table
    {
        return app(ReservaTable::class)->configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReservas::route('/'),
            'create' => CreateReserva::route('/create'),
            'edit' => EditReserva::route('/{record}/edit'),
        ];
    }
}
