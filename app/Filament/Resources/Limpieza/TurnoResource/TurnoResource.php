<?php

declare(strict_types=1);

namespace App\Filament\Resources\Limpieza\TurnoResource;

use App\Filament\Resources\Limpieza\TurnoResource\Pages\CreateTurno;
use App\Filament\Resources\Limpieza\TurnoResource\Pages\EditTurno;
use App\Filament\Resources\Limpieza\TurnoResource\Pages\ListTurnos;
use App\Filament\Resources\Limpieza\TurnoResource\RelationManagers\HorariosRelationManager;
use App\Filament\Resources\Limpieza\TurnoResource\Schemas\TurnoForm;
use App\Filament\Resources\Limpieza\TurnoResource\Tables\TurnoTable;
use App\Models\Limpieza\Turno;
use BackedEnum;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TurnoResource extends Resource
{
    protected static ?string $model = Turno::class;

    protected static ?string $slug = 'limpieza/turnos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Clock;

    protected static string|UnitEnum|null $navigationGroup = 'Limpieza';

    protected static ?string $modelLabel = 'Turno de Limpieza';

    protected static ?string $pluralModelLabel = 'Turnos de Limpieza';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return TurnoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TurnoTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            HorariosRelationManager::class,
        ];
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListTurnos::route('/'),
            'create' => CreateTurno::route('/create'),
            'edit' => EditTurno::route('/{record}/edit'),
        ];
    }
}
