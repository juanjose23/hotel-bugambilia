<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\RegistroIndividualizacion;

use App\Filament\Resources\Activos\RegistroIndividualizacion\Pages\ListRegistroIndividualizaciones;
use App\Filament\Resources\Activos\RegistroIndividualizacion\Schemas\RegistroIndividualizacionForm;
use App\Filament\Resources\Activos\RegistroIndividualizacion\Tables\RegistroIndividualizacionTable;
use App\Models\Activos\RegistroIndividualizacion;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RegistroIndividualizacionResource extends Resource
{
    protected static ?string $model = RegistroIndividualizacion::class;

    protected static ?string $slug = 'activos/individualizaciones';

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::PuzzlePiece;

    protected static \UnitEnum|string|null $navigationGroup = 'Activos Fijos';

    protected static ?string $navigationLabel = 'Bandeja de Individualización';

    protected static ?string $modelLabel = 'Bandeja de Individualización';

    protected static ?string $pluralModelLabel = 'Individualizaciones Pendientes';

    public static function form(Schema $schema): Schema
    {
        return RegistroIndividualizacionForm::form($schema);
    }

    public static function table(Table $table): Table
    {
        return RegistroIndividualizacionTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRegistroIndividualizaciones::route('/'),
        ];
    }
}
