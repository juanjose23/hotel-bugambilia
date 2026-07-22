<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\StockResource;

use App\Filament\Resources\Inventario\StockResource\Pages\ListStocks;
use App\Filament\Resources\Inventario\StockResource\Tables\StockTable;
use App\Repository\Models\Inventario\Stock;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class StockResource extends Resource
{
    protected static ?string $model = Stock::class;

    protected static ?string $slug = 'inventario/stock';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CircleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Inventario';

    protected static ?string $navigationLabel = 'Stock en Bodegas';

    protected static ?string $modelLabel = 'Stock';

    protected static ?string $pluralModelLabel = 'Stock en Bodegas';

    protected static ?int $navigationSort = 10;

    public static function table(Table $table): Table
    {
        return StockTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStocks::route('/'),
        ];
    }
}
