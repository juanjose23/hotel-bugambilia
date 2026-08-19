<?php

namespace App\Filament\Resources\Compras\OrdenesCompra;

use App\Filament\Resources\Compras\OrdenesCompra\Pages\CreateOrdenCompra;
use App\Filament\Resources\Compras\OrdenesCompra\Pages\EditOrdenCompra;
use App\Filament\Resources\Compras\OrdenesCompra\Pages\ListOrdenCompras;
use App\Filament\Resources\Compras\OrdenesCompra\Pages\ViewOrdenCompra;
use App\Filament\Resources\Compras\OrdenesCompra\Schemas\OrdenCompraForm;
use App\Filament\Resources\Compras\OrdenesCompra\Schemas\OrdenCompraInfolist;
use App\Filament\Resources\Compras\OrdenesCompra\Tables\OrdenCompraTable;
use App\Repository\Models\Compras\OrdenCompra;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class OrdenCompraResource extends Resource
{
    protected static ?string $model = OrdenCompra::class;

    protected static ?string $slug = 'compras/ordenes-compra';

    protected static string|UnitEnum|null $navigationGroup = 'Compras & Proveedores';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ShoppingCart;

    protected static ?string $navigationLabel = 'Órdenes de Compra';

    protected static ?string $modelLabel = 'Orden de Compra';

    protected static ?string $pluralModelLabel = 'Órdenes de Compra';

    protected static ?string $recordTitleAttribute = 'codigo';

    public static function form(Schema $schema): Schema
    {
        return OrdenCompraForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrdenCompraTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OrdenCompraInfolist::configure($schema);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'proveedor.persona.personaJuridica',
                'proveedor.persona.personaNatural',
                'solicitud:id,codigo',
                'cotizacion:id',
            ])
            ->withExists('recepciones')
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrdenCompras::route('/'),
            'create' => CreateOrdenCompra::route('/create'),
            'view' => ViewOrdenCompra::route('/{record}'),
            'edit' => EditOrdenCompra::route('/{record}/edit'),
        ];
    }
}
