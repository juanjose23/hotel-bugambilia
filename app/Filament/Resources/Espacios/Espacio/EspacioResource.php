<?php

declare(strict_types=1);

namespace App\Filament\Resources\Espacios\Espacio;

use App\Filament\Resources\Espacios\Espacio\Pages\CreateEspacio;
use App\Filament\Resources\Espacios\Espacio\Pages\EditEspacio;
use App\Filament\Resources\Espacios\Espacio\Pages\ListEspacios;
use App\Filament\Resources\Espacios\Espacio\Schemas\EspacioForm;
use App\Filament\Resources\Espacios\Espacio\Tables\EspacioTable;
use App\Models\Espacios\Espacio;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class EspacioResource extends Resource
{
    protected static ?string $model = Espacio::class;

    protected static ?string $slug = 'espacios';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-building-library';

    protected static \UnitEnum|string|null $navigationGroup = 'Habitaciones y Ambientes';

    protected static ?string $navigationLabel = 'Espacios / Áreas Comunes';

    protected static ?string $modelLabel = 'Espacio';

    protected static ?string $pluralModelLabel = 'Espacios / Áreas Comunes';

    public static function form(Schema $schema): Schema
    {
        return EspacioForm::form($schema);
    }

    public static function table(Table $table): Table
    {
        return EspacioTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEspacios::route('/'),
            'create' => CreateEspacio::route('/create'),
            'edit' => EditEspacio::route('/{record}/edit'),
        ];
    }
}
