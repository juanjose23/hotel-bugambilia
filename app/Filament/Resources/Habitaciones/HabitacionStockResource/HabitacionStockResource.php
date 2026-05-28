<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\HabitacionStockResource;

use App\Filament\Resources\Habitaciones\HabitacionStockResource\Pages\ListHabitacionStocks;
use App\Filament\Resources\Habitaciones\HabitacionStockResource\Tables\HabitacionStockTable;
use App\Models\Habitaciones\HabitacionStock;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class HabitacionStockResource extends Resource
{
    protected static ?string $model = HabitacionStock::class;

    protected static ?string $slug = 'habitaciones/stock';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Habitaciones';

    protected static ?string $navigationLabel = 'Stock Operativo';

    protected static ?string $modelLabel = 'Stock en Habitación';

    protected static ?string $pluralModelLabel = 'Stocks en Habitaciones';

    protected static ?int $navigationSort = 15;

    public static function table(Table $table): Table
    {
        return HabitacionStockTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHabitacionStocks::route('/'),
        ];
    }
}
