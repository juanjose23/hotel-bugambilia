<?php

declare(strict_types=1);

namespace App\Filament\Resources\Usuarios\ConflictosIdentidad;

use App\Filament\Resources\Usuarios\ConflictosIdentidad\Pages\ListConflictosIdentidad;
use App\Filament\Resources\Usuarios\ConflictosIdentidad\Pages\ViewConflictoIdentidad;
use App\Filament\Resources\Usuarios\ConflictosIdentidad\Schemas\ConflictoIdentidadInfolist;
use App\Filament\Resources\Usuarios\ConflictosIdentidad\Tables\ConflictosIdentidadTable;
use App\Repository\Models\Usuarios\ConflictoIdentidad;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ConflictoIdentidadResource extends Resource
{
    protected static ?string $model = ConflictoIdentidad::class;

    protected static ?string $slug = 'usuarios/conflictos-identidad';

    protected static ?string $navigationLabel = 'Conflictos de Identidad';

    protected static ?string $modelLabel = 'Conflicto de Identidad';

    protected static ?string $pluralModelLabel = 'Conflictos de Identidad';

    protected static string|UnitEnum|null $navigationGroup = 'Personas & Accesos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ExclamationTriangle;

    protected static ?int $navigationSort = 4;

    public static function infolist(Schema $schema): Schema
    {
        return ConflictoIdentidadInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ConflictosIdentidadTable::configure($table);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('estado', 'pendiente')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListConflictosIdentidad::route('/'),
            'view' => ViewConflictoIdentidad::route('/{record}'),
        ];
    }
}
