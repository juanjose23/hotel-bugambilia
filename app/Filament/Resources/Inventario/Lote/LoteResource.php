<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\Lote;

use App\Filament\Resources\Inventario\Lote\Pages\ListLotes;
use App\Filament\Resources\Inventario\Lote\Pages\ViewLote;
use App\Filament\Resources\Inventario\Lote\RelationManagers\MovimientosRelationManager;
use App\Filament\Resources\Inventario\Lote\Schemas\LoteInfolist;
use App\Filament\Resources\Inventario\Lote\Tables\LoteTable;
use App\Filament\Resources\Inventario\Lote\Widgets\LoteStatsOverview;
use App\Repository\Models\Inventario\Lote;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LoteResource extends Resource
{
    protected static ?string $model = Lote::class;

    protected static ?string $slug = 'inventario/lotes';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cube;

    protected static string|UnitEnum|null $navigationGroup = 'Inventario';

    public static function infolist(Schema $schema): Schema
    {
        return LoteInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LoteTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            MovimientosRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            LoteStatsOverview::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLotes::route('/'),
            'view' => ViewLote::route('/{record}'),
        ];
    }
}
