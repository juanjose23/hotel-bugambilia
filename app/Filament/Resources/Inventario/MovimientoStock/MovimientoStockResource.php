<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\MovimientoStock;

use App\Filament\Resources\Inventario\MovimientoStock\Pages\ListMovimientoStock;
use App\Filament\Resources\Inventario\MovimientoStock\Widgets\MermasPorCategoriaChart;
use App\Filament\Resources\Inventario\MovimientoStock\Widgets\RotacionInventarioChart;
use App\Repository\Models\Inventario\MovimientoStock;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MovimientoStockResource extends Resource
{
    protected static ?string $model = MovimientoStock::class;

    protected static ?string $slug = 'inventario/movimientos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowPath;

    protected static string|UnitEnum|null $navigationGroup = 'Inventario & Productos';

    protected static ?string $navigationLabel = 'Movimientos de Stock';

    public static function table(Table $table): Table
    {
        return Tables\MovimientoStockTable::configure($table);
    }

    public static function getWidgets(): array
    {
        return [
            RotacionInventarioChart::class,
            MermasPorCategoriaChart::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMovimientoStock::route('/'),
        ];
    }
}
