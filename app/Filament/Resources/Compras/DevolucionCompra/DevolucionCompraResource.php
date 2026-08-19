<?php

namespace App\Filament\Resources\Compras\DevolucionCompra;

use App\Filament\Resources\Compras\DevolucionCompra\Pages\CreateDevolucionCompra;
use App\Filament\Resources\Compras\DevolucionCompra\Pages\EditDevolucionCompra;
use App\Filament\Resources\Compras\DevolucionCompra\Pages\ListDevolucionCompras;
use App\Filament\Resources\Compras\DevolucionCompra\Pages\ViewDevolucionCompra;
use App\Filament\Resources\Compras\DevolucionCompra\Schemas\DevolucionCompraForm;
use App\Filament\Resources\Compras\DevolucionCompra\Schemas\DevolucionCompraInfolist;
use App\Filament\Resources\Compras\DevolucionCompra\Tables\DevolucionCompraTable;
use App\Repository\Models\Compras\DevolucionCompra;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class DevolucionCompraResource extends Resource
{
    protected static ?string $model = DevolucionCompra::class;

    protected static ?string $slug = 'compras/devoluciones';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowTurnDownLeft;

    protected static string|UnitEnum|null $navigationGroup = 'Compras & Proveedores';

    protected static ?string $navigationLabel = 'Devoluciones';

    protected static ?string $modelLabel = 'Devolución de Mercancía';

    protected static ?string $pluralModelLabel = 'Devoluciones';

    protected static ?int $navigationSort = 45;

    protected static ?string $recordTitleAttribute = 'codigo';

    public static function form(Schema $schema): Schema
    {
        return DevolucionCompraForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DevolucionCompraTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DevolucionCompraInfolist::configure($schema);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['ordenCompra', 'recepcionCompra', 'creador']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDevolucionCompras::route('/'),
            'create' => CreateDevolucionCompra::route('/create'),
            'view' => ViewDevolucionCompra::route('/{record}'),
            'edit' => EditDevolucionCompra::route('/{record}/edit'),
        ];
    }
}
