<?php

declare(strict_types=1);

namespace App\Filament\Resources\Limpieza\LimpiezaEjecucionResource;

use App\Filament\Resources\Limpieza\LimpiezaEjecucionResource\Pages\CreateLimpiezaEjecucion;
use App\Filament\Resources\Limpieza\LimpiezaEjecucionResource\Pages\EditLimpiezaEjecucion;
use App\Filament\Resources\Limpieza\LimpiezaEjecucionResource\Pages\ListLimpiezaEjecuciones;
use App\Filament\Resources\Limpieza\LimpiezaEjecucionResource\Pages\ViewLimpiezaEjecucion;
use App\Filament\Resources\Limpieza\LimpiezaEjecucionResource\Schemas\LimpiezaEjecucionForm;
use App\Filament\Resources\Limpieza\LimpiezaEjecucionResource\Schemas\LimpiezaEjecucionInfolist;
use App\Filament\Resources\Limpieza\LimpiezaEjecucionResource\Tables\LimpiezaEjecucionTable;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use BackedEnum;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LimpiezaEjecucionResource extends Resource
{
    protected static ?string $model = LimpiezaEjecucion::class;

    protected static ?string $slug = 'limpieza/ejecuciones';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CheckBadge;

    protected static string|UnitEnum|null $navigationGroup = 'Limpieza';

    protected static ?string $modelLabel = 'Ejecución de Limpieza';

    protected static ?string $pluralModelLabel = 'Ejecuciones de Limpieza';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return LimpiezaEjecucionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LimpiezaEjecucionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LimpiezaEjecucionTable::configure($table);
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListLimpiezaEjecuciones::route('/'),
            'create' => CreateLimpiezaEjecucion::route('/create'),
            'view' => ViewLimpiezaEjecucion::route('/{record}'),
            'edit' => EditLimpiezaEjecucion::route('/{record}/edit'),
        ];
    }
}
