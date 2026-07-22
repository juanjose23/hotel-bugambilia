<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\PedidoResource;

use App\Filament\Resources\Restaurante\PedidoResource\Pages\CreatePedido;
use App\Filament\Resources\Restaurante\PedidoResource\Pages\EditPedido;
use App\Filament\Resources\Restaurante\PedidoResource\Pages\ListPedidos;
use App\Filament\Resources\Restaurante\PedidoResource\Schemas\PedidoForm;
use App\Filament\Resources\Restaurante\PedidoResource\Tables\PedidoTable;
use App\Repository\Models\Restaurante\Pedido;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PedidoResource extends Resource
{
    protected static ?string $model = Pedido::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ClipboardDocumentList;

    protected static ?string $slug = 'restaurante/pedidos';

    protected static string|UnitEnum|null $navigationGroup = 'Restaurante';

    protected static ?string $navigationLabel = 'Pedidos';

    protected static ?string $modelLabel = 'Pedido';

    protected static ?string $pluralModelLabel = 'Pedidos';

    public static function form(Schema $schema): Schema
    {
        return PedidoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PedidoTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPedidos::route('/'),
            'create' => CreatePedido::route('/create'),
            'edit' => EditPedido::route('/{record}/edit'),
        ];
    }
}
