<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\ParStockResource;

use App\Filament\Resources\Inventario\ParStockResource\Pages\CreateParStock;
use App\Filament\Resources\Inventario\ParStockResource\Pages\EditParStock;
use App\Filament\Resources\Inventario\ParStockResource\Pages\ListParStocks;
use App\Filament\Resources\Inventario\ParStockResource\Schemas\ParStockForm;
use App\Filament\Resources\Inventario\ParStockResource\Tables\ParStockTable;
use App\Models\Inventario\ParStock;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ParStockResource extends Resource
{
    protected static ?string $model = ParStock::class;

    protected static ?string $slug = 'inventario/par-stock';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::AdjustmentsHorizontal;

    protected static string|UnitEnum|null $navigationGroup = 'Inventario';

    protected static ?string $navigationLabel = 'Configuración PAR Stock';

    protected static ?string $modelLabel = 'PAR Stock';

    protected static ?string $pluralModelLabel = 'PAR Stocks';

    protected static ?int $navigationSort = 25;

    public static function form(Schema $schema): Schema
    {
        return ParStockForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ParStockTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListParStocks::route('/'),
            'create' => CreateParStock::route('/create'),
            'edit' => EditParStock::route('/{record}/edit'),
        ];
    }
}
