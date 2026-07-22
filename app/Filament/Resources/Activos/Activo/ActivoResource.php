<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\Activo;

use App\Filament\Resources\Activos\Activo\Pages\CreateActivo;
use App\Filament\Resources\Activos\Activo\Pages\EditActivo;
use App\Filament\Resources\Activos\Activo\Pages\ListActivos;
use App\Filament\Resources\Activos\Activo\Pages\ViewActivo;
use App\Filament\Resources\Activos\Activo\Schemas\ActivoForm;
use App\Filament\Resources\Activos\Activo\Schemas\ActivoInfolist;
use App\Filament\Resources\Activos\Activo\Tables\ActivoTable;
use App\Repository\Models\Activos\Activo;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ActivoResource extends Resource
{
    protected static ?string $model = Activo::class;

    protected static ?string $slug = 'activos/registro';

    protected static BackedEnum|string|null $navigationIcon = Heroicon::CpuChip;

    protected static UnitEnum|string|null $navigationGroup = 'Activos Fijos';

    protected static ?string $navigationLabel = 'Inventario de Activos';

    protected static ?string $modelLabel = 'Activo Fijo';

    protected static ?string $pluralModelLabel = 'Inventario de Activos';

    public static function form(Schema $schema): Schema
    {
        return ActivoForm::form($schema);
    }

    public static function table(Table $table): Table
    {
        return app(ActivoTable::class)->configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ActivoInfolist::configure($schema);
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivos::route('/'),
            'create' => CreateActivo::route('/create'),
            'view' => ViewActivo::route('/{record}'),
            'edit' => EditActivo::route('/{record}/edit'),
        ];
    }
}
