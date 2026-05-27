<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\ActPlanMantenimiento;

use App\Filament\Resources\Activos\ActPlanMantenimiento\Pages\CreateActPlanMantenimiento;
use App\Filament\Resources\Activos\ActPlanMantenimiento\Pages\EditActPlanMantenimiento;
use App\Filament\Resources\Activos\ActPlanMantenimiento\Pages\ListActPlanMantenimientos;
use App\Filament\Resources\Activos\ActPlanMantenimiento\Pages\ViewActPlanMantenimiento;
use App\Filament\Resources\Activos\ActPlanMantenimiento\Schemas\ActPlanMantenimientoForm;
use App\Filament\Resources\Activos\ActPlanMantenimiento\Tables\ActPlanMantenimientoTable;
use App\Models\Activos\ActPlanMantenimiento;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ActPlanMantenimientoResource extends Resource
{
    protected static ?string $model = ActPlanMantenimiento::class;

    protected static ?string $slug = 'activos/planes-mantenimiento';

    public static function getModel(): string
    {
        return ActPlanMantenimiento::class;
    }

    protected static ?string $navigationLabel = 'Planes de Mantenimiento';

    protected static ?string $modelLabel = 'Plan de Mantenimiento';

    protected static ?string $pluralModelLabel = 'Planes de Mantenimiento';

    protected static string|UnitEnum|null $navigationGroup = 'Activos Fijos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentCheck;

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return ActPlanMantenimientoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActPlanMantenimientoTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActPlanMantenimientos::route('/'),
            'create' => CreateActPlanMantenimiento::route('/create'),
            'view' => ViewActPlanMantenimiento::route('/{record}'),
            'edit' => EditActPlanMantenimiento::route('/{record}/edit'),
        ];
    }
}
