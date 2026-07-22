<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\ActivoMantenimiento;

use App\Filament\Resources\Activos\ActivoMantenimiento\Pages\CreateActivoMantenimiento;
use App\Filament\Resources\Activos\ActivoMantenimiento\Pages\EditActivoMantenimiento;
use App\Filament\Resources\Activos\ActivoMantenimiento\Pages\ListActivoMantenimientos;
use App\Filament\Resources\Activos\ActivoMantenimiento\Pages\ViewActivoMantenimiento;
use App\Filament\Resources\Activos\ActivoMantenimiento\Schemas\ActivoMantenimientoForm;
use App\Filament\Resources\Activos\ActivoMantenimiento\Schemas\ActivoMantenimientoInfolist;
use App\Filament\Resources\Activos\ActivoMantenimiento\Tables\ActivoMantenimientoTable;
use App\Repository\Models\Activos\ActivoMantenimiento;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ActivoMantenimientoResource extends Resource
{
    protected static ?string $model = ActivoMantenimiento::class;

    protected static ?string $slug = 'activos/mantenimientos';

    protected static BackedEnum|string|null $navigationIcon = Heroicon::WrenchScrewdriver;

    protected static UnitEnum|string|null $navigationGroup = 'Activos Fijos';

    protected static ?string $navigationLabel = 'Mantenimiento';

    protected static ?string $modelLabel = 'Mantenimiento';

    protected static ?string $pluralModelLabel = 'Órdenes de Mantenimiento';

    public static function form(Schema $schema): Schema
    {
        return ActivoMantenimientoForm::form($schema);
    }

    public static function table(Table $table): Table
    {
        return app(ActivoMantenimientoTable::class)->configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ActivoMantenimientoInfolist::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivoMantenimientos::route('/'),
            'create' => CreateActivoMantenimiento::route('/create'),
            'view' => ViewActivoMantenimiento::route('/{record}'),
            'edit' => EditActivoMantenimiento::route('/{record}/edit'),
        ];
    }
}
