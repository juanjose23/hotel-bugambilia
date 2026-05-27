<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\ActivoBaja;

use App\Filament\Resources\Activos\ActivoBaja\Pages\CreateActivoBaja;
use App\Filament\Resources\Activos\ActivoBaja\Pages\ListActivoBajas;
use App\Filament\Resources\Activos\ActivoBaja\Schemas\ActivoBajaForm;
use App\Filament\Resources\Activos\ActivoBaja\Tables\ActivoBajaTable;
use App\Models\Activos\ActivoBaja;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ActivoBajaResource extends Resource
{
    protected static ?string $model = ActivoBaja::class;

    protected static ?string $slug = 'activos/bajas';

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::Trash;

    protected static \UnitEnum|string|null $navigationGroup = 'Activos Fijos';

    protected static ?string $navigationLabel = 'Bajas de Activos';

    protected static ?string $modelLabel = 'Baja de Activo';

    protected static ?string $pluralModelLabel = 'Bajas de Activos';

    public static function form(Schema $schema): Schema
    {
        return ActivoBajaForm::form($schema);
    }

    public static function table(Table $table): Table
    {
        return ActivoBajaTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivoBajas::route('/'),
            'create' => CreateActivoBaja::route('/create'),
        ];
    }
}
